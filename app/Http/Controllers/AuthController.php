<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        /*valida os campos obrigatórios do formulário de login*/
        $credenciais = $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]
        );

        /*verifica se as credenciais informadas são válidas*/
        if (Auth::attempt($credenciais)) {
            $request->session()->regenerate();   /*Regenera a sessão para evitar ataques*/
            /*Redireciona o usuário autenticado para o dashboard*/
            return redirect()->route('Dashboard');
        }
        /*retorna para a tela de login com uma mensagem de erro e mantém o e-mail informado*/
        return back()->withErrors([
            'login' => 'E-mail ou senha inválidos.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        /*Encerra a autenticação do usuário*/
        Auth::logout();
        $request->session()->invalidate();  /*Invalida a sessão atual*/
        $request->session()->regenerateToken();  /*Gera um novo token CSRF*/
        /*Redireciona o usuário para a página inicial*/
        return redirect()->route('PaginaInicial');
    }
}
