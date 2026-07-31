<?php

namespace App\Http\Controllers;

use App\Models\Equipe;
use Illuminate\Http\Request;

class EquipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('areaAdministrativa.equipes.index'); //Retorna o index das página de equipes 
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
        $equipe = $request->validate([
            'nome' => 'required',
            'sigla' => 'required|max:3',
            'escudo' => 'nullable|image',
            'status' => 'required',
        ]);

        // Verifica se foi enviado um arquivo de escudo
        if ($request->hasFile('escudo')) {
            //Armazena a imagem no storage público dentro da pasta escudo-equipes e retorna o caminho da imagem
            $path = $request->file('escudo')->store('escudo-equipes', 'public');
            $equipe['escudo'] = $path;
        }
        //Cria o registro no banco
        Equipe::create($equipe);
        //Retorna para o index retornando a mensagem de sucesso
        return redirect()->route('areaAdministrativa.equipes.index')->with('success', 'Equipe cadastrada com sucesso!');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Equipe $equipe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipe $equipe)
    {
        //
    }
}
