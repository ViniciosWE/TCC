<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\EventoPartida;
use Illuminate\Http\Request;

class CampeonatoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $campeonatos = Campeonato::withCount('inscricoes')->latest()->get();
        return view('areaAdministrativa.campeonatos.index', compact('campeonatos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('areaAdministrativa.campeonatos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Valida os dados enviados pelo formulário
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'minimo_jogadores_equipes' => 'required|integer|min:5',
            'maximo_equipes' => 'required|integer|min:2',
            'tipo' => 'required|in:MATA_MATA,GRUPOS_MATA_MATA,PONTOS_CORRIDOS',
            'categoria' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'status' => 'required|in:INSCRICOES,EM_ANDAMENTO,FINALIZADO',
        ], [
            'minimo_jogadores_equipes.min' => 'O número mínimo de jogadores por equipe deve ser de pelo menos 5.',
            'maximo_equipes.min' => 'A quantidade máxima de equipes deve ser de pelo menos 2.',
            'data_fim.after_or_equal' => 'A data de término não pode ser anterior à data de início.',
        ]);

        $dados['user_id'] = auth()->id();// Pega o id de quem criou
        Campeonato::create($dados);// Cria o registro no banco
        return redirect()->route('campeonatos.index')->with('success', 'Campeonato cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Campeonato $campeonato)
    {
        $classificacao = collect();
        $temTriangular = false;
        $classificacaoMataMata = collect();
        $semifinais = collect();
        $final = null;
        $terceiroLugar = null;
        $classificacoesGrupos = collect();
        $partidasGrupos = collect();
        $partidasMataMata = collect();
        // Pontos corridos
        if ($campeonato->tipo === 'PONTOS_CORRIDOS') {
            $classificacao = $this->calcularClassificacaoPontosCorridos($campeonato);
        }
        // Mata-mata
        elseif ($campeonato->tipo === 'MATA_MATA') {
            $temTriangular = $campeonato->partidas()->where('fase', 'TRIANGULAR')->exists();
            // Se tiver triangular
            if ($temTriangular) {
                $classificacao = $this->calcularClassificacaoPontosCorridos($campeonato, 'TRIANGULAR');
            }
            // Se não tiver triangular
            else {
                $semifinais = $campeonato->partidas()->where('fase', 'SEMIFINAL')->orderBy('id')->get();
                // Calcula os pênaltis das semifinais
                foreach ($semifinais as $partida) {
                    $this->calcularPenaltis($partida);
                }
                $final = $campeonato->partidas()->where('fase', 'FINAL')->first();
                $terceiroLugar = $campeonato->partidas()->where('fase', 'TERCEIRO_LUGAR')->first();
                // Calcula os pênaltis da final
                if ($final) {
                    $this->calcularPenaltis($final);
                }
                // Calcula os pênaltis do terceiro lugar
                if ($terceiroLugar) {
                    $this->calcularPenaltis($terceiroLugar);
                }
                // Monta a classificação final do mata-mata
                $this->montarClassificacaoFinal($classificacaoMataMata, $final, $terceiroLugar);
            }
        }
        // Grupos e mata-mata
        elseif ($campeonato->tipo === 'GRUPOS_MATA_MATA') {
            $classificacoesGrupos = $this->calcularClassificacoesDosGrupos($campeonato);
            $partidasGrupos = $campeonato->partidas()->where('fase', 'like', '%_GRUPO_%')->with(['mandante', 'visitante'])->orderBy('fase')->orderBy('id')->get();
            $partidasMataMata = $campeonato->partidas()
                ->where(function ($query) {
                    $query->whereIn('fase', [
                        'RODADA_INICIAL',
                        'SEMIFINAL',
                        'FINAL',
                        'TERCEIRO_LUGAR',
                        'TRIANGULAR'
                    ])->orWhere('fase', 'like', 'RODADA_%');
                })->where('fase', 'not like', '%_GRUPO_%')->with(['mandante', 'visitante'])->orderBy('id')->get();
            // Verifica se existe triangular
            $temTriangular = $partidasMataMata->where('fase', 'TRIANGULAR')->isNotEmpty();
            if ($temTriangular) {
                $classificacao = $this->calcularClassificacaoPontosCorridos($campeonato, 'TRIANGULAR');
            }
            $semifinais = $partidasMataMata->where('fase', 'SEMIFINAL')->values();
            // Calcula os pênaltis das semifinais
            foreach ($semifinais as $partida) {
                $this->calcularPenaltis($partida);
            }
            $final = $partidasMataMata->where('fase', 'FINAL')->first();
            $terceiroLugar = $partidasMataMata->where('fase', 'TERCEIRO_LUGAR')->first();
            // Calcula os pênaltis da final
            if ($final) {
                $this->calcularPenaltis($final);
            }
            // Calcula os pênaltis do terceiro lugar
            if ($terceiroLugar) {
                $this->calcularPenaltis($terceiroLugar);
            }
            $this->montarClassificacaoFinal($classificacaoMataMata, $final, $terceiroLugar); // Monta a classificação final do mata-mata
        }
        // Envia todas as informações para a página
        return view(
            'areaAdministrativa.campeonatos.show',
            compact('campeonato', 'classificacao', 'temTriangular', 'classificacaoMataMata', 'semifinais', 'final', 'terceiroLugar', 'classificacoesGrupos', 'partidasGrupos', 'partidasMataMata')
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campeonato $campeonato)
    {
        return view('areaAdministrativa.campeonatos.edit', compact('campeonato'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campeonato $campeonato)
    {
        // Valida os dados enviados pelo formulário
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'minimo_jogadores_equipes' => 'required|integer|min:5',
            'maximo_equipes' => 'required|integer|min:2',
            'tipo' => 'required|in:MATA_MATA,GRUPOS_MATA_MATA,PONTOS_CORRIDOS',
            'categoria' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'status' => 'required|in:INSCRICOES,EM_ANDAMENTO,FINALIZADO',
        ], [
            'minimo_jogadores_equipes.min' => 'O número mínimo de jogadores por equipe deve ser de pelo menos 5.',
            'maximo_equipes.min' => 'A quantidade máxima de equipes deve ser de pelo menos 2.',
            'data_fim.after_or_equal' => 'A data de término não pode ser anterior à data de início.',
        ]);
        // Conta quantas equipes já estão inscritas
        $totalInscricoes = $campeonato->inscricoes()->count();
        // Impede diminuir o limite abaixo do número de inscrições existentes
        if ($dados['maximo_equipes'] < $totalInscricoes) {
            return back()->withInput()->withErrors(['maximo_equipes' => "Não é possível definir menos de {$totalInscricoes} equipes, pois já existem {$totalInscricoes} inscrições neste campeonato"]);
        }
        // Verifica se o campeonato pode voltar para inscrições
        if ($campeonato->status === 'EM_ANDAMENTO' && $dados['status'] === 'INSCRICOES') {
            $partidaComEvento = $campeonato->partidas()->whereHas('eventos')->exists();
            if ($partidaComEvento) {
                return back()->withInput()->withErrors(['status' => 'Não é possível voltar o campeonato para inscrições, pois já existem eventos cadastrados em uma ou mais partidas']);
            }
            // Se não existem eventos, pode apagar as partidas
            $campeonato->partidas()->delete();
        }
        // Verifica se o campeonato pode ser finalizado
        if ($dados['status'] === 'FINALIZADO' && $campeonato->status !== 'FINALIZADO') {
            $totalPartidas = $campeonato->partidas()->count();
            $partidasFinalizadas = $campeonato->partidas()->where('status', 'FINALIZADA')->count();
            if ($totalPartidas === 0) {
                return back()->withInput()->withErrors(['status' => 'Não é possível finalizar um campeonato que não possui partidas']);
            }
            if ($totalPartidas !== $partidasFinalizadas) {
                return back()->withInput()->withErrors(['status' => 'Não é possível finalizar o campeonato enquanto existirem partidas não finalizadas']);
            }
        }
        // Pega o id de quem atualizou
        $dados['user_id'] = auth()->id();
        // Atualiza o campeonato
        $campeonato->update($dados);
        return redirect()->route('campeonatos.index')->with('success', 'Campeonato atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campeonato $campeonato)
    {
        // Verifica se existem notícias ou inscrições
        $possuiNoticia = $campeonato->noticias()->exists();
        $possuiInscricoes = $campeonato->inscricoes()->exists();
        if ($possuiInscricoes && $possuiNoticia) {
            return redirect()->route('campeonatos.index')->with('error', 'O campeonato possui inscrições e notícias relacionados e não pode ser excluído');
        }
        if ($possuiInscricoes) {
            return redirect()->route('campeonatos.index')->with('error', 'O campeonato possui inscrições e não pode ser excluído');
        }
        if ($possuiNoticia) {
            return redirect()->route('campeonatos.index')->with('error', 'O campeonato possui Notícias relacionadas e não pode ser excluído');
        }
        $campeonato->delete();
        return redirect()->route('campeonatos.index')->with('success', 'Campeonato excluído com sucesso!');
    }

    // Calcula a classificação de pontos corridos
    private function calcularClassificacaoPontosCorridos(Campeonato $campeonato, $fase = null)
    {
        $campeonato->load('inscricoes.equipe');
        $query = $campeonato->partidas();
        // Se for uma fase específica filtra pela fase
        if ($fase) {
            $query->where('fase', $fase);
        }
        $partidas = $query->get();
        $equipes = $campeonato->inscricoes->pluck('equipe');
        // Se for uma fase específica pega somente as equipes que participaram dela
        if ($fase) {
            $idsEquipes = collect();
            foreach ($partidas as $partida) {
                $idsEquipes->push($partida->mandante_id);
                $idsEquipes->push($partida->visitante_id);
            }
            $idsEquipes = $idsEquipes->unique();
            $equipes = $equipes->filter(function ($equipe) use ($idsEquipes) {
                return $idsEquipes->contains($equipe->id);
            })->values();
        }
        return $this->calcularClassificacao($partidas, $equipes);
    }

    // Calcula uma classificação com base nas partidas recebidas
    private function calcularClassificacao($partidas, $equipes)
    {
        $classificacao = [];
        foreach ($equipes as $equipe) {
            $classificacao[$equipe->id] = [
                'equipe' => $equipe,
                'jogos' => 0,
                'vitorias' => 0,
                'empates' => 0,
                'derrotas' => 0,
                'gols_pro' => 0,
                'gols_contra' => 0,
                'saldo' => 0,
                'pontos' => 0,
            ];
        }
        foreach ($partidas as $partida) {
            if (!in_array($partida->status, ['FINALIZADA', 'WO'])) {
                continue;
            }
            $mandante = $partida->mandante_id;
            $visitante = $partida->visitante_id;
            if (!isset($classificacao[$mandante], $classificacao[$visitante])) {
                continue;
            }
            if ($partida->status === 'WO' && $partida->gols_mandante == 0 && $partida->gols_visitante == 0) {
                $classificacao[$mandante]['jogos']++;
                $classificacao[$visitante]['jogos']++;
                continue;
            }
            if ($partida->status === 'WO') {
                $classificacao[$mandante]['jogos']++;
                $classificacao[$visitante]['jogos']++;
                if ($partida->gols_mandante > $partida->gols_visitante) {
                    $classificacao[$mandante]['vitorias']++;
                    $classificacao[$mandante]['pontos'] += 3;
                    $classificacao[$visitante]['derrotas']++;
                } else {
                    $classificacao[$visitante]['vitorias']++;
                    $classificacao[$visitante]['pontos'] += 3;
                    $classificacao[$mandante]['derrotas']++;
                }
                continue;
            }
            // Conta os jogos
            $classificacao[$mandante]['jogos']++;
            $classificacao[$visitante]['jogos']++;
            // Calcula os gols somente de partidas normais
            $classificacao[$mandante]['gols_pro'] += $partida->gols_mandante;
            $classificacao[$mandante]['gols_contra'] += $partida->gols_visitante;
            $classificacao[$visitante]['gols_pro'] += $partida->gols_visitante;
            $classificacao[$visitante]['gols_contra'] += $partida->gols_mandante;
            // Calcula o resultado
            if ($partida->gols_mandante > $partida->gols_visitante) {
                $classificacao[$mandante]['vitorias']++;
                $classificacao[$mandante]['pontos'] += 3;
                $classificacao[$visitante]['derrotas']++;
            } elseif ($partida->gols_mandante < $partida->gols_visitante) {
                $classificacao[$visitante]['vitorias']++;
                $classificacao[$visitante]['pontos'] += 3;
                $classificacao[$mandante]['derrotas']++;
            } else {
                $classificacao[$mandante]['empates']++;
                $classificacao[$visitante]['empates']++;
                $classificacao[$mandante]['pontos']++;
                $classificacao[$visitante]['pontos']++;
            }
        }
        // Calcula o saldo de gols
        foreach ($classificacao as &$equipe) {
            $equipe['saldo'] = $equipe['gols_pro'] - $equipe['gols_contra'];
        }
        // Ordena a classificação
        usort($classificacao, function ($a, $b) {
            if ($a['pontos'] != $b['pontos']) {
                return $b['pontos'] - $a['pontos'];
            }
            if ($a['saldo'] != $b['saldo']) {
                return $b['saldo'] - $a['saldo'];
            }
            return $b['gols_pro'] - $a['gols_pro'];
        });
        // Adiciona a posição
        foreach ($classificacao as $posicao => &$equipe) {
            $equipe['posicao'] = $posicao + 1;
        }
        return $classificacao;
    }
    // Calcula os pênaltis da partida
    private function calcularPenaltis($partida)
    {
        // Busca os pênaltis convertidos da partida
        $penaltis = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'PENALTI_CONVERTIDO_DESEMPATE')->with('participante')->get();
        $mandante = 0;
        $visitante = 0;
        // Percorre todos os pênaltis
        foreach ($penaltis as $penalti) {
            if (!$penalti->participante) {
                continue;
            }
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
        $partida->penaltisMandante = $mandante;
        $partida->penaltisVisitante = $visitante;
        return $partida;
    }
    // Identifica os grupos existentes nas partidas
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
        return $grupos->sort()->values();
    }

    // Calcula a classificação de todos os grupos
    private function calcularClassificacoesDosGrupos(Campeonato $campeonato)
    {
        $campeonato->load('inscricoes.equipe');
        $partidas = $campeonato->partidas()->where('fase', 'like', '%_GRUPO_%')->with(['mandante', 'visitante'])->orderBy('fase')->orderBy('id')->get();
        $grupos = $this->identificarGrupos($partidas);
        $classificacoes = collect();
        foreach ($grupos as $grupo) {
            // Pega somente as partidas deste grupo
            $partidasGrupo = $partidas->filter(function ($partida) use ($grupo) {
                return str_ends_with($partida->fase, $grupo);
            });
            // Descobre as equipes que participaram do grupo
            $equipes = collect();
            foreach ($partidasGrupo as $partida) {
                $equipeMandante = $campeonato->inscricoes->firstWhere('equipe_id', $partida->mandante_id)?->equipe;
                $equipeVisitante = $campeonato->inscricoes->firstWhere('equipe_id', $partida->visitante_id)?->equipe;
                if ($equipeMandante) {
                    $equipes->push($equipeMandante);
                }
                if ($equipeVisitante) {
                    $equipes->push($equipeVisitante);
                }
            }
            $equipes = $equipes->unique('id')->values();
            // Calcula a classificação usando a mesma regra dos pontos corridos
            $classificacao = $this->calcularClassificacao($partidasGrupo, $equipes);
            $classificacoes[$grupo] = collect($classificacao);
        }
        return $classificacoes;
    }

    // Monta a classificação final do mata-mata
    private function montarClassificacaoFinal(&$classificacaoMataMata, $final, $terceiroLugar)
    {
        if ($final) {
            $vencedorFinal = $this->buscarVencedorDaPartida($final);
            $perdedorFinal = $this->buscarPerdedorDaPartida($final);
            // Adiciona o campeão
            if ($vencedorFinal) {
                $classificacaoMataMata->push(['posicao' => 1, 'equipe' => $vencedorFinal]);
            }
            // Adiciona o vice-campeão
            if ($perdedorFinal) {
                $classificacaoMataMata->push(['posicao' => 2, 'equipe' => $perdedorFinal]);
            }
        }

        // Verifica o terceiro lugar
        if ($terceiroLugar) {
            $terceiro = $this->buscarVencedorDaPartida($terceiroLugar);
            if ($terceiro) {
                $classificacaoMataMata->push(['posicao' => 3, 'equipe' => $terceiro]);
            }
        }
    }

    // Busca o vencedor de uma partida
    private function buscarVencedorDaPartida($partida)
    {
        if ($partida->gols_mandante > $partida->gols_visitante) {
            return $partida->mandante;
        }
        if ($partida->gols_mandante < $partida->gols_visitante) {
            return $partida->visitante;
        }
        if ($partida->penaltisMandante > $partida->penaltisVisitante) {
            return $partida->mandante;
        }
        if ($partida->penaltisMandante < $partida->penaltisVisitante) {
            return $partida->visitante;
        }
        return null;
    }

    // Busca o perdedor de uma partida
    private function buscarPerdedorDaPartida($partida)
    {
        $vencedor = $this->buscarVencedorDaPartida($partida);
        if (!$vencedor) {
            return null;
        }
        if ($vencedor->id == $partida->mandante_id) {
            return $partida->visitante;
        }
        return $partida->mandante;
    }
}