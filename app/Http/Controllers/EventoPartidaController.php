<?php

namespace App\Http\Controllers;

use App\Models\EventoPartida;
use App\Models\Participante;
use App\Models\Partida;
use Illuminate\Http\Request;

class EventoPartidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Verifica se foi informada uma partida específica
        if ($request->filled('partida_id')) {
            $partida = Partida::findOrFail($request->partida_id);// Busca a partida informada
            // Garante que somente partidas finalizadas possam ter seus eventos visualizados
            if ($partida->status !== 'FINALIZADA') {
                return redirect()->route('partidas.index', ['campeonato_id' => $request->campeonato_id, 'campeonato_nome' => $request->campeonato_nome])->with('error', 'A partida ainda não foi finalizada.');
            }
            // Busca somente os eventos da partida selecionada
            $eventoPartidas = EventoPartida::where('partida_id', $partida->id)->with(['participante', 'partida'])->orderBy('tempo')->get();
        } else {
            // então busca os eventos de todas as partidas finalizadas
            $partida = null;
            $eventoPartidas = EventoPartida::whereHas('partida', function ($query) {
                $query->where('status', 'FINALIZADA');
            })->with(['participante', 'partida'])->latest()->get();
        }
        return view('areaAdministrativa.eventoPartidas.index', compact('eventoPartidas', 'partida'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //busca a partida pelo id recebido pela url 
        $partida = Partida::with(['mandante', 'visitante'])->findOrFail($request->partida_id);
        //busca somente os participantes das equpes da partida seleciona com contrato e status ativo
        $participantes = Participante::where('participantes.status', 'ATIVO')->whereHas('contratos', function ($query) use ($partida) {
            $query->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->where('status', 'ATIVO');
        })->orderBy('nome')->get();
        return view('areaAdministrativa.eventoPartidas.create', compact('participantes', 'partida'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'partida_id' => 'required|exists:partidas,id',
            'participante_id' => 'required|exists:participantes,id',
            'tempo' => 'required|date_format:H:i:s',
            'tipo' => 'required|in:GOL,CARTAO_AMARELO,CARTAO_VERMELHO,ASSISTENCIA,GOL_CONTRA,GOLS_SOFRIDOS',
        ]);
        $partida = Partida::findOrFail($request->partida_id);
        // Verifica se o participante pertence a uma das equipes da partida
        $participante = Participante::where('id', $request->participante_id)->where('status', 'ATIVO')->whereHas('contratos', function ($query) use ($partida) {
            $query->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->where('status', 'ATIVO');
        })->first();

        if (!$participante) {
            return back()->withInput()->withErrors(['participante_id' => 'O participante não pertence a uma das equipes desta partida.']);
        }
        // descobrir qual equipe o participante pertence
        $contrato = $participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
        // cadastro do evento
        EventoPartida::create([
            'user_id' => auth()->id(),
            'participante_id' => $participante->id,
            'partida_id' => $partida->id,
            'tipo' => $request->tipo,
            'tempo' => $request->tempo,
        ]);
        // Quando acontece gol soma um na equipe do jogador que marcou
        if ($request->tipo === 'GOL') {
            if ($contrato->equipe_id == $partida->mandante_id) {
                $partida->gols_mandante++;
            } else {
                $partida->gols_visitante++;
            }
        }
        // quando aconte gol contra soma um na equipe adversária
        if ($request->tipo === 'GOL_CONTRA') {
            if ($contrato->equipe_id == $partida->mandante_id) {
                $partida->gols_visitante++;
            } else {
                $partida->gols_mandante++;
            }
        }
        //salva o placar
        $partida->save();
        //manda para tela de partidas com o nome e o id do campeonato selecionado, para que não precise fazer a pesquisa novamente 
        return redirect()->route('partidas.index', ['campeonato_id' => $request->campeonato_id, 'campeonato_nome' => $request->campeonato_nome])->with('success', 'Evento cadastrado com sucesso.');
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
        $partida = $eventoPartida->partida;
        $participantes = Participante::where('participantes.status', 'ATIVO')->whereHas('contratos', function ($query) use ($partida) {
            $query->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->where('status', 'ATIVO');
        })->orderBy('nome')->get();
        return view('areaAdministrativa.eventoPartidas.edit', compact('participantes', 'eventoPartida'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventoPartida $eventoPartida)
    {
        $dados = $request->validate([
            'participante_id' => 'required|exists:participantes,id',
            'tipo' => 'required|in:GOL,CARTAO_AMARELO,CARTAO_VERMELHO,ASSISTENCIA,GOL_CONTRA,GOLS_SOFRIDOS',
            'tempo' => 'required|date_format:H:i:s',
        ]);
        $partida = $eventoPartida->partida;// Busca a partida relacionada ao evento
        // Verifica se o novo participante pertence a uma das equipes da partida e possui contrato ativo
        $participante = Participante::where('id', $dados['participante_id'])->where('status', 'ATIVO')->whereHas('contratos', function ($query) use ($partida) {
            $query->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->where('status', 'ATIVO'); })->first();
        // Se o participante não pertence à partida, impede a alteração
        if (!$participante) {
            return back()->withInput()->withErrors(['participante_id' => 'O participante não pertence a uma das equipes desta partida.']);
        }
        // Busca o contrato ativo do participante que estava cadastrado anteriormente no evento
        $contratoAntigo = $eventoPartida->participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
        // se o evento era gol remove o gal mas não permite negativo
        if ($eventoPartida->tipo === 'GOL' && $contratoAntigo) {
            if ($contratoAntigo->equipe_id == $partida->mandante_id) {
                // Remove um gol do mandante, sem permitir valor negativo
                $partida->gols_mandante = max(0, $partida->gols_mandante - 1);
            } else {
                $partida->gols_visitante = max(0, $partida->gols_visitante - 1);
            }
        }
        //se o evento era gol contra deve tirar o gol, mas limita para n ficar negativo
        if ($eventoPartida->tipo === 'GOL_CONTRA' && $contratoAntigo) {
            if ($contratoAntigo->equipe_id == $partida->mandante_id) {
                $partida->gols_visitante = max(0, $partida->gols_visitante - 1);
            } else {
                $partida->gols_mandante = max(0, $partida->gols_mandante - 1);
            }
        }
        //busca contrato do participante
        $contrato = $participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
        // só garante que o participante tem contrato com as equipes da partida
        if (!$contrato) {
            return back()->withInput()->withErrors(['participante_id' => 'Não foi encontrado um contrato ativo para este participante nesta partida.']);
        }
        $eventoPartida->update([
            'participante_id' => $participante->id,
            'tipo' => $dados['tipo'],
            'tempo' => $dados['tempo'],
        ]);
        //gol aumenta para quem foi feito
        if ($dados['tipo'] === 'GOL') {
            if ($contrato->equipe_id == $partida->mandante_id) {
                $partida->gols_mandante++;
            } else {
                $partida->gols_visitante++;
            }
        }
        //defini o gol contra para a equipe adversária
        if ($dados['tipo'] === 'GOL_CONTRA') {
            if ($contrato->equipe_id == $partida->mandante_id) {
                $partida->gols_visitante++;
            } else {
                $partida->gols_mandante++;
            }
        }
        $partida->save();// salva o resultado
        return redirect()->route('eventoPartidas.index')->with('success', 'Evento atualizado com sucesso!');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventoPartida $eventoPartida)
    {
        $partida = $eventoPartida->partida;// Busca a partida relacionada ao evento
        // Não permite excluir eventos de partidas finalizadas
        if ($partida->status === 'FINALIZADA') {
            return back()->with('error', 'Não é possível excluir eventos de uma partida finalizada.');
        }
        // Busca o contrato do participante do evento
        $contrato = $eventoPartida->participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
        // Desfaz o efeito do evento no placar, mas n pode ficar negativo
        if ($eventoPartida->tipo === 'GOL' && $contrato) {
            if ($contrato->equipe_id == $partida->mandante_id) {
                $partida->gols_mandante = max(0, $partida->gols_mandante - 1);
            } else {
                $partida->gols_visitante = max(0, $partida->gols_visitante - 1);
            }
        }
        // Desfaz o efeito do gol contra, mas não pode ficar negativo
        if ($eventoPartida->tipo === 'GOL_CONTRA' && $contrato) {

            if ($contrato->equipe_id == $partida->mandante_id) {
                $partida->gols_visitante = max(0, $partida->gols_visitante - 1);
            } else {
                $partida->gols_mandante = max(0, $partida->gols_mandante - 1);
            }
        }
        $partida->save();// Salva o novo placar
        $eventoPartida->delete();// Exclui o evento
        return redirect()->route('eventoPartidas.index')->with('success', 'Evento excluído com sucesso!');
    }
}
