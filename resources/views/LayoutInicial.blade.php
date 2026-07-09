<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <title>@yield('title')</title>
</head>

<body>
    <header>
        <h1>Esporte Total</h1>
        <nav>
            <ul>
                <li><a href="{{ route('PaginaInicial') }}">Início</a></li>
                <li><a href="{{ route('PaginaCampeonatos') }}">Campeonatos</a></li>
                <li><a href="{{ route('PaginaNoticias') }}">Notícias</a></li>
                <li><a href="{{ route('login') }}">Painel ADM</a></li>
            </ul>
        </nav>

    </header>
    <h2>Nossa Cidade entra em jogo</h2>
    <p>Resultados, tabelas e emoção</p>

    <div>
        @yield('content')
    </div>


    <footer>
        <h2>Esporte Total Municipal</h2>
        <h3>Desenvolvido para incentivar o talento local</h3>
        <hr>
        <p>&copy; {{ date('Y') }} - Vinicios Weide Ebling</p>
    </footer>
</body>

</html>