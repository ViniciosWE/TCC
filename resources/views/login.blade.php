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
            <h1 class="fw-bold text-uppercase text-white display-3">
                Esporte <span class="cor-logo-login">Total</span>
            </h1>
            {{-- Card Login --}}
            <div class="bg-white p-5 rounded-4 mt-4">
                <h1 class="fw-bold cor-h1-login mb-4 text-uppercase">
                    Página de Login
                </h1>
                <a class="botao-voltar-login px-4 py-2 fw-bold text-decoration-none d-inline-block"
                    href="{{ route('PaginaInicial') }}">
                    Voltar
                </a>

            </div>
        </div>
    </main>
</body>

</html>