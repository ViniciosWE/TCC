<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <title>Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="fundo-login">
    <main class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="text-center">
            {{-- Logo --}}
            <h1 class="fw-bold text-uppercase text-white display-3 mb-4">
                Esporte <span class="cor-logo-login">Total</span>
            </h1>
            {{-- Card de Login --}}
            <div class="bg-white p-5 rounded-4">
                <h2 class="fw-bold cor-h1-login text-uppercase mb-4">
                    Página de Login
                </h2>

                {{-- Mensagem de erro --}}
                @error('login')
                    <div id="mensagemErro" class="alert alert-danger mb-3">
                        {{ $message }}
                    </div>
                @enderror

                <form action="{{ route('LoginSubmit') }}" method="POST">
                    @csrf
                    {{-- E-mail --}}
                    <div class="mb-3 text-start">
                        <label for="email" class="fw-semibold">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                            placeholder="Digite seu e-mail" required>
                    </div>
                    {{-- Senha --}}
                    <div class="mb-3 text-start">
                        <label for="password" class="fw-semibold">Senha</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Digite sua senha" required>
                    </div>
                    {{-- Botão Entrar --}}
                    <button type="submit" class="btn botoes-login mb-3 fw-bold py-2 px-4">Entrar</button>
                </form>
                {{-- Botão de voltar --}}
                <a class="botoes-login px-4 py-2 fw-bold text-decoration-none d-inline-block"
                    href="{{ route('PaginaInicial') }}">
                    Voltar
                </a>
            </div>
        </div>
    </main>
</body>

</html>