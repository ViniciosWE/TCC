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
        // Busca os participantes e seus contratos ativos com a equipe
        $participantes = Participante::with([
            'contratos' => function ($query) {
                $query->where('status', 'ATIVO');
            },
            'contratos.equipe'
        ])->latest()->get();
        return view('areaAdministrativa.participantes.index', compact('participantes'));  // Retorna a view com os participantes
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $equipes = Equipe::where('status', 'ATIVA')->orderBy('nome')->get();// Busca somente equipes que estão ativas
        return view('areaAdministrativa.participantes.create', compact('equipes')); // Retorna a página de cadastro dos participantes
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Valida os dados do participante
        $dados = $request->validate(
            [
                'nome' => 'required',
                'cpf' => 'required|max:11|unique:participantes,cpf',
                'status' => 'required',
                'numero' => 'nullable|between:0,99',
                'funcao' => 'nullable',
                'equipe_id' => 'required_if:status,ATIVO|nullable|exists:equipes,id',
            ],
            [
                'cpf.unique' => 'Já existe um participante cadastrado com esse CPF.',
                'equipe_id.required_if' => 'Um participante ativo precisa estar vinculado a uma equipe.',
            ]
        );
        // Verifica se a equipe escolhida pode receber jogadores
        if ($dados['status'] === 'ATIVO') {
            $equipe = Equipe::find($dados['equipe_id']);
            if (!$equipe || !in_array($equipe->status, ['ATIVA', 'SUSPENSA'])) {
                return back()->withErrors(['equipe_id' => 'A equipe selecionada está encerrada e não pode receber jogadores.'])->withInput();
            }
        }
        // Cria o participante
        $participante = Participante::create([
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'status' => $dados['status'],
            'numero' => $dados['numero'] ?? null,
            'funcao' => $dados['funcao'] ?? null,
        ]);

        // Se o participante estiver ativo, cria o contrato
        if ($dados['status'] === 'ATIVO') {
            $participante->contratos()->create([
                'equipe_id' => $dados['equipe_id'],
                'status' => 'ATIVO',
            ]);
        }

        return redirect()->route('participantes.index')->with('success', 'Participante cadastrado com sucesso!');// Retorna para o index
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
        $equipes = Equipe::where('status', 'ATIVA')->orderBy('nome')->get();// Busca somente equipes que estão ativas
        $contrato = $participante->contratos()->where('status', 'ATIVO')->with('equipe')->first();// Busca o contrato ativo do participante
        return view('areaAdministrativa.participantes.edit', compact('participante', 'equipes', 'contrato'));// Retorna a página de edição
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
                'numero' => 'nullable|between:0,99',
                'funcao' => 'nullable',
                'equipe_id' => 'required_if:status,ATIVO|nullable|exists:equipes,id',
            ],
            [
                'cpf.unique' => 'Já existe um participante cadastrado com esse CPF.',
                'equipe_id.required_if' => 'Um participante ativo precisa estar vinculado a uma equipe.',
            ]
        );

        // Se o participante estiver ATIVO, verifica se a equipe está ativa
        if ($dados['status'] === 'ATIVO') {
            $equipe = Equipe::find($dados['equipe_id']);
            if (!$equipe || $equipe->status !== 'ATIVA') {
                return back()->withErrors(['equipe_id' => 'A equipe selecionada não está ativa e não pode receber jogadores.'])->withInput();
            }
        }
        // Atualiza os dados do participante
        $participante->update([
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'status' => $dados['status'],
            'numero' => $dados['numero'] ?? null,
            'funcao' => $dados['funcao'] ?? null,
        ]);

        $contrato = $participante->contratos()->where('status', 'ATIVO')->first();// Busca o contrato ativo do participante

        if ($dados['status'] === 'ATIVO') {
            // Se já possui contrato, atualiza a equipe
            if ($contrato) {
                $contrato->update(['equipe_id' => $dados['equipe_id'],]);
            } else {
                // Se não possui contrato, cria um novo
                $participante->contratos()->create([
                    'equipe_id' => $dados['equipe_id'],
                    'status' => 'ATIVO',
                ]);
            }
        }
        elseif ($dados['status'] === 'SUSPENSO') {
            // Encerra o contrato ativo
            if ($contrato) {
                $contrato->update(['status' => 'ENCERRADO',]);
            }
        }

        elseif ($dados['status'] === 'APOSENTADO') {
            // Encerra o contrato ativo
            if ($contrato) {
                $contrato->update(['status' => 'ENCERRADO',]);
            }
        }

        elseif ($dados['status'] === 'SEM_EQUIPE') {
            // Garante que não fique com contrato ativo
            if ($contrato) {
                $contrato->update(['status' => 'ENCERRADO',]);
            }
        }

        return redirect()->route('participantes.index')->with('success', 'Participante atualizado com sucesso!'); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Participante $participante)
    {
        $possuiContrato = $participante->contratos()->exists();// Verifica se o participante possui algum contrato
        // Se possuir qualquer contrato, não permite a exclusão
        if ($possuiContrato) {
            return redirect()->route('participantes.index')->with('error', 'O participante possui contratos e não pode ser excluído. Dessa maneira, ele deverá ser aposentado.');
        }

        // Se nunca teve contrato, permite a exclusão
        $participante->delete();
        return redirect()->route('participantes.index')->with('success', 'Participante excluído com sucesso!');
    }
}
