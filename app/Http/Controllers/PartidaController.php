<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Contrato;
use App\Models\Equipe;
use App\Models\EventoPartida;
use App\Models\Inscricao;
use App\Models\Partida;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PartidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $campeonatos = Campeonato::latest()->get();
        $partidas = collect();
        // Busca as partidas somente quando um campeonato foi selecionado
        if ($request->filled('campeonato_id')) {
            $partidas = Partida::with(['mandante', 'visitante'])
                ->where('campeonato_id', $request->campeonato_id)
                ->orderByRaw("CASE WHEN status = 'PENDENTE' THEN 0 ELSE 1 END")
                ->orderByRaw("CASE WHEN status = 'PENDENTE' THEN CAST(REPLACE(fase, 'RODADA_', '') AS UNSIGNED) ELSE NULL END")
                ->orderBy('data_hora')
                ->get();
            $campeonato = Campeonato::findOrFail($request->campeonato_id);
            foreach ($partidas as $partida) {
                $mandantePodeJogar = $this->equipePodeJogar($partida->mandante_id, $campeonato);
                $visitantePodeJogar = $this->equipePodeJogar($partida->visitante_id, $campeonato);
                $partida->deveSerWO = !$mandantePodeJogar || !$visitantePodeJogar;
            }
            $this->adicionarPenaltisNasPartidas($partidas);
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
        $campeonato = $partida->campeonato;
        // Verifica se a data está dentro do campeonato
        if ($request->data < $campeonato->data_inicio || $request->data > $campeonato->data_fim) {
            return back()->withInput()->withErrors(['data' => 'A data da partida deve estar dentro do período do campeonato']);
        }
        // Verifica se existe outra partida no mesmo local e horário
        $conflito = Partida::where('id', '!=', $partida->id)->where('local', $local)->where('data_hora', $dataHora)->exists();
        if ($conflito) {
            return back()->withInput()->withErrors(['data' => 'Já existe uma partida marcada neste local, nesta mesma data e horário']);
        }
        // Atualiza os dados da partida
        $partida->update(['data_hora' => $dataHora, 'local' => $local, 'status' => 'AGENDADA']);
        // Volta para a lista mantendo o campeonato selecionado.
        return redirect()->route('partidas.index', ['campeonato_id' => $request->campeonato_id, 'campeonato_nome' => $request->campeonato_nome])->with('success', 'Data, hora e local da partida definidos com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partida $partida)
    {
        //
    }

    //Cria uma partida pendente
    private function criarPartida($campeonato, $mandante, $visitante, $fase)
    {
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

    //Adiciona os pênaltis de desempate nas partidas para exibição
    private function adicionarPenaltisNasPartidas($partidas)
    {
        foreach ($partidas as $partida) {
            [$mandante, $visitante] = $this->contarPenaltis($partida);
            $partida->penaltisMandante = $mandante;
            $partida->penaltisVisitante = $visitante;
        }
    }

    //Conta os pênaltis convertidos de cada equipe
    private function contarPenaltis($partida)
    {
        $penaltis = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'PENALTI_CONVERTIDO_DESEMPATE')->with('participante')->get();
        $mandante = 0;
        $visitante = 0;
        foreach ($penaltis as $penalti) {
            $equipeId = $this->buscarEquipeDoParticipante($penalti->participante, $partida);
            if ($equipeId === $partida->mandante_id) {
                $mandante++;
            } elseif ($equipeId === $partida->visitante_id) {
                $visitante++;
            }
        }
        return [$mandante, $visitante];
    }

    //Descobre a equipe do jogador que participou do evento
    private function buscarEquipeDoParticipante($participante, $partida)
    {
        if (!$participante) {
            return null;
        }
        $contrato = $participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
        return $contrato?->equipe_id;
    }

    //Gera as partidas conforme o tipo do campeonato
    public function gerarConfrontos(Campeonato $campeonato)
    {
        // O sorteio só pode acontecer durante as inscrições
        if ($campeonato->status !== 'INSCRICOES') {
            return back()->with('error', 'Este campeonato não está disponível para sorteio.');
        }
        // Busca as equipes inscritas
        $campeonato->load('inscricoes.equipe');
        $equipes = $campeonato->inscricoes->pluck('equipe');
        // Verifica se atingiu a quantidade necessária
        if ($equipes->count() != $campeonato->maximo_equipes) {
            return back()->with('error', 'O campeonato ainda não possui o número necessário de equipes.');
        }
        // Escolhe o gerador conforme o tipo do campeonato.
        switch ($campeonato->tipo) {
            case 'MATA_MATA':
                $this->gerarMataMata($campeonato, $equipes);
                break;
            case 'GRUPOS_MATA_MATA':
                $this->gerarGruposMataMata($campeonato, $equipes);
                break;

            case 'PONTOS_CORRIDOS':
                $this->gerarPontosCorridos($campeonato, $equipes);
                break;
        }
        // Após gerar as partidas, coloca o campeonato em andamento
        $campeonato->update(['status' => 'EM_ANDAMENTO']);
        return redirect()->route('campeonatos.index')->with('success', 'Partidas geradas com sucesso!');
    }
    //Gera partidas de pontos corridos em dois turnos
    private function gerarPontosCorridos($campeonato, $equipes)
    {
        $equipes = $equipes->values();
        // Adiciona uma folga quando existe quantidade ímpar
        if ($equipes->count() % 2 != 0) {
            $equipes->push(null);
        }
        $quantidadeEquipes = $equipes->count();
        $numeroRodadas = $quantidadeEquipes - 1;
        $partidasPorRodada = $quantidadeEquipes / 2;
        $equipesOriginais = $equipes->values();
        // Gera ida e volta
        for ($turno = 1; $turno <= 2; $turno++) {
            $equipes = $equipesOriginais->values();
            for ($rodada = 1; $rodada <= $numeroRodadas; $rodada++) {
                for ($jogo = 0; $jogo < $partidasPorRodada; $jogo++) {
                    $mandante = $equipes[$jogo];
                    $visitante = $equipes[$quantidadeEquipes - 1 - $jogo];
                    // Ignora a equipe que estiver na folga
                    if ($mandante === null || $visitante === null) {
                        continue;
                    }
                    $numeroRodadaAtual = $rodada;
                    // No segundo turno, muda a rodada e inverte o mando
                    if ($turno == 2) {
                        $numeroRodadaAtual += $numeroRodadas;
                        [$mandante, $visitante] = [$visitante, $mandante];
                    }
                    $this->criarPartida($campeonato, $mandante->id, $visitante->id, 'RODADA_' . $numeroRodadaAtual);
                }
                // Mantém a primeira equipe fixa e gira as demais
                $equipes = $this->rotacionarEquipes($equipes);
            }
        }
    }

    //Rotaciona as equipes para gerar a tabela de pontos corridos
    private function rotacionarEquipes($equipes)
    {
        $fixa = $equipes->first();
        $restantes = $equipes->slice(1)->values();
        $ultimo = $restantes->pop();
        $restantes->prepend($ultimo);
        return collect([$fixa])->merge($restantes)->values();
    }

    //Gera o mata-mata inicial.
    private function gerarMataMata($campeonato, $equipes)
    {
        $equipes = $equipes->shuffle()->values();
        $quantidade = $equipes->count();
        // Duas equipes disputam diretamente a final
        if ($quantidade === 2) {
            $this->gerarPartidaEntreEquipes($campeonato, $equipes[0], $equipes[1], 'FINAL');
            return;
        }
        // Três equipes disputam um triangular
        if ($quantidade === 3) {
            $this->gerarTriangular($campeonato, $equipes);
            return;
        }
        // Quatro equipes disputam duas semifinais
        if ($quantidade === 4) {
            $this->gerarSemifinais($campeonato, $equipes);
            return;
        }
        // Cinco ou mais equipes começam pelas rodadas iniciais
        $this->gerarConfrontosEmPares($campeonato, $equipes, 'RODADA_INICIAL');
    }

    //Cria uma partida usando objetos ou IDs
    private function gerarPartidaEntreEquipes($campeonato, $mandante, $visitante, $fase)
    {
        $mandanteId = is_object($mandante) ? $mandante->id : $mandante;
        $visitanteId = is_object($visitante) ? $visitante->id : $visitante;
        return $this->criarPartida($campeonato, $mandanteId, $visitanteId, $fase);
    }

    //Cria partidas de duas em duas
    private function gerarConfrontosEmPares($campeonato, $equipes, $fase)
    {
        $equipes = collect($equipes)->unique()->values();
        for ($i = 0; $i < $equipes->count(); $i += 2) {
            if (!isset($equipes[$i + 1])) {
                continue;
            }
            $this->gerarPartidaEntreEquipes($campeonato, $equipes[$i], $equipes[$i + 1], $fase);
        }
    }

    //Gera as semifinais.
    private function gerarSemifinais(Campeonato $campeonato, $equipes)
    {
        $equipes = collect($equipes)->unique()->values();
        if ($equipes->count() !== 4) {
            return;
        }
        $this->gerarConfrontosEmPares($campeonato, $equipes, 'SEMIFINAL');
    }
    //Gera um triangular
    private function gerarTriangular(Campeonato $campeonato, $equipes)
    {
        $equipes = collect($equipes)->unique()->values();
        if ($equipes->count() !== 3) {
            return;
        }
        // Cada equipe joga contra todas as outras
        for ($i = 0; $i < 3; $i++) {
            for ($j = $i + 1; $j < 3; $j++) {
                $this->gerarPartidaEntreEquipes($campeonato, $equipes[$i], $equipes[$j], 'TRIANGULAR');
            }
        }
    }
    //Gera uma nova rodada do mata-mata
    private function gerarRodada(Campeonato $campeonato, $equipes, $fase)
    {
        $equipes = collect($equipes)->unique()->values()->shuffle();
        // Com quatro ou menos equipes, utiliza a estrutura específica
        if ($equipes->count() <= 4) {
            return;
        }
        // Uma equipe passa diretamente quando a quantidade é ímpar
        if ($equipes->count() % 2 !== 0) {
            $equipes->shift();
        }
        $this->gerarConfrontosEmPares($campeonato, $equipes, $fase);
    }

    //Gera uma partida entre duas equipes para a próxima fase
    private function gerarProximaFase($campeonato, $equipes, $fase)
    {
        $equipes = collect($equipes)->unique()->values();
        if ($equipes->count() !== 2) {
            return;
        }
        $this->gerarPartidaEntreEquipes($campeonato, $equipes[0], $equipes[1], $fase);
    }

    //Gera os grupos e suas partidas
    private function gerarGruposMataMata($campeonato, $equipes)
    {
        $equipes = $equipes->shuffle()->values();
        $quantidadeEquipes = $equipes->count();
        // Com menos de cinco equipes, utiliza mata-mata normal
        if ($quantidadeEquipes < 5) {
            $this->gerarMataMata($campeonato, $equipes);
            return;
        }
        // Cada grupo possui no máximo quatro equipes
        $quantidadeGrupos = (int) ceil($quantidadeEquipes / 4);
        $equipesPorGrupo = intdiv($quantidadeEquipes, $quantidadeGrupos);
        $sobra = $quantidadeEquipes % $quantidadeGrupos;
        $grupos = $this->dividirEmGrupos($equipes, $quantidadeGrupos, $equipesPorGrupo, $sobra);
        // Gera as partidas de cada grupo
        foreach ($grupos as $indice => $grupo) {
            $nomeGrupo = 'GRUPO_' . chr(65 + $indice);
            $confrontos = $this->gerarConfrontosGrupo($grupo);
            foreach ($confrontos as $rodada => $jogos) {
                foreach ($jogos as $confronto) {
                    $this->criarPartida($campeonato, $confronto['mandante'], $confronto['visitante'], 'RODADA_' . ($rodada + 1) . '_' . $nomeGrupo);
                }
            }
        }
    }
    //Divide as equipes entre os grupos
    private function dividirEmGrupos($equipes, $quantidadeGrupos, $equipesPorGrupo, $sobra)
    {
        $grupos = collect();
        $posicao = 0;
        for ($i = 0; $i < $quantidadeGrupos; $i++) {
            $tamanhoGrupo = $equipesPorGrupo + ($i < $sobra ? 1 : 0);
            $grupos->push($equipes->slice($posicao, $tamanhoGrupo)->values());
            $posicao += $tamanhoGrupo;
        }
        return $grupos;
    }

    //Gera os confrontos de um grupo
    private function gerarConfrontosGrupo($grupo)
    {
        $equipes = $grupo->values();
        // Adiciona uma folga para grupos com quantidade ímpar
        if ($equipes->count() % 2 !== 0) {
            $equipes->push(null);
        }
        $quantidade = $equipes->count();
        $numeroRodadas = $quantidade - 1;
        $jogosPorRodada = intdiv($quantidade, 2);
        $rodadas = collect();
        for ($rodada = 0; $rodada < $numeroRodadas; $rodada++) {
            $jogos = collect();
            for ($i = 0; $i < $jogosPorRodada; $i++) {
                $mandante = $equipes[$i];
                $visitante = $equipes[$quantidade - 1 - $i];
                // Ignora a equipe que estiver de folga.
                if ($mandante === null || $visitante === null) {
                    continue;
                }
                $jogos->push(['mandante' => $mandante->id, 'visitante' => $visitante->id]);
            }
            $rodadas->push($jogos);
            $equipes = $this->rotacionarEquipes($equipes);
        }
        return $rodadas;
    }

    //Verifica se todos os jogos dos grupos terminaram.
    private function gruposForamFinalizados($campeonato)
    {
        $partidas = $this->buscarPartidasDosGrupos($campeonato);
        if ($partidas->isEmpty()) {
            return false;
        }
        return $partidas->every(fn($partida) => in_array($partida->status, ['FINALIZADA', 'WO']));
    }

    //Busca todas as partidas que pertencem aos grupos
    private function buscarPartidasDosGrupos($campeonato, $somenteFinalizadas = false)
    {
        $query = Partida::where('campeonato_id', $campeonato->id);
        if ($somenteFinalizadas) {
            $query->whereIn('status', ['FINALIZADA', 'WO']);
        }
        return $query->get()->filter(fn($partida) => str_contains($partida->fase, '_GRUPO_'));
    }

    //Busca os classificados dos grupos
    private function buscarClassificadosDosGrupos($campeonato)
    {
        $classificacoes = $this->calcularClassificacaoDosGrupos($campeonato);
        $classificados = collect();
        foreach ($classificacoes as $classificacao) {
            $melhores = $classificacao->take(2);
            foreach ($melhores as $equipe) {
                $classificados->push($equipe['equipe_id']);
            }
        }
        return $classificados->unique()->values();
    }
    //Calcula a classificação de todos os grupos
    private function calcularClassificacaoDosGrupos($campeonato)
    {
        $partidas = $this->buscarPartidasDosGrupos($campeonato, true);
        $grupos = $this->identificarGrupos($partidas);
        $classificacoes = collect();
        foreach ($grupos as $grupo) {
            $partidasGrupo = $partidas->filter(fn($partida) => str_ends_with($partida->fase, $grupo));
            $classificacao = $this->calcularClassificacaoGrupo($partidasGrupo);
            $classificacoes->push($classificacao);
        }
        return $classificacoes;
    }
    //Identifica os grupos existentes nas partidas
    private function identificarGrupos($partidas)
    {
        $grupos = collect();
        foreach ($partidas as $partida) {
            $posicao = strpos($partida->fase, 'GRUPO_');
            if ($posicao !== false) {
                $grupo = substr($partida->fase, $posicao);
                if (!$grupos->contains($grupo)) {
                    $grupos->push($grupo);
                }
            }
        }
        $grupos = $grupos->sort()->values();
        return $grupos;
    }

    //Calcula a classificação de um grupo
    private function calcularClassificacaoGrupo($partidasGrupo)
    {
        $equipes = collect();
        foreach ($partidasGrupo as $partida) {
            $equipes->push($partida->mandante_id);
            $equipes->push($partida->visitante_id);
        }
        $equipes = $equipes->unique()->values();
        $classificacao = collect();
        foreach ($equipes as $equipeId) {
            $classificacao->push($this->calcularEstatisticasEquipe($equipeId, $partidasGrupo));
        }
        $classificacao = $classificacao->sort(function ($a, $b) {
            return $this->compararClassificacao($a, $b);
        })->values();
        return $classificacao;
    }
    //Calcula as estatísticas de uma equipe no grupo
    private function calcularEstatisticasEquipe($equipeId, $partidas)
    {
        $estatisticas = [
            'equipe_id' => $equipeId,
            'pontos' => 0,
            'vitorias' => 0,
            'empates' => 0,
            'derrotas' => 0,
            'gols_marcados' => 0,
            'gols_sofridos' => 0,
            'saldo' => 0,
        ];
        foreach ($partidas as $partida) {
            if ($partida->mandante_id != $equipeId && $partida->visitante_id != $equipeId) {
                continue;
            }
            $ehMandante = $partida->mandante_id == $equipeId;
            $golsMarcados = $ehMandante ? $partida->gols_mandante : $partida->gols_visitante;
            $golsSofridos = $ehMandante ? $partida->gols_visitante : $partida->gols_mandante;
            $estatisticas['gols_marcados'] += $golsMarcados;
            $estatisticas['gols_sofridos'] += $golsSofridos;
            if ($partida->status === 'WO' && $partida->gols_mandante == 0 && $partida->gols_visitante == 0) {
                continue;
            }
            if ($golsMarcados > $golsSofridos) {
                $estatisticas['pontos'] += 3;
                $estatisticas['vitorias']++;
            } elseif ($golsMarcados == $golsSofridos) {
                $estatisticas['pontos']++;
                $estatisticas['empates']++;
            } else {
                $estatisticas['derrotas']++;
            }
        }
        $estatisticas['saldo'] = $estatisticas['gols_marcados'] - $estatisticas['gols_sofridos'];
        return $estatisticas;
    }

    //Define os critérios de desempate da classificação
    private function compararClassificacao($a, $b)
    {
        if ($a['pontos'] != $b['pontos']) {
            return $b['pontos'] <=> $a['pontos'];
        }
        if ($a['saldo'] != $b['saldo']) {
            return $b['saldo'] <=> $a['saldo'];
        }
        if ($a['gols_marcados'] != $b['gols_marcados']) {
            return $b['gols_marcados'] <=> $a['gols_marcados'];
        }
        return 0;
    }
    //Inicia o mata-mata depois da fase de grupos
    private function iniciarMataMataDosGrupos($campeonato)
    {
        // Impede a criação duplicada do mata-mata
        $jaExisteMataMata = Partida::where('campeonato_id', $campeonato->id)->whereIn('fase', ['SEMIFINAL', 'FINAL', 'TRIANGULAR', 'TERCEIRO_LUGAR'])->exists();
        if ($jaExisteMataMata) {
            return;
        }
        $classificados = $this->buscarClassificadosComPosicaoDosGrupos($campeonato);
        if ($classificados->count() < 2) {
            return;
        }
        $quantidade = $classificados->count();
        // Duas equipes vão direto para a final
        if ($quantidade === 2) {
            $this->gerarProximaFase($campeonato, $classificados->pluck('equipe_id'), 'FINAL');
            return;
        }
        // Três equipes disputam triangular
        if ($quantidade === 3) {
            $this->gerarTriangular($campeonato, $classificados->pluck('equipe_id'));
            return;
        }
        // Quatro equipes disputam semifinais
        if ($quantidade === 4) {
            $this->gerarSemifinaisPosGrupos($campeonato, $classificados);
            return;
        }
        // Cinco ou mais equipes começam pela primeira fase
        $this->gerarPrimeiraFasePosGrupos($campeonato, $classificados);
    }
    //Gera as semifinais utilizando a posição dos grupos
    private function gerarSemifinaisPosGrupos($campeonato, $classificados)
    {
        $primeiros = $classificados->where('posicao', 1)->shuffle()->values();
        $segundos = $classificados->where('posicao', 2)->shuffle()->values();
        // É necessário ter dois primeiros e dois segundos
        if ($primeiros->count() !== 2 || $segundos->count() !== 2) {
            return;
        }
        for ($tentativa = 0; $tentativa < 1000; $tentativa++) {
            $segundosTentativa = $segundos->shuffle()->values();
            if (!$this->confrontosEntreGruposValidos($primeiros, $segundosTentativa)) {
                continue;
            }
            for ($i = 0; $i < 2; $i++) {
                $this->criarPartida($campeonato, $primeiros[$i]['equipe_id'], $segundosTentativa[$i]['equipe_id'], 'SEMIFINAL');
            }
            return;
        }
    }
    //Verifica se os confrontos entre classificados são válidos
    private function confrontosEntreGruposValidos($primeiros, $segundos)
    {
        for ($i = 0; $i < $primeiros->count(); $i++) {
            if ($primeiros[$i]['grupo'] === $segundos[$i]['grupo']) {
                return false;
            }
            if ($primeiros[$i]['posicao'] === $segundos[$i]['posicao']) {
                return false;
            }
        }
        return true;
    }
    //Descobre o vencedor de uma partida
    private function buscarVencedorDaPartida($partida)
    {
        // Primeiro verifica o resultado dos gols
        if ($partida->gols_mandante > $partida->gols_visitante) {
            return $partida->mandante_id;
        }
        if ($partida->gols_visitante > $partida->gols_mandante) {
            return $partida->visitante_id;
        }
        // Em caso de empate, verifica os pênaltis
        [$mandante, $visitante] = $this->contarPenaltis($partida);

        if ($mandante > $visitante) {
            return $partida->mandante_id;
        }
        if ($visitante > $mandante) {
            return $partida->visitante_id;
        }
        return null;
    }

    //Busca os vencedores de uma fase
    private function buscarVencedoresDaFase(Campeonato $campeonato, $fase)
    {
        $partidas = $this->buscarPartidasFinalizadasDaFase($campeonato, $fase);
        $vencedores = collect();
        foreach ($partidas as $partida) {
            $vencedor = $this->buscarVencedorDaPartida($partida);
            if ($vencedor) {
                $vencedores->push($vencedor);
            }
        }
        return $vencedores->unique()->values();
    }
    //Busca os perdedores de uma fase
    private function buscarPerdedoresDaFase(Campeonato $campeonato, $fase)
    {
        $perdedores = collect();
        foreach ($this->buscarPartidasFinalizadasDaFase($campeonato, $fase) as $partida) {
            $vencedor = $this->buscarVencedorDaPartida($partida);
            if ($vencedor === $partida->mandante_id) {
                $perdedores->push($partida->visitante_id);
            } elseif ($vencedor === $partida->visitante_id) {
                $perdedores->push($partida->mandante_id);
            }
        }
        return $perdedores->unique()->values();
    }
    //Busca as partidas finalizadas de uma fase
    private function buscarPartidasFinalizadasDaFase(Campeonato $campeonato, $fase)
    {
        return Partida::where('campeonato_id', $campeonato->id)->where('fase', $fase)->whereIn('status', ['FINALIZADA', 'WO'])->get();
    }
    //Descobre qual é a fase anterior
    private function buscarFaseAnterior($fase)
    {
        if ($fase === 'RODADA_INICIAL') {
            return null;
        }
        if (str_starts_with($fase, 'RODADA_')) {
            $numero = (int) str_replace('RODADA_', '', $fase);
            if ($numero <= 2) {
                return 'RODADA_INICIAL';
            }
            return 'RODADA_' . ($numero - 1);
        }
        return null;
    }
    //Busca as equipes que deveriam participar de uma fase
    private function buscarEquipesDaFase($campeonato, $fase)
    {
        // A primeira rodada começa com todas as equipes
        if ($fase === 'RODADA_INICIAL') {
            // No grupos + mata-mata começam somente os classificados
            if ($campeonato->tipo === 'GRUPOS_MATA_MATA') {
                return $this->buscarClassificadosDosGrupos($campeonato);
            }
            return Inscricao::where('campeonato_id', $campeonato->id)->pluck('equipe_id')->unique()->values();
        }
        // Nas demais rodadas busca os classificados da fase anterior
        $faseAnterior = $this->buscarFaseAnterior($fase);
        if ($faseAnterior) {
            return $this->buscarEquipesQueAvancaramDaFase($campeonato, $faseAnterior);
        }
        return collect();
    }
    //Busca as equipes que avançaram de uma fase
    private function buscarEquipesQueAvancaramDaFase(Campeonato $campeonato, $fase)
    {
        $equipes = $this->buscarEquipesDaFase($campeonato, $fase);
        $partidas = $this->buscarPartidasFinalizadasDaFase($campeonato, $fase);
        $vencedores = collect();
        $jogaram = collect();
        foreach ($partidas as $partida) {
            $vencedor = $this->buscarVencedorDaPartida($partida);
            if ($vencedor) {
                $vencedores->push($vencedor);
            }
            $jogaram->push($partida->mandante_id);
            $jogaram->push($partida->visitante_id);
        }
        // Guarda as equipes que realmente jogaram
        $jogaram = $jogaram->unique()->values();
        // Quem estava na fase e não jogou passou diretamente
        $equipesQueNaoJogaram = $equipes->diff($jogaram);
        return $vencedores->merge($equipesQueNaoJogaram)->unique()->values();
    }

    //Descobre a próxima fase numerada
    private function buscarProximaFase($fase)
    {
        if ($fase === 'RODADA_INICIAL') {
            return 'RODADA_2';
        }
        if (str_starts_with($fase, 'RODADA_')) {
            $numero = (int) str_replace('RODADA_', '', $fase);
            return 'RODADA_' . ($numero + 1);
        }
        return 'RODADA_2';
    }

    //Finaliza uma partida e avança o campeonato
    public function finalizar(Partida $partida)
    {
        if ($partida->status !== 'AGENDADA') {
            return back()->withErrors(['partida' => 'Esta partida não está disponível para finalização.']);
        }
        $campeonato = $partida->campeonato;
        $mandantePodeJogar = $this->equipePodeJogar($partida->mandante_id, $campeonato);
        $visitantePodeJogar = $this->equipePodeJogar($partida->visitante_id, $campeonato);
        if (!$mandantePodeJogar || !$visitantePodeJogar) {
            return back()->withErrors(['partida' => 'Esta partida deve ser finalizada por WO, pois uma das equipes está impedida de jogar']);
        }
        $campeonato = $partida->campeonato;
        // Pontos corridos e grupos permitem empate
        $permiteEmpate = $campeonato->tipo === 'PONTOS_CORRIDOS' || $partida->fase === 'TRIANGULAR' || str_contains($partida->fase, '_GRUPO_');
        // Mata-mata precisa obrigatoriamente de um vencedor
        if (!$permiteEmpate && $partida->gols_mandante == $partida->gols_visitante && $this->buscarVencedorDaPartida($partida) === null) {
            return back()->withInput()->withErrors(['partida' => 'A partida está empatada. É necessário definir um vencedor nos pênaltis antes de finalizar.']);
        }
        // Marca a partida como finalizada
        $partida->update(['status' => 'FINALIZADA']);
        // Pontos corridos não possui próxima fase
        if ($campeonato->tipo === 'PONTOS_CORRIDOS') {
            return $this->finalizarPontosCorridos($campeonato);
        }
        // Espera todas as partidas da fase terminarem
        if ($this->existemPartidasPendentesDaFase($partida)) {
            return $this->redirecionarPartidas();
        }
        // Quando todos os grupos terminarem, inicia o mata-mata
        if ($campeonato->tipo === 'GRUPOS_MATA_MATA' && str_contains($partida->fase, '_GRUPO_')) {
            if ($this->gruposForamFinalizados($campeonato)) {
                $this->iniciarMataMataDosGrupos($campeonato);
            }
            return $this->redirecionarPartidas();
        }
        // Partidas de grupo não possuem próxima fase individual
        if (str_contains($partida->fase, '_GRUPO_')) {
            return $this->redirecionarPartidas();
        }
        // Trata as rodadas do mata-mata
        if ($this->ehRodadaDeMataMata($partida->fase)) {
            $this->processarFimDaRodada($campeonato, $partida);
        } elseif ($partida->fase === 'SEMIFINAL') {
            $this->processarFimDaSemifinal($campeonato);
        } elseif ($partida->fase === 'FINAL' || $partida->fase === 'TERCEIRO_LUGAR') {
            $this->processarFimDaFinal($campeonato);
        } elseif ($partida->fase === 'TRIANGULAR') {
            // Mantém o comportamento original: terminou o triangular, termina o campeonato
            $campeonato->update(['status' => 'FINALIZADO']);
        }
        return $this->redirecionarPartidas();
    }
    //Finaliza o campeonato de pontos corridos quando todos os jogos terminam.
    private function finalizarPontosCorridos($campeonato)
    {
        $existemPendentes = Partida::where('campeonato_id', $campeonato->id)->whereNotIn('status', ['FINALIZADA', 'WO'])->exists();
        if (!$existemPendentes) {
            $campeonato->update(['status' => 'FINALIZADO']);
        }
        return $this->redirecionarPartidas();
    }
    //Verifica se ainda existem partidas pendentes na fase
    private function existemPartidasPendentesDaFase($partida)
    {
        return Partida::where('campeonato_id', $partida->campeonato_id)->where('fase', $partida->fase)->whereNotIn('status', ['FINALIZADA', 'WO'])->exists();
    }
    //Verifica se a fase é uma rodada do mata-mata
    private function ehRodadaDeMataMata($fase)
    {
        return $fase === 'RODADA_INICIAL' || str_starts_with($fase, 'RODADA_');
    }

    //Processa o término de uma rodada do mata-mata
    private function processarFimDaRodada($campeonato, $partida)
    {
        $equipes = $this->buscarEquipesQueAvancaramDaFase($campeonato, $partida->fase);
        if ($equipes->count() === 2) {
            $this->gerarProximaFase($campeonato, $equipes, 'FINAL');
        } elseif ($equipes->count() === 3) {
            $this->gerarTriangular($campeonato, $equipes);
        } elseif ($equipes->count() === 4) {
            $this->gerarSemifinais($campeonato, $equipes);
        } elseif ($equipes->count() > 4) {
            $this->gerarRodada($campeonato, $equipes, $this->buscarProximaFase($partida->fase));
        }
    }
    //Processa o término das semifinais
    private function processarFimDaSemifinal($campeonato)
    {
        $vencedores = $this->buscarVencedoresDaFase($campeonato, 'SEMIFINAL');
        $perdedores = $this->buscarPerdedoresDaFase($campeonato, 'SEMIFINAL');
        // Cria final e terceiro lugar
        if ($vencedores->count() === 2 && $perdedores->count() === 2) {
            $this->gerarProximaFase($campeonato, $vencedores, 'FINAL');
            $this->gerarProximaFase($campeonato, $perdedores, 'TERCEIRO_LUGAR');
        }
    }
    //Processa o término da final e terceiro lugar
    private function processarFimDaFinal($campeonato)
    {
        $finalTerminou = Partida::where('campeonato_id', $campeonato->id)->where('fase', 'FINAL')->whereIn('status', ['FINALIZADA', 'WO'])->exists();
        $terceiroLugarExiste = Partida::where('campeonato_id', $campeonato->id)->where('fase', 'TERCEIRO_LUGAR')->exists();
        $terceiroLugarTerminou = Partida::where('campeonato_id', $campeonato->id)->where('fase', 'TERCEIRO_LUGAR')->whereIn('status', ['FINALIZADA', 'WO'])->exists();
        if ($finalTerminou && (!$terceiroLugarExiste || $terceiroLugarTerminou)) {
            $campeonato->update(['status' => 'FINALIZADO']);
        }
    }
    //Busca classificados dos grupos com sua posição
    private function buscarClassificadosComPosicaoDosGrupos($campeonato)
    {
        $classificacoes = $this->calcularClassificacaoDosGrupos($campeonato);
        $classificados = collect();
        foreach ($classificacoes as $indice => $classificacao) {
            $grupo = 'GRUPO_' . chr(65 + $indice);
            foreach ($classificacao->take(2) as $posicao => $classificado) {
                $classificados->push(['equipe_id' => $classificado['equipe_id'], 'grupo' => $grupo, 'posicao' => $posicao + 1,]);
            }
        }
        return $classificados;
    }
    //Gera a primeira fase do mata-mata após os grupos
    private function gerarPrimeiraFasePosGrupos($campeonato, $classificados)
    {
        $primeiros = $classificados->where('posicao', 1)->shuffle()->values();
        $segundos = $classificados->where('posicao', 2)->shuffle()->values();
        // Tenta encontrar confrontos entre grupos diferentes
        for ($tentativa = 0; $tentativa < 1000; $tentativa++) {
            $segundosTentativa = $segundos->shuffle()->values();
            if (!$this->confrontosEntreGruposValidos($primeiros, $segundosTentativa)) {
                continue;
            }
            for ($i = 0; $i < $primeiros->count(); $i++) {
                $this->criarPartida($campeonato, $primeiros[$i]['equipe_id'], $segundosTentativa[$i]['equipe_id'], 'RODADA_INICIAL');
            }
            return;
        }
    }
    public function wo(Partida $partida)
    {
        if ($partida->status !== 'AGENDADA') {
            return back()->withErrors(['partida' => 'Esta partida não pode ser finalizada por WO.']);
        }
        $campeonato = $partida->campeonato;
        $mandantePodeJogar = $this->equipePodeJogar($partida->mandante_id, $campeonato);
        $visitantePodeJogar = $this->equipePodeJogar($partida->visitante_id, $campeonato);
        if ($mandantePodeJogar && $visitantePodeJogar) {
            return back()->withErrors(['partida' => 'Esta partida não pode ser finalizada por WO, pois ambas as equipes estão aptas a jogar']);
        }
        // WO DUPLO
        if (!$mandantePodeJogar && !$visitantePodeJogar) {
            $partida->update(['gols_mandante' => 0, 'gols_visitante' => 0, 'status' => 'WO',]);
            return $this->processarPartidaFinalizada($partida);
        }
        // WO do mandante
        if (!$mandantePodeJogar) {
            $partida->update(['gols_mandante' => 0, 'gols_visitante' => 3, 'status' => 'WO',]);
            return $this->processarPartidaFinalizada($partida);
        }
        // WO do visitante
        $partida->update(['gols_mandante' => 3, 'gols_visitante' => 0, 'status' => 'WO',]);
        return $this->processarPartidaFinalizada($partida);
    }

    private function equipePodeJogar($equipeId, $campeonato)
    {
        $equipe = Equipe::find($equipeId);
        // Equipe suspensa não pode jogar
        if ($equipe->status === 'SUSPENSA') {
            return false;
        }
        // Verifica a inscrição da equipe neste campeonato
        $inscricao = Inscricao::where('campeonato_id', $campeonato->id)->where('equipe_id', $equipeId)->first();
        // Inscrição suspensa não pode jogar
        if (!$inscricao || $inscricao->status === 'SUSPENSA') {
            return false;
        }
        // Conta somente jogadores aptos
        $totalJogadores = Contrato::where('equipe_id', $equipeId)->where('status', 'ATIVO')
            ->whereHas('participante', function ($query) {
                $query->where('status', 'ATIVO')
                    ->whereNotIn('funcao', [
                        'TECNICO',
                        'AUXILIAR_TECNICO',
                        'PREPARADOR_FISICO',
                    ]);
            })->count();
        // Abaixo do mínimo não pode jogar
        if ($totalJogadores < $campeonato->minimo_jogadores_equipes) {
            return false;
        }
        return true;
    }

    private function processarPartidaFinalizada(Partida $partida)
    {
        $campeonato = $partida->campeonato;
        if ($campeonato->tipo === 'PONTOS_CORRIDOS') {
            return $this->finalizarPontosCorridos($campeonato);
        }
        if ($this->existemPartidasPendentesDaFase($partida)) {
            return $this->redirecionarPartidas();
        }
        if ($campeonato->tipo === 'GRUPOS_MATA_MATA' && str_contains($partida->fase, '_GRUPO_')) {
            if ($this->gruposForamFinalizados($campeonato)) {
                $this->iniciarMataMataDosGrupos($campeonato);
            }
            return $this->redirecionarPartidas();
        }
        if (str_contains($partida->fase, '_GRUPO_')) {
            return $this->redirecionarPartidas();
        }
        if ($this->ehRodadaDeMataMata($partida->fase)) {
            $this->processarFimDaRodada($campeonato, $partida);
        } elseif ($partida->fase === 'SEMIFINAL') {
            $this->processarFimDaSemifinal($campeonato);
        } elseif (
            $partida->fase === 'FINAL' ||
            $partida->fase === 'TERCEIRO_LUGAR'
        ) {
            $this->processarFimDaFinal($campeonato);
        } elseif ($partida->fase === 'TRIANGULAR') {
            $campeonato->update(['status' => 'FINALIZADO']);
        }
        return $this->redirecionarPartidas();
    }

    //Redireciona para a lista de partidas mantendo o campeonato selecionado
    private function redirecionarPartidas()
    {
        return redirect()->route('partidas.index', ['campeonato_id' => request('campeonato_id'), 'campeonato_nome' => request('campeonato_nome')])->with('success', 'Partida finalizada com sucesso.');
    }


    public function sumula(Partida $partida)
    {
        $partida->load(['campeonato', 'mandante.contratos.participante', 'visitante.contratos.participante',]);
        $funcoesComissao = ['TECNICO', 'AUXILIAR_TECNICO', 'PREPARADOR_FISICO'];// Funções da comissão técnica
        // Jogadores do mandante
        $jogadoresMandante = $partida->mandante->contratos->where('status', 'ATIVO')->filter(function ($contrato) use ($funcoesComissao) {
            if (!$contrato->participante) {
                return false;
            }
            if (in_array($contrato->participante->funcao, $funcoesComissao)) {
                return false;
            }
            return true;
        })->values();
        // Jogadores do visitante
        $jogadoresVisitante = $partida->visitante->contratos->where('status', 'ATIVO')->filter(function ($contrato) use ($funcoesComissao) {
            if (!$contrato->participante) {
                return false;
            }
            if (in_array($contrato->participante->funcao, $funcoesComissao)) {
                return false;
            }
            return true;
        })->values();
        // Comissão técnica do mandante
        $comissaoMandante = $partida->mandante->contratos->where('status', 'ATIVO')->filter(function ($contrato) use ($funcoesComissao) {
            if (!$contrato->participante) {
                return false;
            }
            if (!in_array($contrato->participante->funcao, $funcoesComissao)) {
                return false;
            }
            return true;
        })->values();
        // Comissão técnica do visitante
        $comissaoVisitante = $partida->visitante->contratos->where('status', 'ATIVO')->filter(function ($contrato) use ($funcoesComissao) {
            if (!$contrato->participante) {
                return false;
            }
            if (!in_array($contrato->participante->funcao, $funcoesComissao)) {
                return false;
            }
            return true;
        })->values();
        $pdf = Pdf::loadView('areaAdministrativa.partidas.sumula', compact('partida', 'jogadoresMandante', 'jogadoresVisitante', 'comissaoMandante', 'comissaoVisitante'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('sumula-partida-' . $partida->id . '.pdf');
    }
}