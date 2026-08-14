<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Contrato;
use App\Models\Equipe;
use App\Models\Inscricao;
use Illuminate\Http\Request;

class InscricaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inscricoes = Inscricao::with(['equipe', 'campeonato'])->latest()->get();
        return view('areaAdministrativa.inscricoes.index', compact('inscricoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $campeonatos = Campeonato::where('status', 'INSCRICOES')->orderBy('nome')->get();
        $equipes = Equipe::orderBy('nome')->get();
        return view('areaAdministrativa.inscricoes.create', compact('campeonatos', 'equipes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dados = $request->validate([
            'campeonato_id' => 'required|exists:campeonatos,id',
            'equipe_id' => 'required|exists:equipes,id',
        ]);
        $campeonato = Campeonato::findOrFail($dados['campeonato_id']); // Busca o campeonato

        // Verifica se o campeonato ainda está aceitando inscrições
        if ($campeonato->status !== 'INSCRICOES') {
            return back()->withInput()->with('error', 'Este campeonato não está aceitando inscrições.');
        }

        // Verifica se a equipe já está inscrita
        if (Inscricao::where('campeonato_id', $campeonato->id)->where('equipe_id', $dados['equipe_id'])->exists()) {
            return back()->withInput()->with('error', 'Esta equipe já está inscrita neste campeonato.');
        }

        $totalEquipes = Inscricao::where('campeonato_id', $campeonato->id)->count();  // Conta quantas equipes já estão inscritas

        // Verifica se atingiu o limite de equipes
        if ($totalEquipes >= $campeonato->maximo_equipes) {
            return back()->withInput()->with('error', 'Este campeonato já atingiu o número máximo de equipes.');
        }
        $equipe = Equipe::findOrFail($dados['equipe_id']); // Busca a equipe

        $totalJogadores = Contrato::where('equipe_id', $equipe->id)->where('status', 'ATIVO')
            ->whereHas('participante', function ($query) {
                $query->whereNotIn('funcao', ['TECNICO', 'AUXILIAR_TECNICO', 'PREPARADOR_FISICO',]);
            })->count();

        // Verifica o mínimo de jogadores
        if ($totalJogadores < $campeonato->minimo_jogadores_equipes) {
            return back()->withInput()->with('error', "A equipe precisa ter pelo menos {$campeonato->minimo_jogadores_equipes} jogadores para participar deste campeonato. Atualmente possui {$totalJogadores}.");
        }

        $dados['user_id'] = auth()->id(); // Adiciona o usuário responsável pela inscrição
        Inscricao::create($dados); // Cria a inscrição
        return redirect()->route('inscricoes.index')->with('success', 'Inscrição cadastrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Inscricao $inscricao)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inscricao $inscricao)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inscricao $inscricao)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inscricao $inscricao)
    {
        //
    }
}
