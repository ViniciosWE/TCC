<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Partida;
use Illuminate\Http\Request;

class PartidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Busca os campeonatos que estão em andamento
        $campeonatos = Campeonato::where('status', 'EM_ANDAMENTO')
            ->latest()
            ->get();

        // Começa sem nenhuma partida selecionada
        $partidas = collect();

        // Verifica se foi selecionado um campeonato
        if ($request->filled('campeonato_id')) {

            $partidas = Partida::with(['mandante', 'visitante'])
                ->where('campeonato_id', $request->campeonato_id)
                ->orderBy('data_hora')
                ->get();
        }

        return view('areaAdministrativa.partidas.index', compact(
            'campeonatos',
            'partidas'
        ));
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partida $partida)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partida $partida)
    {
        //
    }
}
