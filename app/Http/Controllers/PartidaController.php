<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\EventoPartida;
use App\Models\Inscricao;
use App\Models\Partida;
use Illuminate\Http\Request;

class PartidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $campeonatos = Campeonato::where('status', 'EM_ANDAMENTO')->latest()->get();// Campeonatos em andamento para aparecer no datalist
        $partidas = collect();// Começa sem partidas
        // Se um campeonato foi selecionado
        if ($request->filled('campeonato_id')) {
            $partidas = Partida::with(['mandante', 'visitante'])->where('campeonato_id', $request->campeonato_id)
                ->orderByRaw("CASE WHEN status = 'PENDENTE' THEN 0 ELSE 1 END")
                ->orderByRaw(" CASE  WHEN status = 'PENDENTE' THEN CAST(REPLACE(fase, 'RODADA_', '') AS UNSIGNED) ELSE NULL END")
                ->orderBy('data_hora')->get();

            // Calcula os pênaltis de desempate de cada partida
            foreach ($partidas as $partida) {
                $penaltis = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'PENALTI_CONVERTIDO_DESEMPATE')->with('participante')->get();
                $penaltisMandante = 0;
                $penaltisVisitante = 0;
                foreach ($penaltis as $penalti) {
                    $contrato = $penalti->participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
                    if (!$contrato) {
                        continue;
                    }
                    if ($contrato->equipe_id == $partida->mandante_id) {
                        $penaltisMandante++;
                    } else {
                        $penaltisVisitante++;
                    }
                }
                // Adiciona os pênaltis apenas para exibir na tela
                $partida->penaltisMandante = $penaltisMandante;
                $partida->penaltisVisitante = $penaltisVisitante;
            }
        }
        return view('areaAdministrativa.partidas.index', compact('campeonatos', 'partidas'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Partida $partida)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partida $partida)
    {
        return view('areaAdministrativa.partidas.edit', compact('partida'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partida $partida)
    {
        $request->validate([
            'data' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'local' => 'required|string|max:255',
        ]);
        $dataHora = $request->data . ' ' . $request->hora . ':00';
        $local = trim($request->local);
        $campeonato = $partida->campeonato;// Busca o campeonato da partida
        // Verifica se a data está dentro do período do campeonato
        if ($request->data < $campeonato->data_inicio || $request->data > $campeonato->data_fim) {
            return back()->withInput()->withErrors(['data' => 'A data da partida deve estar dentro do período do campeonato.']);
        }
        // Verifica se já existe outra partida no mesmo local, na mesma data e no mesmo horário
        $conflito = Partida::where('id', '!=', $partida->id)->where('local', $local)->where('data_hora', $dataHora)->exists();
        if ($conflito) {
            return back()->withInput()->withErrors(['data' => 'Já existe uma partida marcada neste local, nesta mesma data e horário.']);
        }
        // Atualiza a partida
        $partida->update([
            'data_hora' => $dataHora,
            'local' => $local,
            'status' => 'AGENDADA',
        ]);
        //manda para tela de partidas com o nome e o id do campeonato selecionado, para que não precise fazer a pesquisa novamente 
        return redirect()->route('partidas.index', ['campeonato_id' => $request->campeonato_id, 'campeonato_nome' => $request->campeonato_nome,])->with('success', 'Data, hora e local da partida definidos com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partida $partida)
    {
        //
    }

    private function buscarVencedorDaPartida($partida)
    {
        // Primeiro verifica quem venceu pelo número de gols
        if ($partida->gols_mandante > $partida->gols_visitante) {
            return $partida->mandante_id;
        }

        if ($partida->gols_visitante > $partida->gols_mandante) {
            return $partida->visitante_id;
        }

        // Se empatou, procura os pênaltis de desempate
        $penaltis = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'PENALTI_CONVERTIDO_DESEMPATE')->with('participante')->get();

        $mandante = 0;
        $visitante = 0;
        foreach ($penaltis as $penalti) {
            if (!$penalti->participante) {
                continue;
            }
            // Descobre a qual equipe pertence o jogador que bateu o pênalti
            $contrato = $penalti->participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
            if (!$contrato) {
                continue;
            }
            if ($contrato->equipe_id == $partida->mandante_id) {
                $mandante++;
            } elseif ($contrato->equipe_id == $partida->visitante_id) {
                $visitante++;
            }
        }
        if ($mandante > $visitante) {
            return $partida->mandante_id;
        }
        if ($visitante > $mandante) {
            return $partida->visitante_id;
        }
        return null;
    }

    private function buscarVencedoresDaFase(Campeonato $campeonato, $fase)
    {
        // Busca todas as partidas finalizadas da fase
        $partidas = Partida::where('campeonato_id', $campeonato->id)->where('fase', $fase)->where('status', 'FINALIZADA')->get();
        $vencedores = collect();// Guarda os vencedores das partidas
        foreach ($partidas as $partida) {
            $vencedor = $this->buscarVencedorDaPartida($partida); // Descobre quem venceu cada partida
            if ($vencedor !== null) {
                $vencedores->push($vencedor);
            }
        }
        // Remove equipes repetidas e reorganiza os índices
        return $vencedores->unique()->values();
    }

    private function buscarPerdedoresDaFase(Campeonato $campeonato, $fase)
    {
        // Busca todas as partidas finalizadas da fase
        $partidas = Partida::where('campeonato_id', $campeonato->id)->where('fase', $fase)->where('status', 'FINALIZADA')->get();
        $perdedores = collect(); //guarda os perdedores das partidas
        foreach ($partidas as $partida) {
            $vencedor = $this->buscarVencedorDaPartida($partida);
            // Se o mandante venceu, o perdedor é o visitante
            if ($vencedor === $partida->mandante_id) {
                $perdedores->push($partida->visitante_id);
                // Se o visitante venceu, o perdedor é o mandante
            } elseif ($vencedor === $partida->visitante_id) {
                $perdedores->push($partida->mandante_id);
            }
        }
        // Remove equipes repetidas e reorganiza os índices
        return $perdedores->unique()->values();
    }

    private function buscarFaseAnterior($fase)
    {
        // A rodada inicial não possui uma fase anterior
        if ($fase === 'RODADA_INICIAL') {
            return null;
        }
        // Verifica se a fase é uma rodada numerada
        if (str_starts_with($fase, 'RODADA_')) {
            $numero = (int) str_replace('RODADA_', '', $fase);
            if ($numero <= 2) {
                return 'RODADA_INICIAL';
            }
            return 'RODADA_' . ($numero - 1);
        }
        return null;
    }

    private function buscarEquipesDaFase(Campeonato $campeonato, $fase)
    {
        // Na primeira rodada, todas as equipes inscritas começam
        if ($fase === 'RODADA_INICIAL') {
            return Inscricao::where('campeonato_id', $campeonato->id)->pluck('equipe_id')->unique()->values();
        }
        // Nas próximas rodadas pega quem avançou da rodada anterior
        $faseAnterior = $this->buscarFaseAnterior($fase);
        if ($faseAnterior) {
            return $this->buscarEquipesQueAvancaramDaFase($campeonato, $faseAnterior);
        }
        return collect();
    }

    private function buscarEquipesQueAvancaramDaFase(Campeonato $campeonato, $fase)
    {
        // Primeiro pega todas as equipes que deveriam estar nessa fase
        $equipes = $this->buscarEquipesDaFase($campeonato, $fase);
        $partidas = Partida::where('campeonato_id', $campeonato->id)->where('fase', $fase)->where('status', 'FINALIZADA')->get();
        $vencedores = collect();
        // Pega os vencedores das partidas
        foreach ($partidas as $partida) {
            $vencedor = $this->buscarVencedorDaPartida($partida);
            if ($vencedor !== null) {
                $vencedores->push($vencedor);
            }
        }
        // Guarda todas as equipes que realmente jogaram
        $jogaram = collect();
        foreach ($partidas as $partida) {
            $jogaram->push($partida->mandante_id);
            $jogaram->push($partida->visitante_id);
        }
        // As equipes que estavam na fase mas não jogaram são as que passaram direto
        return $vencedores->merge($equipes->diff($jogaram))->unique()->values();
    }

    private function buscarProximaFase($fase)
    {
        // Se for a primeira rodada a próxima será a rodada 2
        if ($fase === 'RODADA_INICIAL') {
            return 'RODADA_2';
        }
        // Se já estiver em uma rodada numerada aumenta o número
        if (str_starts_with($fase, 'RODADA_')) {
            $numero = (int) str_replace('RODADA_', '', $fase);
            return 'RODADA_' . ($numero + 1);
        }
        return 'RODADA_2';
    }

    private function criarPartida($campeonato, $mandante, $visitante, $fase)
    {
        //Apenas cria a partida evita repetir código toda a vez
        return Partida::create([
            'campeonato_id' => $campeonato->id,
            'mandante_id' => $mandante,
            'visitante_id' => $visitante,
            'gols_mandante' => 0,
            'gols_visitante' => 0,
            'data_hora' => null,
            'status' => 'PENDENTE',
            'fase' => $fase,
            'local' => null,
        ]);
    }

    private function gerarRodada(Campeonato $campeonato, $equipes, $fase)
    {
        // Remove equipes repetidas e embaralha para fazer o sorteio
        $equipes = collect($equipes)->unique()->values()->shuffle();
        // Quando chegar em 4 ou menos, usa semifinal ou triangular.
        if ($equipes->count() <= 4) {
            return;
        }
        //se for impar uma equipe passa direto então ela é aleatorio porque  foi embaralhado antes
        if ($equipes->count() % 2 !== 0) {
            $equipes->shift();
        }
        // Divide as equipes de duas em duas para criar os jogos
        for ($i = 0; $i < $equipes->count(); $i += 2) {
            if (isset($equipes[$i + 1])) {
                $this->criarPartida($campeonato, $equipes[$i], $equipes[$i + 1], $fase);
            }
        }
    }

    private function gerarSemifinais(Campeonato $campeonato, $equipes)
    {
        $equipes = collect($equipes)->unique()->values();
        if ($equipes->count() !== 4) {
            return;
        }
        // Com 4 equipes são criados 2 jogos de semifinal
        for ($i = 0; $i < 4; $i += 2) {
            $this->criarPartida($campeonato, $equipes[$i], $equipes[$i + 1], 'SEMIFINAL');
        }
    }

    private function gerarTriangular(Campeonato $campeonato, $equipes)
    {
        $equipes = collect($equipes)->unique()->values();
        if ($equipes->count() !== 3) {
            return;
        }
        // Com 3 equipes, cada uma joga contra as outras duas
        for ($i = 0; $i < 3; $i++) {
            for ($j = $i + 1; $j < 3; $j++) {
                $this->criarPartida($campeonato, $equipes[$i], $equipes[$j], 'TRIANGULAR');
            }
        }
    }

    private function gerarProximaFase($campeonato, $equipes, $fase)
    {
        $equipes = collect($equipes)->unique()->values();
        if ($equipes->count() !== 2) {
            return;
        }
        $this->criarPartida($campeonato, $equipes[0], $equipes[1], $fase);
    }

    public function finalizar(Partida $partida)
    {
        $campeonato = $partida->campeonato;
        // nas fases eliminatórias, empate precisa ser decidido nos pênaltis, no triangular o empate é permitido
        if ($partida->fase !== 'TRIANGULAR' && $partida->gols_mandante == $partida->gols_visitante && $this->buscarVencedorDaPartida($partida) === null) {
            return back()->withInput()->withErrors(['partida' => 'A partida está empatada. É necessário definir um vencedor nos pênaltis antes de finalizar.']);
        }
        $partida->update(['status' => 'FINALIZADA']);// Marca a partida como finalizada
        // Verifica se ainda existe alguma partida dessa fase pendente
        $pendentes = Partida::where('campeonato_id', $campeonato->id)->where('fase', $partida->fase)->where('status', '!=', 'FINALIZADA')->exists();
        // Se ainda houver jogos não cria a próxima fase
        if ($pendentes) {
            return $this->redirecionarPartidas();
        }
        // Trata a sequência das rodadas iniciais
        if ($partida->fase === 'RODADA_INICIAL' || str_starts_with($partida->fase, 'RODADA_')) {
            $equipes = $this->buscarEquipesQueAvancaramDaFase($campeonato, $partida->fase);
            // 3 equipes triangular
            if ($equipes->count() === 3) {
                $this->gerarTriangular($campeonato, $equipes);
                // 4 equipes semifinais
            } elseif ($equipes->count() === 4) {
                $this->gerarSemifinais($campeonato, $equipes);
                // Mais de 4 cria outra rodada
            } elseif ($equipes->count() > 4) {
                $this->gerarRodada($campeonato, $equipes, $this->buscarProximaFase($partida->fase));
            }
        }

        // Quando as duas semifinais terminarem
        elseif ($partida->fase === 'SEMIFINAL') {
            $vencedores = $this->buscarVencedoresDaFase($campeonato, 'SEMIFINAL');
            $perdedores = $this->buscarPerdedoresDaFase($campeonato, 'SEMIFINAL');
            // Os vencedores vão para a final e os perdedores disputam o terceiro lugar
            if ($vencedores->count() === 2 && $perdedores->count() === 2) {
                $this->gerarProximaFase($campeonato, $vencedores, 'FINAL');
                $this->gerarProximaFase($campeonato, $perdedores, 'TERCEIRO_LUGAR');
            }
        }
        // Depois que final e terceiro lugar terminarem o campeonato também termina
        elseif ($partida->fase === 'FINAL' || $partida->fase === 'TERCEIRO_LUGAR') {
            $finalizada = function ($fase) use ($campeonato) {
                return Partida::where('campeonato_id', $campeonato->id)->where('fase', $fase)->where('status', 'FINALIZADA')->exists();
            };

            if ($finalizada('FINAL') && $finalizada('TERCEIRO_LUGAR')) {
                $campeonato->update(['status' => 'FINALIZADO']);
            }
        }
        // No triangular, quando todas as partidas terminaram o campeonato é encerrado
        elseif ($partida->fase === 'TRIANGULAR') {
            $campeonato->update(['status' => 'FINALIZADO']);
        }

        return $this->redirecionarPartidas();
    }

    private function redirecionarPartidas()
    {
        return redirect()->route('partidas.index', ['campeonato_id' => request('campeonato_id'), 'campeonato_nome' => request('campeonato_nome')])->with('success', 'Partida finalizada com sucesso.');
    }
}
