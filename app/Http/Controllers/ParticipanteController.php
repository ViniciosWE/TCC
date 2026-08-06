<?php

namespace App\Http\Controllers;
use App\Models\Contrato;
use App\Models\Equipe;
use App\Models\Participante;
use Illuminate\Http\Request;

class ParticipanteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $participantes = Participante::latest()->get(); // Busca os participantes começando pelos mais recentes
        return view('areaAdministrativa.participantes.index', compact('participantes')); // Retorna a view com as lista de todos os participantes
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $equipes = Equipe::latest()->get(); // busca todas as equipes
        return view('areaAdministrativa.participantes.create', compact('equipes')); // Retorna a página de cadastro dos participantes
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Realiza a validação dos dados enviados pelo formulário
        $participante = $request->validate(
            [
                'nome' => 'required',
                'cpf' => 'required|max:11|unique:participantes,cpf',
                'status' => 'required',
                'numero' => 'nullable',
                'funcao' => 'nullable',
            ],
            [
                'cpf.unique' => 'já existe um participante cadastrado com esse CPF',
            ]
        );

        //Cria o registro no banco
        Participante::create($participante);
        //Retorna para o index retornando a mensagem de sucesso
        return redirect()->route('participantes.index')->with('success', 'Participante cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Participante $participante)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Participante $participante)
    {
        $equipes = Equipe::latest()->get(); // busca todas as equipes
        return view('areaAdministrativa.participantes.edit', compact('participante', 'equipes')); //Retorna a página de editar com os dados do participante selecionado
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Participante $participante)
    {
        // Valida os dados enviados pelo formulário
        $dados = $request->validate(
            [
                'nome' => 'required',
                'cpf' => 'required|max:11|unique:participantes,cpf,' . $participante->id,
                'status' => 'required',
                'numero' => 'nullable',
                'funcao' => 'nullable',
            ],
            [
                'cpf.unique' => 'já existe um participante cadastrado com esse CPF',
            ]
        );

        // Atualiza os dados da equipe
        $participante->update($dados);
        // Retorna para a listagem
        return redirect()->route('participantes.index')->with('success', 'Participante atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Participante $participante)
    {
        // Remove a equipe do banco de dados
        $participante->delete();
        //Retorna para o index retornando a mensagem de sucesso
        return redirect()->route('participantes.index')
            ->with('success', 'Participante excluído com sucesso!');
    }
}
