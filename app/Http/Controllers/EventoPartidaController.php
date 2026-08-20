<?php

namespace App\Http\Controllers;

use App\Models\EventoPartida;
use App\Models\Participante;
use Illuminate\Http\Request;

class EventoPartidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('areaAdministrativa.eventoPartidas.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $participantes = Participante::Where('status', 'ATIVO')->orderBy('nome')->get();
        return view('areaAdministrativa.eventoPartidas.create', compact('participantes'));
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
    public function show(EventoPartida $eventoPartida)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventoPartida $eventoPartida)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventoPartida $eventoPartida)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventoPartida $eventoPartida)
    {
        //
    }
}
