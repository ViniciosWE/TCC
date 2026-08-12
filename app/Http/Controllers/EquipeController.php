<?php

namespace App\Http\Controllers;

use App\Models\Equipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //busca o total de participantes ativos nas equipes
        $equipes = Equipe::withCount([
            'contratos as participantes_ativos_count' => function ($query) {
                $query->where('status', 'ATIVO');
            }
        ])->latest()->get();
        return view('areaAdministrativa.equipes.index', compact('equipes')); //Retorna a página de index
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('areaAdministrativa.equipes.create'); //Retorna a página de cadastro de equipes
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Realiza a validação dos dados enviados pelo formulário
        $equipe = $request->validate(
            [
                'nome' => 'required|unique:equipes,nome',
                'sigla' => 'required|max:3|unique:equipes,sigla',
                'escudo' => 'required|image',
                'status' => 'required',
            ],
            [
                'nome.unique' => 'Já existe uma equipe cadastrada com esse nome.',
                'sigla.unique' => 'Já existe uma equipe cadastrada com essa sigla.',
                'escudo.image' => 'O arquivo enviado deve ser uma imagem válida.',
            ]
        );

        // Verifica se foi enviado um arquivo de escudo
        if ($request->hasFile('escudo')) {
            //Armazena a imagem no storage público dentro da pasta escudo-equipes e retorna o caminho da imagem
            $path = $request->file('escudo')->store('escudo-equipes', 'public');
            $equipe['escudo'] = $path;
        }
        Equipe::create($equipe);//Cria o registro no banco 
        return redirect()->route('equipes.index')->with('success', 'Equipe cadastrada com sucesso!');//Retorna para o index retornando a mensagem de sucesso
    }

    /**
     * Display the specified resource.
     */
    public function show(Equipe $equipe)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Equipe $equipe)
    {
        return view('areaAdministrativa.equipes.edit', compact('equipe')); //Retorna a página de editar com os dados da equipe selecionada
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Equipe $equipe)
    {
        // Valida os dados enviados pelo formulário
        $dados = $request->validate(
            [
                'nome' => 'required|unique:equipes,nome,' . $equipe->id,
                'sigla' => 'required|max:3|unique:equipes,sigla,' . $equipe->id,
                'escudo' => 'nullable|image',
                'status' => 'required',
            ],
            [
                'nome.unique' => 'Já existe uma equipe cadastrada com esse nome.',
                'sigla.unique' => 'Já existe uma equipe cadastrada com essa sigla.',
                'escudo.image' => 'O arquivo enviado deve ser uma imagem válida.',
            ]
        );
        // Verifica se foi enviada uma nova imagem
        if ($request->hasFile('escudo')) {
            // Remove o escudo antigo do storage
            if ($equipe->escudo && Storage::disk('public')->exists($equipe->escudo)) {
                Storage::disk('public')->delete($equipe->escudo);
            }
            // Salva o novo escudo
            $dados['escudo'] = $request->file('escudo')->store('escudo-equipes', 'public');
        }
        $equipe->update($dados); // Atualiza os dados da equipe
        return redirect()->route('equipes.index')->with('success', 'Equipe atualizada com sucesso!');// Retorna para o index
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipe $equipe)
    {

        $possuiContrato = $equipe->contratos()->exists();// Verifica se a equipe possui algum contrato
        // Se possuir contratos, não permite a exclusão
        if ($possuiContrato) {
            return redirect()->route('equipes.index')->with('error', 'A equipe possui contratos e não pode ser excluída. Dessa maneira, ela deverá ser encerrada.');
        }
        // Se nunca teve contratos, remove o escudo
        if ($equipe->escudo && Storage::disk('public')->exists($equipe->escudo)) {
            Storage::disk('public')->delete($equipe->escudo);
        }
        $equipe->delete(); // Remove a equipe do banco
        return redirect()->route('equipes.index')->with('success', 'Equipe excluída com sucesso!');
    }
}
