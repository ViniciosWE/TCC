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
        $campeonatos = Campeonato::where('status', 'EM_ANDAMENTO')->latest()->get();// Campeonatos em andamento para aparecer no datalist
        $partidas = collect();// Começa sem partidas
        // Se um campeonato foi selecionado
        if ($request->filled('campeonato_id')) {
            $partidas = Partida::with([
                'mandante',
                'visitante'
            ])->where('campeonato_id', $request->campeonato_id)->orderBy('data_hora')->get();
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
        return redirect()->route('partidas.index')->with('success', 'Data, hora e local da partida definidos com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partida $partida)
    {
        //
    }
}
