<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use Illuminate\Http\Request;

class CampeonatoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $campeonatos = Campeonato::latest()->get();
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
        //
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

        $dados['user_id'] = auth()->id(); //pega o id de quem criou 
        $campeonato->update($dados); // edita
        return redirect()->route('campeonatos.index')->with('success', 'Campeonato atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campeonato $campeonato)
    {
        $campeonato->delete();
         return redirect()->route('campeonatos.index')->with('success', 'Campeonato excluído com sucesso!');
    }
}
