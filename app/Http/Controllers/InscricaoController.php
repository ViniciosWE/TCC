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
        $campeonatos = Campeonato::where('status', 'INSCRICOES')->orderBy('nome')->get(); // busca os campeonatos em inscrições 
        $equipes = Equipe::where('status', 'ATIVA')->orderBy('nome')->get(); // busca apenas equipes ativas
        return view('areaAdministrativa.inscricoes.create', compact('campeonatos', 'equipes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dados = $request->validate([
            'campeonato_id' => 'required|exists:campeonatos,id',
            'equipe_id' => 'required|exists:equipes,id,status,ATIVA',
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
        return redirect()->route('inscricoes.index')->with('success', 'Inscrição cadastrada com sucesso!')->with('comprovante', $inscricao->id);
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
        $campeonatos = Campeonato::orderBy('nome')->get();
        $equipes = Equipe::orderBy('nome')->get();
        return view('areaAdministrativa.inscricoes.edit', compact('inscricao', 'equipes', 'campeonatos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inscricao $inscricao)
    {
        $dados = $request->validate([
            'campeonato_id' => 'required|exists:campeonatos,id',
            'equipe_id' => 'required|exists:equipes,id,status,ATIVA',
        ]);
        $campeonato = Campeonato::findOrFail($dados['campeonato_id']);// Busca o campeonato
        // Verifica se já existe outra inscrição com a mesma equipe no campeonato
        if (Inscricao::where('campeonato_id', $campeonato->id)->where('equipe_id', $dados['equipe_id'])->where('id', '!=', $inscricao->id)->exists()) {
            return back()->withInput()->withErrors(['equipe_id' => 'Esta equipe já está inscrita neste campeonato.']);
        }
        // Conta as equipes inscritas, ignorando a inscrição que está sendo editada
        $totalEquipes = Inscricao::where('campeonato_id', $campeonato->id)->where('id', '!=', $inscricao->id)->count();
        // Verifica o limite máximo de equipes
        if ($totalEquipes >= $campeonato->maximo_equipes) {
            return back()->withInput()->withErrors(['campeonato_id' => 'Este campeonato já atingiu o número máximo de equipes.']);
        }
        $equipe = Equipe::findOrFail($dados['equipe_id']);// Busca a equipe
        // Conta somente os jogadores ativos
        $totalJogadores = Contrato::where('equipe_id', $equipe->id)
            ->where('status', 'ATIVO')->whereHas('participante', function ($query) {
                $query->whereNotIn('funcao', [
                    'TECNICO',
                    'AUXILIAR_TECNICO',
                    'PREPARADOR_FISICO',
                ]);
            })->count();
        // Verifica o mínimo de jogadores
        if ($totalJogadores < $campeonato->minimo_jogadores_equipes) {
            return back()->withInput()->withErrors(['equipe_id' => "A equipe precisa ter pelo menos {$campeonato->minimo_jogadores_equipes} jogadores para participar deste campeonato. Atualmente possui {$totalJogadores}."]);
        }
        $inscricao->update($dados); // Atualiza a inscrição
        return redirect()->route('inscricoes.index')->with('success', 'Inscrição atualizada com sucesso!')->with('comprovante', $inscricao->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inscricao $inscricao)
    {
        //só pode excluir a inscrição se o campeonato esta no status de inscrições, senão não é possivel
        if ($inscricao->campeonato->status != 'INSCRICOES') {
            return redirect()->route('inscricoes.index')->with('error', 'Não é possível excluir a inscrição. O campeonato não está na fase de inscrições.');
        }
        $inscricao->delete();
        return redirect()->route('inscricoes.index')->with('success', 'Inscrição excluída com sucesso!');
    }

    public function comprovante(Inscricao $inscricao)
    {
        $inscricao->load(['equipe', 'campeonato']);// Carrega os relacionamentos da inscrição
        $pdf = Pdf::loadView('areaAdministrativa.inscricoes.comprovante', compact('inscricao'));// Gera o PDF novamente
        return $pdf->stream('comprovante-inscricao-' . $inscricao->id . '.pdf');// Abre o PDF no navegador
    }
}
