<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Equipe;
use App\Models\Participante;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contratos = Contrato::with(['equipe', 'participante'])->latest()->get();  // Busca os contratos junto com a equipe e o participante
        return view('areaAdministrativa.contratos.index', compact('contratos'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $equipes = Equipe::where('status', 'ATIVA')->orderBy('nome')->get();// Busca somente equipes que estão ativas e suspensas
        $participantes = Participante::Where('status', 'SEM_EQUIPE')->orderBy('nome')->get();//Busca somente equipes sem equipes e suspensos
        return view('areaAdministrativa.contratos.create', compact('equipes', 'participantes')); //Retorna a view de cadastro
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dados = $request->validate([
            'equipe_id' => 'required|exists:equipes,id',
            'participante_id' => 'required|exists:participantes,id',
            'status' => 'required|in:ATIVO,ENCERRADO',
        ]);
        $contrato = Contrato::create($dados);// Cria o contrato
        // Se o contrato estiver ativo, atualiza o participante
        if ($dados['status'] === 'ATIVO') {
            $contrato->participante->update(['status' => 'ATIVO',]);
        }
        return redirect()->route('contratos.index')->with('success', 'Contrato cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contrato $contrato)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contrato $contrato)
    {
        $equipes = Equipe::where('status', 'ATIVA')->orderBy('nome')->get();// Busca somente equipes ativas
        return view('areaAdministrativa.contratos.edit', compact('equipes', 'contrato'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contrato $contrato)
    {
        $dados = $request->validate([
            'equipe_id' => 'required|exists:equipes,id',
            'status' => 'required|in:ATIVO,ENCERRADO',
        ]);
        // Atualiza o contrato
        $contrato->update([
            'equipe_id' => $dados['equipe_id'],
            'status' => $dados['status'],
        ]);
        // Se o contrato estiver ativo, o participante fica ativo
        if ($dados['status'] === 'ATIVO') {
            $contrato->participante->update(['status' => 'ATIVO',]);
        }
        // Se o contrato for encerrado, o participante fica sem equipe
        else {
            $contrato->participante->update(['status' => 'SEM_EQUIPE',]);
        }
        return redirect()->route('contratos.index')->with('success', 'Contrato atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contrato $contrato)
    {
        $participante = $contrato->participante;

        // Verifica se o participante já possui eventos em partidas
        if ($participante->eventos()->exists()) {
            return back()->withErrors(['error' => 'Não é possível excluir este contrato, pois o participante já possui eventos registrados em partidas.']);
        }
        $participante->update([
            'status' => 'SEM_EQUIPE',
        ]);
        $contrato->delete();
        return redirect()->route('contratos.index')->with('success', 'Contrato excluído com sucesso!');
    }
}
