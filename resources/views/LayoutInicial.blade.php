<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <header class="layout-bg-nav">
        <!--navbar do Bootstrap responsável pela navegação do site-->
        <nav class="navbar navbar-expand-lg ">
            <!-- container responsivo ocupando toda a largura disponível, com espaçamento lateral usando px-4-->
            <div class="container-fluid px-4">
                <!--nome do site que recebe o link da página incial-->
                <a class="navbar-brand fw-bold text-uppercase text-white fs-1" href="{{ route('PaginaInicial') }}">
                    Esporte <span class="cor-texto-amarelo">Total</span>
                </a>
                <!--botão hamburguer exibido em telas menores, o Bootstrap controla a abertura e fechamento do menu através do collapse abaixo-->
                <button class="navbar-toggler border-0 menu-inicial-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#menuNavbar" aria-controls="menuNavbar" aria-label="Toggle navigation">
                    <i class="bi bi-list fs-1 text-white"></i>
                </button>

                <!--área responsável pelo menu responsivo, no desktop fica aberto e no mobile aparece ao clicar no botão hamburguer-->
                </ul>
                <div class="collapse navbar-collapse" id="menuNavbar">
                    <!--lista das opções-->
                    <ul class="navbar-nav ms-auto fw-bold gap-2">
                        <li class="nav-item">
                            <a class="nav-link menu-header-link {{ request()->routeIs('PaginaInicial') ? 'cor-texto-amarelo' : 'text-white' }}"
                                href="{{ route('PaginaInicial') }}">Início</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-header-link {{ request()->routeIs('PaginaCampeonatos') ? 'cor-texto-amarelo' : 'text-white' }}"
                                href="{{ route('PaginaCampeonatos') }}">Campeonatos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-header-link {{ request()->routeIs('PaginaNoticias') ? 'cor-texto-amarelo' : 'text-white' }}"
                                href="{{ route('PaginaNoticias') }}">Notícias</a>
                        </li>
                        <!-- Botão de acesso administrativo-->
                        <li class="nav-item">
                            <a class="nav-link painel-adm cor-fundo-amarelo cor-texto-azul px-3 py-2 d-inline-flex"
                                href="{{ route('login') }}">
                                <i class="bi bi-lock me-2"></i>
                                Painel ADM
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
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