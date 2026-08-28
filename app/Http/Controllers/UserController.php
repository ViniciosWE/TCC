<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\EventoPartida;
use App\Models\Inscricao;
use App\Models\Noticia;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::latest()->get();
        return view('areaAdministrativa.user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('areaAdministrativa.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'tipo' => 'required|in:ADMINISTRADOR,SUPER_ADMINISTRADOR',
        ], [
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
        ]);
        //cria registro no banco
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tipo' => $request->tipo,
        ]);
        return redirect()->route('user.index')->with('success', 'Usuário cadastrado com sucesso!');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('areaAdministrativa.user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'tipo' => 'required|in:ADMINISTRADOR,SUPER_ADMINISTRADOR',
        ], [
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.min' => 'A nova senha deve ter no mínimo 8 caracteres.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->tipo = $request->tipo;

        // Só altera a senha se o campo foi preenchido
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return redirect()->route('user.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //verifica se tem relação
        $temRelacao =
            Campeonato::where('user_id', $user->id)->exists() ||
            Noticia::where('user_id', $user->id)->exists() ||
            EventoPartida::where('user_id', $user->id)->exists() ||
            Inscricao::where('user_id', $user->id)->exists();

        //se tiver relação não deixa excluir
        if ($temRelacao) {
            return redirect()->route('user.index')->with('error', 'Não é possível excluir o usuário porque existem registros relacionados a ele.');
        }

        //Não deixa excluir própio usuário
        if (auth()->id() === $user->id) {
            return redirect()->route('user.index')->with('error', 'Você não pode excluir o próprio usuário logado.');
        }
        $user->delete();
        return redirect()->route('user.index')->with('success', 'Usuário excluído com sucesso!');
    }

    public function perfil()
    {
        $user = auth()->user();
        return view('areaAdministrativa.user.perfil', compact('user'));
    }

    public function atualizarPerfil(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ], [
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.min' => 'A nova senha deve ter no mínimo 8 caracteres.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Só altera a senha se foi preenchida
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return redirect()->route('user.perfil')->with('success', 'Perfil atualizado com sucesso!');
    }
}
