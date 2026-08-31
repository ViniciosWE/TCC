<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Contrato;
use App\Models\EventoPartida;
use App\Models\Partida;
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

        $dados['user_id'] = auth()->id(); //pega o id de quem criou 
        Campeonato::create($dados); // cria o registro no banco
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
        //pontos corridos
        if ($campeonato->tipo === 'PONTOS_CORRIDOS') {
            $classificacao = $this->calcularClassificacaoPontosCorridos($campeonato);//calcula a classificação do campeonato
        }
        // mata-mata
        elseif ($campeonato->tipo === 'MATA_MATA') {
            $temTriangular = $campeonato->partidas()->where('fase', 'TRIANGULAR')->exists();// Verifica se o campeonato possui uma fase triangular
            // se tiver triangular
            if ($temTriangular) {
                $classificacao = $this->calcularClassificacaoPontosCorridos($campeonato, 'TRIANGULAR');//calcula a classificação do triangular
            }
            //se não tiver triangular
            else {
                $semifinais = $campeonato->partidas()->where('fase', 'SEMIFINAL')->orderBy('id')->get();//busca as semifinais
                // Calcula os pênaltis das semifinais
                foreach ($semifinais as $partida) {
                    $this->calcularPenaltis($partida);
                }
                $final = $campeonato->partidas()->where('fase', 'FINAL')->where('status', 'FINALIZADA')->first();//busca a finalizada
                $terceiroLugar = $campeonato->partidas()->where('fase', 'TERCEIRO_LUGAR')->where('status', 'FINALIZADA')->first();//busca o terceiro lugar finalizado
                //calcula os pênaltis da final
                if ($final) {
                    $this->calcularPenaltis($final);
                }
                //calcula os pênaltis do terceiro lugar
                if ($terceiroLugar) {
                    $this->calcularPenaltis($terceiroLugar);
                }
                //monta a classificação final do mata-mata
                if ($final) {
                    $vencedorFinal = null;
                    $perdedorFinal = null;
                    // verifica quem venceu no tempo normal
                    if ($final->gols_mandante > $final->gols_visitante) {
                        $vencedorFinal = $final->mandante;
                        $perdedorFinal = $final->visitante;
                    } elseif ($final->gols_mandante < $final->gols_visitante) {
                        $vencedorFinal = $final->visitante;
                        $perdedorFinal = $final->mandante;
                        //se empatou verifica os pênaltis
                    } elseif ($final->penaltisMandante > $final->penaltisVisitante) {
                        $vencedorFinal = $final->mandante;
                        $perdedorFinal = $final->visitante;
                    } elseif ($final->penaltisMandante < $final->penaltisVisitante) {
                        $vencedorFinal = $final->visitante;
                        $perdedorFinal = $final->mandante;
                    }
                    //adiciona o vencedor como 1º lugar
                    if ($vencedorFinal) {
                        $classificacaoMataMata->push(['posicao' => 1, 'equipe' => $vencedorFinal,]);
                    }
                    //adiciona o perdedor como 2º lugar
                    if ($perdedorFinal) {
                        $classificacaoMataMata->push(['posicao' => 2, 'equipe' => $perdedorFinal,]);
                    }
                    //verifica o terceiro lugar
                    if ($terceiroLugar) {
                        $terceiro = null;
                        //verifica quem venceu no tempo normal
                        if ($terceiroLugar->gols_mandante > $terceiroLugar->gols_visitante) {
                            $terceiro = $terceiroLugar->mandante;
                        } elseif ($terceiroLugar->gols_mandante < $terceiroLugar->gols_visitante) {
                            $terceiro = $terceiroLugar->visitante;
                            // Se empatou verifica os pênaltis
                        } elseif ($terceiroLugar->penaltisMandante > $terceiroLugar->penaltisVisitante) {
                            $terceiro = $terceiroLugar->mandante;
                        } elseif ($terceiroLugar->penaltisMandante < $terceiroLugar->penaltisVisitante) {
                            $terceiro = $terceiroLugar->visitante;
                        }
                        //adiciona o terceiro colocado
                        if ($terceiro) {
                            $classificacaoMataMata->push(['posicao' => 3, 'equipe' => $terceiro,]);
                        }
                    }
                }
            }
        }
        //envia todas as informações para a página
        return view('areaAdministrativa.campeonatos.show', compact('campeonato', 'classificacao', 'temTriangular', 'classificacaoMataMata', 'semifinais', 'final', 'terceiroLugar'));
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

        $totalInscricoes = $campeonato->inscricoes()->count(); // Conta quantas equipes já estão inscritas
        // Impede diminuir o limite abaixo do número de inscrições existentes
        if ($dados['maximo_equipes'] < $totalInscricoes) {
            return back()->withInput()->withErrors(['maximo_equipes' => "Não é possível definir menos de {$totalInscricoes} equipes, pois já existem {$totalInscricoes} inscrições neste campeonato"]);
        }

        if ($campeonato->status === 'EM_ANDAMENTO' && $dados['status'] === 'INSCRICOES') {
            $partidaComEvento = $campeonato->partidas()->whereHas('eventos')->exists();
            if ($partidaComEvento) {
                return back()->withInput()->withErrors(['status' => 'Não é possível voltar o campeonato para inscrições, pois já existem eventos cadastrados em uma ou mais partidas']);
            }
            // Se não existem eventos, pode apagar as partidas
            $campeonato->partidas()->delete();
        }
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
        $dados['user_id'] = auth()->id(); //pega o id de quem criou 
        $campeonato->update($dados); // edita
        return redirect()->route('campeonatos.index')->with('success', 'Campeonato atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campeonato $campeonato)
    {
        $possuiNoticia = $campeonato->noticias()->exists(); // verifica se existe notíca
        $possuiInscricoes = $campeonato->inscricoes()->exists();//verifica se existe incrição no campeonato
        if ($possuiInscricoes && $possuiNoticia) {
            return redirect()->route('campeonatos.index')->with('error', 'O campeonato possui inscrições e notícias relacionados e não pode ser excluído.');
        } elseif ($possuiInscricoes) {
            return redirect()->route('campeonatos.index')->with('error', 'O campeonato possui inscrições e não pode ser excluído.');
        } elseif ($possuiNoticia) {
            return redirect()->route('campeonatos.index')->with('error', 'O campeonato possui Notícias relacionadas e não pode ser excluído.');
        }
        $campeonato->delete();
        return redirect()->route('campeonatos.index')->with('success', 'Campeonato excluído com sucesso!');
    }

    public function gerarConfrontos(Campeonato $campeonato)
    {
        // Verifica se o campeonato está pronto para o sorteio
        if ($campeonato->status !== 'INSCRICOES') {
            return back()->with('error', 'Este campeonato não está disponível para sorteio.');
        }
        // Busca as equipes inscritas
        $campeonato->load('inscricoes.equipe');
        $equipes = $campeonato->inscricoes->pluck('equipe');
        // Verifica se atingiu o limite de equipes
        if ($equipes->count() != $campeonato->maximo_equipes) {
            return back()->with('error', 'O campeonato ainda não possui o número necessário de equipes.');
        }
        // Verifica o tipo do campeonato
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

        $campeonato->update(['status' => 'EM_ANDAMENTO']); // Coloca o campeonato em andamento
        return redirect()->route('campeonatos.index')->with('success', 'Partidas geradas com sucesso!');
    }

    private function gerarPontosCorridos($campeonato, $equipes)
    {

        $equipes = $equipes->values(); // Organiza os índices começando pelo 0
        // Se a quantidade for ímpar, adiciona uma folga
        if ($equipes->count() % 2 != 0) {
            $equipes->push(null);
        }
        $quantidadeEquipes = $equipes->count();// Quantidade de equipes
        $numeroRodadas = $quantidadeEquipes - 1;// Quantidade de rodadas em um turno
        $partidasPorRodada = $quantidadeEquipes / 2; // Quantidade de partidas por rodada
        $equipesOriginais = $equipes->values();// Guarda a configuração inicial
        //gera os turnos
        for ($turno = 1; $turno <= 2; $turno++) {
            $equipes = $equipesOriginais->values();// Começa novamente com as equipes na posição inicial
            // Percorre as rodadas
            for ($rodada = 1; $rodada <= $numeroRodadas; $rodada++) {
                // Percorre as partidas da rodada
                for ($jogo = 0; $jogo < $partidasPorRodada; $jogo++) {
                    $mandante = $equipes[$jogo]; // Pega o mandante
                    $visitante = $equipes[$quantidadeEquipes - 1 - $jogo]; // Pega o visitante
                    // Se tiver folga, não cria partida
                    if ($mandante === null || $visitante === null) {
                        continue;
                    }
                    $numeroRodada = $rodada;// Define qual rodada será
                    // No segundo turno, soma as rodadas do primeiro
                    if ($turno == 2) {
                        $numeroRodada += $numeroRodadas;
                    }
                    // No segundo turno inverte o mando
                    if ($turno == 2) {
                        $temp = $mandante;
                        $mandante = $visitante;
                        $visitante = $temp;
                    }
                    // Cria a partida
                    Partida::create([
                        'campeonato_id' => $campeonato->id,
                        'mandante_id' => $mandante->id,
                        'visitante_id' => $visitante->id,
                        'gols_mandante' => 0,
                        'gols_visitante' => 0,
                        'data_hora' => null,
                        'status' => 'PENDENTE',
                        'fase' => 'RODADA_' . $numeroRodada,
                        'local' => null,
                    ]);
                }
                // Rotaciona as equipes para a próxima rodada
                $primeira = $equipes->shift();
                $ultimo = $equipes->pop();
                $equipes->prepend($ultimo);
                $equipes->prepend($primeira);
            }
        }
    }

    private function gerarMataMata($campeonato, $equipes)
    {
        // Sorteia a ordem das equipes
        $equipes = $equipes->shuffle()->values();
        // Percorre as equipes de 2 em 2
        for ($i = 0; $i < $equipes->count() - 1; $i += 2) {

            Partida::create([
                'campeonato_id' => $campeonato->id,
                'mandante_id' => $equipes[$i]->id,
                'visitante_id' => $equipes[$i + 1]->id,
                'gols_mandante' => 0,
                'gols_visitante' => 0,
                'data_hora' => null,
                'status' => 'PENDENTE',
                'fase' => 'RODADA_INICIAL',
                'local' => null,
            ]);
        }
    }

    private function gerarGruposMataMata($campeonato, $equipes)
    {
        //
    }

    private function calcularClassificacaoPontosCorridos(Campeonato $campeonato, $fase = null)
    {
        $campeonato->load('inscricoes.equipe');
        $query = $campeonato->partidas()->where('status', 'FINALIZADA');// Busca somente partidas finalizadas

        // Se for uma fase específica filtra pela fase
        if ($fase) {
            $query->where('fase', $fase);
        }
        $partidas = $query->get();
        $equipesDaFase = [];// IDs das equipes que participaram da fase
        if ($fase) {
            foreach ($partidas as $partida) {
                $equipesDaFase[] = $partida->mandante_id;
                $equipesDaFase[] = $partida->visitante_id;
            }
            $equipesDaFase = array_unique($equipesDaFase);
        }
        $classificacao = [];  // Cria a classificação
        foreach ($campeonato->inscricoes as $inscricao) {
            $equipe = $inscricao->equipe;
            // Se for triangular só adiciona quem participou dele
            if ($fase && !in_array($equipe->id, $equipesDaFase)) {
                continue;
            }
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
        // Calcula os resultados
        foreach ($partidas as $partida) {
            $mandante = $partida->mandante_id;
            $visitante = $partida->visitante_id;
            if (!isset($classificacao[$mandante], $classificacao[$visitante])) {
                continue;
            }
            // Jogos
            $classificacao[$mandante]['jogos']++;
            $classificacao[$visitante]['jogos']++;
            // Gols
            $classificacao[$mandante]['gols_pro'] += $partida->gols_mandante;
            $classificacao[$mandante]['gols_contra'] += $partida->gols_visitante;
            $classificacao[$visitante]['gols_pro'] += $partida->gols_visitante;
            $classificacao[$visitante]['gols_contra'] += $partida->gols_mandante;
            // Resultado
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
        // Saldo de gols
        foreach ($classificacao as &$equipe) {
            $equipe['saldo'] = $equipe['gols_pro'] - $equipe['gols_contra'];
        }
        // Ordena
        usort($classificacao, function ($a, $b) {
            if ($a['pontos'] != $b['pontos']) {
                return $b['pontos'] - $a['pontos'];
            }
            if ($a['saldo'] != $b['saldo']) {
                return $b['saldo'] - $a['saldo'];
            }
            return $b['gols_pro'] - $a['gols_pro'];
        });
        // Posição
        foreach ($classificacao as $posicao => &$equipe) {
            $equipe['posicao'] = $posicao + 1;
        }
        return $classificacao;
    }

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


}
