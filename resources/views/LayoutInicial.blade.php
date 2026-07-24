<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="cor-fundo-header-inicial">
        {{--navbar do Bootstrap responsável pela navegação do site--}}
        <nav class="navbar navbar-expand-lg">
            {{-- container responsivo ocupando toda a largura disponível, com espaçamento lateral usando px-4--}}
            <div class="container-fluid px-4">
                {{--nome do site que recebe o link da página incial--}}
                <a class="navbar-brand fw-bold text-uppercase text-white fs-1" href="{{ route('PaginaInicial') }}">
                    Esporte<span class="textos-navegacao-inicial">Total</span>
                </a>
                {{-- Menu desktop --}}
                <div class="d-none d-lg-flex">
                    <ul class="navbar-nav ms-auto fw-bold gap-2 text-end">
                        <li class="nav-item">
                            <a class="nav-link menu-header-link {{ request()->routeIs('PaginaInicial') ? 'textos-navegacao-inicial' : 'text-white' }}"
                                href="{{ route('PaginaInicial') }}">
                                Início
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-header-link {{ request()->routeIs('PaginaCampeonatos') ? 'textos-navegacao-inicial' : 'text-white' }}"
                                href="{{ route('PaginaCampeonatos') }}">
                                Campeonatos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-header-link {{ request()->routeIs('PaginaNoticias') ? 'textos-navegacao-inicial' : 'text-white' }}"
                                href="{{ route('PaginaNoticias') }}">
                                Notícias
                            </a>
                        </li>
                        {{-- Botão de login para o painel ADM --}}
                        <li class="nav-item">
                            <a class="nav-link botao-painel-adm px-3 py-2 d-inline-flex" href="{{ route('Login') }}">
                                <i class="bi bi-lock me-2"></i>Painel ADM
                            </a>
                        </li>
                    </ul>
                </div>
                {{-- Botão hamburger mobile --}}
                <button class="navbar-toggler border-0 menu-inicial-toggler d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#menuLateral" aria-controls="menuLateral">
                    <i class="bi bi-list fs-1 text-white"></i>
                </button>
            </div>
        </nav>
    </header>

    {{-- Menu lateral aberto pelo botão hamburger --}}
    <div class="offcanvas offcanvas-end cor-fundo-header-inicial menu-lateral-inicial" id="menuLateral">
        {{-- Cabeçalho do menu lateral --}}
        <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-bold text-white text-uppercase">
                Esporte <span class="textos-navegacao-inicial">Total</span>
            </h5>
            {{-- Botão fechar menu --}}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas">
            </button>
        </div>
        {{-- Links do menu mobile --}}
        <div class="offcanvas-body">
            <ul class="navbar-nav fw-bold gap-2">
                <li class="nav-item">
                    <a class="nav-link menu-header-link {{ request()->routeIs('PaginaInicial') ? 'textos-navegacao-inicial' : 'text-white' }}"
                        href="{{ route('PaginaInicial') }}">
                        Início
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-header-link {{ request()->routeIs('PaginaCampeonatos') ? 'textos-navegacao-inicial' : 'text-white' }}"
                        href="{{ route('PaginaCampeonatos') }}">
                        Campeonatos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-header-link {{ request()->routeIs('PaginaNoticias') ? 'textos-navegacao-inicial' : 'text-white' }}"
                        href="{{ route('PaginaNoticias') }}">
                        Notícias
                    </a>
                </li>
                <hr class="text-white">
                <li class="nav-item">
                    <a class="nav-link botao-painel-adm px-3 py-2 d-inline-flex" href="{{ route('Login') }}">
                        Painel ADM
                    </a>
                </li>
            </ul>
        </div>
    </div>
    {{--banner--}}
    <div class="cor-banner-inicial text-center text-white py-5">
        <h2 class="text-uppercase fw-bold display-4 banner-titulo">Nossa Cidade entra em jogo</h2>
        <p class="banner-subtitulo">Resultados, tabelas e emoção</p>
    </div>

    {{--conteúdo da página--}}
    <main class="flex-grow-1">
        @yield('content')
    </main>

    {{--rodapé--}}
    <footer class="cor-rodape-inicial text-center p-4">
        <h2 class="cor-texto-rodape-amarelo-inicial text-uppercase rodape-titulo">Esporte Total Municipal</h2>
        <h5 class="text-uppercase rodape-sub">Desenvolvido para incentivar o talento local</h5>
        <hr>
        <p>&copy; {{ date('Y') }} - Vinicios Weide Ebling</p>
    </footer>
</body>

</html>