<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Noticia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $noticias = Noticia::with('campeonato')->latest()->get();
        return view('areaAdministrativa.noticias.index', compact('noticias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $campeonatos = Campeonato::orderBy('nome')->get();
        return view('areaAdministrativa.noticias.create', compact('campeonatos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dados = $request->validate([
            'titulo' => 'required',
            'descricao' => 'required',
            'imagem' => 'nullable|image',
            'campeonato_id' => 'nullable',
        ], [
            'imagem.image' => 'O arquivo enviado deve ser uma imagem válida.',
        ]);

        // Verifica se foi enviado um arquivo de escudo
        if ($request->hasFile('imagem')) {
            //Armazena a imagem no storage público dentro da pasta imagem-noticias e retorna o caminho da imagem
            $path = $request->file('imagem')->store('imagem-noticias', 'public');
            $dados['imagem'] = $path;
        }

        $dados['user_id'] = auth()->id(); //pega o id de quem criou 
        Noticia::create($dados);//Cria o registro no banco 
        return redirect()->route('noticias.index')->with('success', 'Notícia cadastrada com sucesso!');//Retorna para o index retornando a mensagem de sucesso
    }

    /**
     * Display the specified resource.
     */
    public function show(Noticia $noticia)
    {
        return view('areaAdministrativa.noticias.show', compact('noticia'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Noticia $noticia)
    {
        $campeonatos = Campeonato::orderBy('nome')->get();
        return view('areaAdministrativa.noticias.edit', compact('campeonatos', 'noticia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Noticia $noticia)
    {
        $dados = $request->validate([
            'titulo' => 'required',
            'descricao' => 'required',
            'imagem' => 'nullable|image',
            'campeonato_id' => 'nullable',
        ], [
            'imagem.image' => 'O arquivo enviado deve ser uma imagem válida.',
        ]);

        // Verifica se foi enviada uma nova imagem
        if ($request->hasFile('imagem')) {
            // Remove a imagem antiga do storage
            if ($noticia->imagem && Storage::disk('public')->exists($noticia->imagem)) {
                Storage::disk('public')->delete($noticia->imagem);
            }
            // Salva o novo escudo
            $dados['imagem'] = $request->file('imagem')->store('imagem-noticias', 'public');
        }
        $noticia->update($dados); // Atualiza os dados da equipe
        return redirect()->route('noticias.index')->with('success', 'Notícia atualizada com sucesso!');// Retorna para o index

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Noticia $noticia)
    {
        //verifica se existe imagem, caso existir exclui do banco
        if ($noticia->imagem && Storage::disk('public')->exists($noticia->imagem)) {
            Storage::disk('public')->delete($noticia->imagem);
        }
        $noticia->delete(); // Remove a noticia do banco
        return redirect()->route('noticias.index')->with('success', 'Notícia excluída com sucesso!');
    }
    public function paginaPublica()
    {
        $noticias = Noticia::with('campeonato')->latest()->get();
        return view('PaginaNoticias', compact('noticias'));
    }

    public function detalhes(Noticia $noticia){
         return view('PaginaNoticiasDetalhes', compact('noticia'));
    }
}
