<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
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
        return view('areaAdministrativa.campeonatos.show', compact('campeonato'));
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
        $equipes = $equipes->values(); // Organiza os índices das equipes
        // Adiciona uma folga se houver número ímpar de equipes
        if ($equipes->count() % 2 != 0) {
            $equipes->push(null);
        }
        $quantidadeEquipes = $equipes->count();
        $numeroRodadas = $quantidadeEquipes - 1;
        $partidasPorRodada = $quantidadeEquipes / 2;
        // Gera as rodadas
        for ($rodada = 1; $rodada <= $numeroRodadas; $rodada++) {
            // Gera os jogos da rodada
            for ($jogo = 0; $jogo < $partidasPorRodada; $jogo++) {
                $mandante = $equipes[$jogo];
                $visitante = $equipes[$quantidadeEquipes - 1 - $jogo];
                // Ignora a folga
                if ($mandante === null || $visitante === null) {
                    continue;
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
                    'fase' => 'RODADA_' . $rodada,
                    'local' => null,
                ]);
            }
            // Mantém a primeira equipe fixa e gira as outras
            $primeira = $equipes->shift();
            $ultimo = $equipes->pop();
            $equipes->prepend($ultimo);
            $equipes->prepend($primeira);
        }
    }
}
