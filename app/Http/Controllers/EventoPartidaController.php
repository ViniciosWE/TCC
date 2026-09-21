<?php

namespace App\Http\Controllers;

use App\Models\EventoPartida;
use App\Models\Participante;
use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventoPartidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //verifica se foi informado o ID de uma partida na requisição
        if ($request->filled('partida_id')) {
            $partida = Partida::findOrFail($request->partida_id); //busca a partida pelo ID informado
            $eventoPartidas = EventoPartida::where('partida_id', $partida->id)->with(['participante', 'partida'])->orderBy('tempo')->orderBy('id')->get();//busca todos os eventos dessa partida
        } else {
            $partida = null;
            $eventoPartidas = EventoPartida::with(['participante', 'partida'])->latest()->get();//caso nenhuma partida informad busca todos os eventos do mais recente para o mais antigo
        }
        return view('areaAdministrativa.eventoPartidas.index', compact('eventoPartidas', 'partida'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $partida = Partida::with(['mandante', 'visitante'])->findOrFail($request->partida_id);
        $titularesMandante = $this->contarTitulares($partida, $partida->mandante_id);
        $titularesVisitante = $this->contarTitulares($partida, $partida->visitante_id);
        $titularesCompletos = $titularesMandante === 5 && $titularesVisitante === 5;
        $participantes = Participante::with([
            'contratos' => function ($query) {
                $query->where('status', 'ATIVO');
            }
        ])->where('participantes.status', 'ATIVO')->whereHas('contratos', function ($query) use ($partida) {
            $query->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->where('status', 'ATIVO');
        })->when(!$titularesCompletos, function ($query) use ($partida) {
            $query->whereNotIn('funcao', ['TECNICO', 'AUXILIAR_TECNICO', 'PREPARADOR_FISICO',]);
            $titulares = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'TITULAR')->pluck('participante_id');
            $query->whereNotIn('participantes.id', $titulares);
        })->orderBy('nome')->get();
        foreach ($participantes as $participante) {
            $contrato = $participante->contratos->first();
            $participante->equipe_nome = $contrato->equipe_id == $partida->mandante_id ? $partida->mandante->nome : $partida->visitante->nome;
        }
        $participantes = $participantes->sortBy([['equipe_nome', 'asc'], ['nome', 'asc'],])->values();
        $penaltiDesempateDisponivel = $this->podeCadastrarPenaltiDesempate($partida);
        $disputaPenaltisIniciada = $this->existePenaltiDesempate($partida);
        return view('areaAdministrativa.eventoPartidas.create', compact('participantes', 'partida', 'titularesMandante', 'titularesVisitante', 'titularesCompletos', 'penaltiDesempateDisponivel', 'disputaPenaltisIniciada'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //valida os dados enviados pelo formulário
        $request->validate([
            'partida_id' => 'required|exists:partidas,id',
            'participante_id' => ['required', 'exists:participantes,id'],
            'tempo' => ['required', 'date_format:H:i:s'],
            'tipo' => ['required', 'in:TITULAR,ENTRADA_GOLEIRO,GOL,CARTAO_AMARELO,CARTAO_VERMELHO,ASSISTENCIA,GOL_CONTRA,PENALTI_CONVERTIDO_DESEMPATE'],
        ]);
        $partida = Partida::findOrFail($request->partida_id);//busca a partida pelo ID enviado
        //verifica as regras da disputa de pênaltis
        if ($request->tipo === 'PENALTI_CONVERTIDO_DESEMPATE') {
            if (!$this->permiteDesempate($partida)) {
                return back()->withInput()->withErrors(['tipo' => 'Esta partida permite empate e não possui disputa de pênaltis.']);
            }
            //só pode iniciar a disputa quando o placar normal estiver empatado
            if (!$this->existePenaltiDesempate($partida) && $partida->gols_mandante != $partida->gols_visitante) {
                return back()->withInput()->withErrors(['tipo' => 'A disputa de pênaltis só pode começar quando a partida estiver empatada.']);
            }
        }
        //depois que a disputa de pênaltis começou, não podem mais ser cadastrados gols, gols contra ou assistências
        if ($this->existePenaltiDesempate($partida) && in_array($request->tipo, ['GOL', 'GOL_CONTRA', 'ASSISTENCIA'])) {
            return back()->withInput()->withErrors(['tipo' => 'Após o início da disputa de pênaltis, não é mais possível cadastrar gols, gols contra ou assistências.']);
        }
        //busca o participante ele precisa estar ativo e possuir contrato ativo com uma das equipes da partida
        $participante = Participante::where('id', $request->participante_id)->where('status', 'ATIVO')->whereHas('contratos', function ($query) use ($partida) {
            $query->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->where('status', 'ATIVO');
        })->first();
        if (!$participante) {
            return back()->withInput()->withErrors(['participante_id' => 'O participante não pertence a uma das equipes desta partida.',]);
        }
        //busca o contrato ativo do participante com uma das equipes da partida
        $contrato = $participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
        if (!$contrato) {
            return back()->withInput()->withErrors(['participante_id' => 'Não foi encontrado um contrato ativo para este participante nesta partida.',]);
        }
        //a comissão técnica só pode receber cartões
        if (in_array($participante->funcao, ['TECNICO', 'AUXILIAR_TECNICO', 'PREPARADOR_FISICO',]) && !in_array($request->tipo, ['CARTAO_AMARELO', 'CARTAO_VERMELHO',])) {
            return back()->withInput()->withErrors(['tipo' => 'A comissão técnica só pode receber cartão amarelo ou cartão vermelho.',]);
        }
        //conta novamente os titulares das duas equipes
        $titularesMandante = $this->contarTitulares($partida, $partida->mandante_id);
        $titularesVisitante = $this->contarTitulares($partida, $partida->visitante_id);
        $titularesCompletos = $titularesMandante === 5 && $titularesVisitante === 5;//verifica se as duas equipes já possuem 5 titulares
        if (!$titularesCompletos && $request->tipo !== 'TITULAR') {
            return back()->withInput()->withErrors(['tipo' => 'Cadastre os 5 titulares de cada equipe antes de cadastrar outros eventos.',]);
        }
        if ($titularesCompletos && $request->tipo === 'TITULAR') {
            return back()->withInput()->withErrors(['tipo' => 'Os 5 titulares de cada equipe já foram cadastrados.',]);
        }
        if ($request->tipo === 'TITULAR' && in_array($participante->funcao, ['TECNICO', 'AUXILIAR_TECNICO', 'PREPARADOR_FISICO',])) {
            return back()->withInput()->withErrors(['participante_id' => 'Somente jogadores podem ser titulares.',]);
        }
        if ($request->tipo === 'TITULAR') {
            //somente jogadores podem ser titulares
            if (in_array($participante->funcao, ['TECNICO', 'AUXILIAR_TECNICO', 'PREPARADOR_FISICO',])) {
                return back()->withInput()->withErrors(['participante_id' => 'Somente jogadores podem ser titulares.',]);
            }
            //não permite dois goleiros titulares na mesma equipe
            if (in_array($participante->funcao, ['GOLEIRO', 'GOLEIRO_LINHA'])) {
                $jaExisteGoleiro = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'TITULAR')->whereHas('participante', function ($query) {
                    $query->whereIn('funcao', ['GOLEIRO', 'GOLEIRO_LINHA']);
                })->whereHas('participante.contratos', function ($query) use ($contrato) {
                    $query->where('equipe_id', $contrato->equipe_id)->where('status', 'ATIVO');
                })->exists();
                if ($jaExisteGoleiro) {
                    return back()->withInput()->withErrors(['participante_id' => 'Esta equipe já possui um goleiro titular.',]);
                }
            }
            //procura se o jogador já foi cadastrado como titular nessa partida
            $jaTitular = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'TITULAR')->where('participante_id', $participante->id)->exists();
            if ($jaTitular) {
                return back()->withInput()->withErrors(['participante_id' => 'Este jogador já foi definido como titular nesta partida.',]);
            }
            //verifica limite de 5 titulares por equipe
            if ($contrato->equipe_id == $partida->mandante_id) {
                if ($titularesMandante >= 5) {
                    return back()->withInput()->withErrors(['participante_id' => 'O mandante já possui 5 titulares cadastrados.',]);
                }

            } else {
                if ($titularesVisitante >= 5) {
                    return back()->withInput()->withErrors(['participante_id' => 'O visitante já possui 5 titulares cadastrados.',]);
                }
            }
        }
        if ($request->tipo === 'ENTRADA_GOLEIRO') {
            if (!in_array($participante->funcao, ['GOLEIRO', 'GOLEIRO_LINHA'])) {
                return back()->withInput()->withErrors(['participante_id' => 'Este evento só pode ser registrado para goleiros.',]);
            }
            $equipeId = $contrato->equipe_id;//guarda o ID da equipe do participante
            $goleiroAtual = $this->buscarGoleiroAtual($partida, $equipeId, $request->tempo);// Procura qual goleiro está atualmente em quadra naquele momento da partida
            if ($goleiroAtual && $goleiroAtual->id == $participante->id) {
                return back()->withInput()->withErrors(['participante_id' => 'Este goleiro já está em quadra neste momento.',]);
            }
            if (!$goleiroAtual) {
                return back()->withInput()->withErrors(['participante_id' => 'Não foi possível identificar o goleiro que está em quadra para realizar a substituição.',]);
            }
        }
        if ($request->tipo === 'ASSISTENCIA') {
            // Procura um gol ou gol contra no mesmo tempo informado
            $existeGol = EventoPartida::where('partida_id', $partida->id)->where('tempo', $request->tempo)->whereIn('tipo', ['GOL', 'GOL_CONTRA'])->exists();
            // A assistência só pode existir se houver um gol no mesmo tempo
            if (!$existeGol) {
                return back()->withInput()->withErrors(['tempo' => 'A assistência precisa estar no mesmo tempo de um gol.',]);
            }
        }
        EventoPartida::create([
            'user_id' => auth()->id(),
            'participante_id' => $participante->id,
            'partida_id' => $partida->id,
            'tipo' => $request->tipo,
            'tempo' => $request->tipo === 'TITULAR' ? '00:00:00' : $request->tempo,
        ]);
        $this->recalcularTudo($partida);
        return redirect()->route('eventoPartidas.create', ['partida_id' => $partida->id, 'campeonato_id' => $request->campeonato_id, 'campeonato_nome' => $request->campeonato_nome,])->with('success', 'Evento cadastrado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EventoPartida $eventoPartida)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventoPartida $eventoPartida)
    {
        //eventos gerados automaticamente não podem ser editados diretamente.
        if (in_array($eventoPartida->tipo, ['SAIDA_GOLEIRO', 'GOLS_SOFRIDOS'])) {
            return back()->with('error', 'Este evento é gerado automaticamente e não pode ser editado diretamente.');
        }
        $partida = $eventoPartida->partida;//busca a partida relacionada ao evento
        //conta os titulares de cada equipe
        $titularesMandante = $this->contarTitulares($partida, $partida->mandante_id);
        $titularesVisitante = $this->contarTitulares($partida, $partida->visitante_id);
        $titularesCompletos = $titularesMandante === 5 && $titularesVisitante === 5; //verifica se as duas equipes já possuem 5 titulares
        $participantes = Participante::with([
            'contratos' => function ($query) {
                $query->where('status', 'ATIVO');
            }
        ])->where('participantes.status', 'ATIVO')->whereHas('contratos', function ($query) use ($partida) {
            $query->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->where('status', 'ATIVO');
        })->when(!$titularesCompletos, function ($query) {
            $query->whereNotIn('funcao', ['TECNICO', 'AUXILIAR_TECNICO', 'PREPARADOR_FISICO',]);
        })->get();
        //identifica a equipe de cada participante
        foreach ($participantes as $participante) {
            $contrato = $participante->contratos->first();
            $participante->equipe_nome = $contrato->equipe_id == $partida->mandante_id ? $partida->mandante->nome : $partida->visitante->nome;
        }
        //ordena primeiro pela equipe e depois pelo nome
        $participantes = $participantes->sortBy([['equipe_nome', 'asc'], ['nome', 'asc'],])->values();
        return view('areaAdministrativa.eventoPartidas.edit', compact('participantes', 'eventoPartida', 'titularesCompletos', 'partida'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventoPartida $eventoPartida)
    {
        //impede a edição direta de eventos gerados automaticamente
        if (in_array($eventoPartida->tipo, ['SAIDA_GOLEIRO', 'GOLS_SOFRIDOS'])) {
            return back()->with('error', 'Este evento é gerado automaticamente e não pode ser alterado diretamente.');
        }
        //valida os dados enviados pelo formulário
        $dados = $request->validate([
            'participante_id' => ['required', 'exists:participantes,id'],
            'tipo' => ['required', 'in:TITULAR,ENTRADA_GOLEIRO,GOL,CARTAO_AMARELO,CARTAO_VERMELHO,ASSISTENCIA,GOL_CONTRA,PENALTI_CONVERTIDO_DESEMPATE'],
            'tempo' => ['required', 'date_format:H:i:s'],
        ]);
        $partida = $eventoPartida->partida;//busca a partida relacionada ao evento
        //verifica as regras da disputa de pênaltis
        if ($dados['tipo'] === 'PENALTI_CONVERTIDO_DESEMPATE') {
            if (!$this->permiteDesempate($partida)) {
                return back()->withInput()->withErrors(['tipo' => 'Esta partida permite empate e não possui disputa de pênaltis.']);
            }
            //se ainda não havia disputa, ela só pode começar com o placar empatado
            if ($eventoPartida->tipo !== 'PENALTI_CONVERTIDO_DESEMPATE' &&!$this->existePenaltiDesempate($partida) &&$partida->gols_mandante != $partida->gols_visitante) {
                return back()->withInput()->withErrors(['tipo' => 'A disputa de pênaltis só pode começar quando a partida estiver empatada.']);
            }
        }
        //depois que a disputa começou, não permite transformar um evento em gol, gol contra ou assistência.
        if ($this->existePenaltiDesempate($partida) &&$eventoPartida->tipo !== 'PENALTI_CONVERTIDO_DESEMPATE' &&in_array($dados['tipo'], ['GOL', 'GOL_CONTRA', 'ASSISTENCIA'])) {
            return back()->withInput()->withErrors(['tipo' => 'Após o início da disputa de pênaltis, não é mais possível cadastrar gols, gols contra ou assistências.']);
        }
        if (in_array($eventoPartida->tipo, ['GOL', 'GOL_CONTRA']) && !in_array($dados['tipo'], ['GOL', 'GOL_CONTRA'])) {
            return back()->withInput()->withErrors(['tipo' => 'Um evento de gol só pode continuar sendo um evento de gol.',]);
        }
        if ($eventoPartida->tipo === 'ASSISTENCIA' && $dados['tipo'] !== 'ASSISTENCIA') {
            return back()->withInput()->withErrors(['tipo' => 'Uma assistência deve continuar sendo uma assistência.',]);
        }
        if ($eventoPartida->tipo === 'ENTRADA_GOLEIRO' && $dados['tipo'] !== 'ENTRADA_GOLEIRO') {
            return back()->withInput()->withErrors(['tipo' => 'Uma entrada de goleiro deve continuar sendo uma entrada de goleiro.',]);
        }
        //busca o participante ativo que pertence a uma das equipes da partida
        $participante = Participante::where('id', $dados['participante_id'])->where('status', 'ATIVO')->whereHas('contratos', function ($query) use ($partida) {
            $query->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->where('status', 'ATIVO');
        })->first();
        if (!$participante) {
            return back()->withInput()->withErrors(['participante_id' => 'O participante não pertence a uma das equipes desta partida.',]);
        }
        //busca o contrato ativo do participante
        $contrato = $participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();

        if (!$contrato) {
            return back()->withInput()->withErrors(['participante_id' => 'Não foi encontrado um contrato ativo para este participante nesta partida.',]);
        }
        //a comissão técnica só pode receber cartões
        if (in_array($participante->funcao, ['TECNICO', 'AUXILIAR_TECNICO', 'PREPARADOR_FISICO',]) && !in_array($request->tipo, ['CARTAO_AMARELO', 'CARTAO_VERMELHO',])) {
            return back()->withInput()->withErrors(['tipo' => 'A comissão técnica só pode receber cartão amarelo ou cartão vermelho.',]);
        }
        //verifica se o participante pode ser titular
        if ($dados['tipo'] === 'TITULAR') {
            if (in_array($participante->funcao, ['TECNICO', 'AUXILIAR_TECNICO', 'PREPARADOR_FISICO',])) {
                return back()->withInput()->withErrors(['participante_id' => 'Somente jogadores podem ser titulares.',]);
            }
            // Não permite dois goleiros titulares na mesma equipe
            if (in_array($participante->funcao, ['GOLEIRO', 'GOLEIRO_LINHA'])) {
                $jaExisteGoleiro = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'TITULAR')->where('id', '!=', $eventoPartida->id)->whereHas('participante', function ($query) {
                    $query->whereIn('funcao', ['GOLEIRO', 'GOLEIRO_LINHA']);
                })->whereHas('participante.contratos', function ($query) use ($contrato) {
                    $query->where('equipe_id', $contrato->equipe_id)->where('status', 'ATIVO');
                })->exists();
                if ($jaExisteGoleiro) {
                    return back()->withInput()->withErrors(['participante_id' => 'Esta equipe já possui um goleiro titular.',]);
                }
            }
            //busca os outros titulares, ignorando o evento que está sendo editado
            $titulares = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'TITULAR')->where('id', '!=', $eventoPartida->id)->get();
            //verifica se o jogador já é titular
            $jaTitular = $titulares->where('participante_id', $participante->id)->isNotEmpty();
            if ($jaTitular) {
                return back()->withInput()->withErrors(['participante_id' => 'Este jogador já foi definido como titular nesta partida.',]);
            }
            //conta os titulares de cada equipe
            $titularesMandante = $this->contarTitulares($partida, $partida->mandante_id, $eventoPartida->id);
            $titularesVisitante = $this->contarTitulares($partida, $partida->visitante_id, $eventoPartida->id);
            //verifica o limite de 5 titulares por equipe
            if ($contrato->equipe_id == $partida->mandante_id && $titularesMandante >= 5) {
                return back()->withInput()->withErrors(['participante_id' => 'O mandante já possui 5 titulares.',]);
            }
            if ($contrato->equipe_id == $partida->visitante_id && $titularesVisitante >= 5) {
                return back()->withInput()->withErrors(['participante_id' => 'O visitante já possui 5 titulares.',]);
            }
        }
        //verifica a entrada do goleiro
        if ($dados['tipo'] === 'ENTRADA_GOLEIRO') {
            //somente goleiros podem receber esse tipo de evento
            if (!in_array($participante->funcao, ['GOLEIRO', 'GOLEIRO_LINHA'])) {
                return back()->withInput()->withErrors(['participante_id' => 'Este evento só pode ser registrado para goleiros.',]);
            }
            //busca o goleiro que estava em quadra antes da alteração
            $goleiroAtual = $this->buscarGoleiroAtual($partida, $contrato->equipe_id, $dados['tempo'], $eventoPartida->id);
            //impede colocar o mesmo goleiro que já está em quadra
            if ($goleiroAtual && $goleiroAtual->id == $participante->id) {
                return back()->withInput()->withErrors(['participante_id' => 'Este goleiro já está em quadra neste momento.',]);
            }
            //verifica se foi possível encontrar o goleiro atual
            if (!$goleiroAtual) {
                return back()->withInput()->withErrors(['participante_id' => 'Não foi possível identificar o goleiro atual para realizar a substituição.',]);
            }
        }
        //verifica a assistência
        if ($dados['tipo'] === 'ASSISTENCIA') {
            //procura um gol ou gol contra no mesmo tempo
            $existeGol = EventoPartida::where('partida_id', $partida->id)->where('tempo', $dados['tempo'])->whereIn('tipo', ['GOL', 'GOL_CONTRA'])->exists();
            if (!$existeGol) {
                return back()->withInput()->withErrors(['tempo' => 'A assistência precisa estar no mesmo tempo de um gol.',]);
            }
        }
        //Edição do gol
        if (in_array($eventoPartida->tipo, ['GOL', 'GOL_CONTRA'])) {
            //verifica se alguma informação do gol foi alterada
            if ($eventoPartida->tempo !== $dados['tempo'] || $eventoPartida->participante_id != $participante->id || $eventoPartida->tipo !== $dados['tipo']) {
                //guarda a assistência antes de excluir o grupo antigo
                $assistencia = EventoPartida::where('partida_id', $partida->id)->where('tempo', $eventoPartida->tempo)->where('tipo', 'ASSISTENCIA')->first();
                $this->excluirGrupoGol($partida, $eventoPartida->tempo);//remove o gol, assistência e gols sofridos antigos
                //cria o gol com os novos dados
                EventoPartida::create([
                    'user_id' => $eventoPartida->user_id,
                    'participante_id' => $participante->id,
                    'partida_id' => $partida->id,
                    'tipo' => $dados['tipo'],
                    'tempo' => $dados['tempo'],
                ]);
                //se existia assistência mantém ela no novo tempo
                if ($assistencia) {
                    EventoPartida::create([
                        'user_id' => $assistencia->user_id,
                        'participante_id' => $assistencia->participante_id,
                        'partida_id' => $partida->id,
                        'tipo' => 'ASSISTENCIA',
                        'tempo' => $dados['tempo'],
                    ]);
                }
            } else {
                //atualiza o gol normalmente
                $eventoPartida->update(['participante_id' => $participante->id, 'tipo' => $dados['tipo'], 'tempo' => $dados['tempo'],]);
            }
            $this->recalcularTudo($partida);//recalcula eventos automáticos e placar
        } elseif ($eventoPartida->tipo === 'ASSISTENCIA') {
            //atualiza a assistência
            $eventoPartida->update(['participante_id' => $participante->id, 'tempo' => $dados['tempo'],]);
            $this->recalcularTudo($partida); //recalcula eventos automáticos e placar
        } elseif ($eventoPartida->tipo === 'ENTRADA_GOLEIRO') {
            //atualiza o goleiro e o tempo da entrada
            $eventoPartida->update(['participante_id' => $participante->id, 'tempo' => $dados['tempo'],]);
            $this->recalcularTudo($partida);//recria as saídas dos goleiros e recalcula os gols sofridos
        } else {
            //atualiza o evento normalmente
            $eventoPartida->update([
                'participante_id' => $participante->id,
                'tipo' => $dados['tipo'],
                'tempo' => $dados['tipo'] === 'TITULAR' ? '00:00:00' : $dados['tempo'],
            ]);
            $this->recalcularTudo($partida);//Recalcula os eventos automáticos e o placar
        }
        return redirect()->route('eventoPartidas.index')->with('success', 'Evento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventoPartida $eventoPartida)
    {
        $partida = $eventoPartida->partida;
        if (in_array($eventoPartida->tipo, ['GOL', 'GOL_CONTRA', 'ASSISTENCIA', 'GOLS_SOFRIDOS'])) {
            EventoPartida::where('partida_id', $partida->id)->where('tempo', $eventoPartida->tempo)->whereIn('tipo', ['GOL', 'GOL_CONTRA', 'ASSISTENCIA', 'GOLS_SOFRIDOS'])->delete();
        } elseif (in_array($eventoPartida->tipo, ['ENTRADA_GOLEIRO', 'SAIDA_GOLEIRO'])) {
            $contrato = $eventoPartida->participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
            if ($contrato) {
                //exclui a entrada e a saída do goleiro somente daquela equipe e daquele momento
                EventoPartida::where('partida_id', $partida->id)->where('tempo', $eventoPartida->tempo)->whereIn('tipo', ['ENTRADA_GOLEIRO', 'SAIDA_GOLEIRO'])->whereHas('participante.contratos', function ($query) use ($contrato) {
                    $query->where('equipe_id', $contrato->equipe_id)->where('status', 'ATIVO');
                })->delete();
            }
        } else {
            $eventoPartida->delete();
        }
        $this->recalcularTudo($partida);//depois da exclusão, recalcula os eventos automáticos e o placar
        return redirect()->route('eventoPartidas.index')->with('success', 'Evento excluído com sucesso.');
    }
    private function contarTitulares(Partida $partida, $equipeId, $ignorarEventoId = null)
    {
        $query = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'TITULAR')->whereHas('participante.contratos', function ($query) use ($equipeId) {
            $query->where('equipe_id', $equipeId)->where('status', 'ATIVO');
        });
        if ($ignorarEventoId) {
            $query->where('id', '!=', $ignorarEventoId);
        }
        return $query->count();//retorna a quantidade de titulares encontrados
    }
    private function buscarGoleiroAtual(Partida $partida, $equipeId, $tempo, $ignorarEventoId = null)
    {
        $eventos = EventoPartida::where('partida_id', $partida->id)->whereIn('tipo', ['TITULAR', 'ENTRADA_GOLEIRO'])->where('tempo', '<=', $tempo)->whereHas('participante.contratos', function ($query) use ($equipeId) {
            $query->where('equipe_id', $equipeId)->where('status', 'ATIVO');
        })->with('participante')->orderBy('tempo')->orderBy('id')->get();
        $goleiroAtual = null;
        //percorre os eventos encontrados em ordem
        foreach ($eventos as $evento) {
            if (!in_array($evento->participante->funcao, ['GOLEIRO', 'GOLEIRO_LINHA'])) {
                continue;
            }
            if ($ignorarEventoId && $evento->id == $ignorarEventoId) {
                continue;
            }
            $goleiroAtual = $evento->participante;
        }
        return $goleiroAtual;//retorna o goleiro que estava em quadra naquele momento
    }
    private function reconstruirSaidasGoleiro(Partida $partida)
    {
        EventoPartida::where('partida_id', $partida->id)->where('tipo', 'SAIDA_GOLEIRO')->delete();
        //percorre as duas equipes da partida
        foreach ([$partida->mandante_id, $partida->visitante_id] as $equipeId) {
            //busca os titulares e entradas de goleiro da equipe
            $eventos = EventoPartida::where('partida_id', $partida->id)->whereIn('tipo', ['TITULAR', 'ENTRADA_GOLEIRO'])->whereHas('participante.contratos', function ($query) use ($equipeId) {
                $query->where('equipe_id', $equipeId)->where('status', 'ATIVO');
            })->with('participante')->orderBy('tempo')->orderBy('id')->get();
            $goleiroAtual = null;
            //percorre os eventos da equipe
            foreach ($eventos as $evento) {
                if (!in_array($evento->participante->funcao, ['GOLEIRO', 'GOLEIRO_LINHA'])) {
                    continue;
                }
                if ($evento->tipo === 'TITULAR') {
                    $goleiroAtual = $evento->participante;
                    continue;
                }
                //quando encontra uma entrada de goleiro cria a saída do goleiro que estava em quadra
                if ($evento->tipo === 'ENTRADA_GOLEIRO') {
                    //verifica se realmente havia outro goleiro em quadra
                    if ($goleiroAtual && $goleiroAtual->id != $evento->participante->id) {
                        $contrato = $evento->participante->contratos()->where('status', 'ATIVO')->where('equipe_id', $equipeId)->first();
                        if ($contrato) {
                            EventoPartida::create([
                                'user_id' => $evento->user_id,
                                'participante_id' => $goleiroAtual->id,
                                'partida_id' => $partida->id,
                                'tipo' => 'SAIDA_GOLEIRO',
                                'tempo' => $evento->tempo,
                            ]);
                        }
                    }
                    $goleiroAtual = $evento->participante;//o goleiro que entrou passa a ser o goleiro atual
                }
            }
        }
    }
    private function recalcularGolsSofridos(Partida $partida)
    {
        EventoPartida::where('partida_id', $partida->id)->where('tipo', 'GOLS_SOFRIDOS')->delete();
        $gols = EventoPartida::where('partida_id', $partida->id)->whereIn('tipo', ['GOL', 'GOL_CONTRA'])->with('participante')->orderBy('tempo')->orderBy('id')->get();
        //percorre cada gol encontrado
        foreach ($gols as $gol) {
            $contrato = $gol->participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
            if (!$contrato) {
                continue;
            }
            //descobre qual equipe sofreu o gol
            if ($gol->tipo === 'GOL') {
                if ($contrato->equipe_id == $partida->mandante_id) {
                    $equipeQueSofreu = $partida->visitante_id;
                } else {
                    $equipeQueSofreu = $partida->mandante_id;
                }
            } else {
                $equipeQueSofreu = $contrato->equipe_id;
            }
            $goleiro = $this->buscarGoleiroAtual($partida, $equipeQueSofreu, $gol->tempo); //busca qual goleiro estava em quadra no momento do gol
            if (!$goleiro) {
                continue;
            }
            //cria automaticamente o evento de gol sofrido para o goleiro
            EventoPartida::create([
                'user_id' => $gol->user_id,
                'participante_id' => $goleiro->id,
                'partida_id' => $partida->id,
                'tipo' => 'GOLS_SOFRIDOS',
                'tempo' => $gol->tempo,
            ]);
        }
    }
    private function recalcularPlacar(Partida $partida)
    {
        $golsMandante = 0;
        $golsVisitante = 0;
        $gols = EventoPartida::where('partida_id', $partida->id)->whereIn('tipo', ['GOL', 'GOL_CONTRA'])->with('participante')->get();//busca todos os gols e gols contra da partida
        //percorre todos os gols
        foreach ($gols as $gol) {
            $contrato = $gol->participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
            if (!$contrato) {
                continue;
            }
            //gol normal aumenta o placar da equipe do jogador
            if ($gol->tipo === 'GOL') {
                if ($contrato->equipe_id == $partida->mandante_id) {
                    $golsMandante++;
                } else {
                    $golsVisitante++;
                }
            } else {
                //gol contra aumenta o placar da equipe adversária
                if ($contrato->equipe_id == $partida->mandante_id) {
                    $golsVisitante++;
                } else {
                    $golsMandante++;
                }
            }
        }
        $partida->update(['gols_mandante' => $golsMandante, 'gols_visitante' => $golsVisitante,]);//atualiza o placar salvo na partida
    }
    private function excluirGrupoGol(Partida $partida, $tempo)
    {
        //exclui os eventos de gol, gol contra, assistênciae gols sofridos que possuem o mesmo tempo na partida
        EventoPartida::where('partida_id', $partida->id)->where('tempo', $tempo)->whereIn('tipo', ['GOL', 'GOL_CONTRA', 'ASSISTENCIA', 'GOLS_SOFRIDOS'])->delete();
    }
    private function recalcularTudo(Partida $partida)
    {
        $this->reconstruirSaidasGoleiro($partida);//recria as saídas dos goleiros com base nas entradas
        $this->recalcularGolsSofridos($partida);//recalcula qual goleiro sofreu cada gol
        $this->recalcularPlacar($partida);//recalcula o placar da partida
    }

    private function permiteDesempate(Partida $partida)
    {
        $campeonato = $partida->campeonato;

        // Estas fases permitem empate, portanto não possuem disputa de pênaltis
        if (
            $campeonato->tipo === 'PONTOS_CORRIDOS' ||
            $partida->fase === 'TRIANGULAR' ||
            str_contains($partida->fase, '_GRUPO_')
        ) {
            return false;
        }

        // Demais fases são mata-mata e precisam de vencedor
        return true;
    }

    private function existePenaltiDesempate(Partida $partida)
    {
        return EventoPartida::where('partida_id', $partida->id)
            ->where('tipo', 'PENALTI_CONVERTIDO_DESEMPATE')
            ->exists();
    }

    private function podeCadastrarPenaltiDesempate(Partida $partida)
    {
        // A partida precisa ser de uma fase que não permite empate
        if (!$this->permiteDesempate($partida)) {
            return false;
        }

        // Para iniciar a disputa, o placar normal precisa estar empatado
        if ($partida->gols_mandante != $partida->gols_visitante) {
            return false;
        }

        // Se já começou a disputa de pênaltis, continua podendo cadastrar
        return true;
    }
}