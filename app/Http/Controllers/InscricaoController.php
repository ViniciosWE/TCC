<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Contrato;
use App\Models\Equipe;
use App\Models\Inscricao;
use Barryvdh\DomPDF\Facade\Pdf;
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

        // Verifica se a equipe já está inscrita
        if (Inscricao::where('campeonato_id', $campeonato->id)->where('equipe_id', $dados['equipe_id'])->exists()) {
            return back()->withInput()->withErrors(['equipe_id' => 'Esta equipe já está inscrita neste campeonato.']);
        }
        $totalEquipes = Inscricao::where('campeonato_id', $campeonato->id)->count();// Conta as equipes inscritas
        // Verifica limite de equipes
        if ($totalEquipes >= $campeonato->maximo_equipes) {
            return back()->withInput()->withErrors(['campeonato_id' => 'Este campeonato já atingiu o número máximo de equipes.']);
        }

        $equipe = Equipe::findOrFail($dados['equipe_id']);

        // Conta somente jogadores
        $totalJogadores = Contrato::where('equipe_id', $equipe->id)
            ->where('status', 'ATIVO')
            ->whereHas('participante', function ($query) {
                $query->whereNotIn('funcao', ['TECNICO', 'AUXILIAR_TECNICO', 'PREPARADOR_FISICO',]);
            })->count();

        // Verifica mínimo de jogadores
        if ($totalJogadores < $campeonato->minimo_jogadores_equipes) {
            return back()->withInput()->withErrors(['equipe_id' => "A equipe precisa ter pelo menos {$campeonato->minimo_jogadores_equipes} jogadores para participar deste campeonato. Atualmente possui {$totalJogadores}."]);
        }
        $dados['user_id'] = auth()->id();
        $inscricao = Inscricao::create($dados);
        $inscricao->load(['equipe', 'campeonato']);// Busca a inscrição novamente com os relacionamentos
        $pdf = Pdf::loadView('areaAdministrativa.inscricoes.comprovante', compact('inscricao')); // Gera o PDF
        return $pdf->stream('comprovante-inscricao-' . $inscricao->id . '.pdf');// Retorna o PDF para o navegador
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
        $inscricao->delete();
        return redirect()->route('inscricoes.index')->with('success', 'Inscrição excluída com sucesso!');
    }
}                                                   
