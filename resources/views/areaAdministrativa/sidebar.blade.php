<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="cor-fundo-sidebar">
    @php $user = auth()->user();@endphp

    <header class="cor-fundo-header-sidebar">
        <nav class="navbar px-4 py-3">
            {{-- Logo --}}
            <h1 class="navbar-brand fw-bold text-uppercase text-white fs-2">
                Esporte<span class="cor-logo-sidebar">Total</span>
            </h1>
            {{-- menu do usuário autenticado --}}
            <div class="dropdown ms-auto">
                <button class="btn text-white d-flex align-items-center gap-3 border-0" data-bs-toggle="dropdown">
                    {{-- avatar exibindo a primeira letra do nome --}}
                    <div class="avatar d-flex align-items-center justify-content-center fw-bold fs-5">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <strong class="text-uppercase d-none d-lg-block">Olá, {{ $user->name }}</strong>
                    <i class="bi bi-chevron-down small d-none d-lg-block"></i>
                </button>
                {{-- opções do usuário --}}
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-person me-2"></i>
                            Meu perfil
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        {{-- formulário responsável pelo logout do sistema --}}
                        <form action="{{ route('Logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Sair
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            {{-- Botão hamburguer mobile --}}
            <button class="btn text-white d-lg-none" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#menuSidebar"><i class="bi bi-list fs-1"></i>
            </button>
        </nav>
    </header>
    <div class="d-flex">
        {{-- sidebar desktop --}}
        <aside class="sidebar bg-white border-end p-3 d-none d-lg-block">
            <a href="{{ route('Dashboard') }}"
                class="{{ request()->routeIs('Dashboard') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                <i class="bi bi-house me-2"></i>Dashboard
            </a>
            <a href="{{ route('campeonatos.index') }}"
                class="{{ request()->routeIs('campeonatos.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                <i class="bi bi-trophy-fill me-2"></i>Campeonatos
            </a>

            <a href="{{ route('contratos.index') }}"
                class="{{ request()->routeIs('contratos.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                <i class="bi bi-trophy-fill me-2"></i>Contratos
            </a>

            <a href="{{ route('equipes.index') }}"
                class="{{ request()->routeIs('equipes.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                <i class="bi bi-trophy-fill me-2"></i>Equipes
            </a>

            <a href="{{ route('eventoPartidas.index') }}"
                class="{{ request()->routeIs('eventoPartidas.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                <i class="bi bi-trophy-fill me-2"></i>Eventos das Partidas
            </a>

            <a href="{{ route('inscricoes.index') }}"
                class="{{ request()->routeIs('inscricoes.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                <i class="bi bi-trophy-fill me-2"></i>Inscrições
            </a>

            <a href="{{ route('noticas.index') }}"
                class="{{ request()->routeIs('noticas.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                <i class="bi bi-trophy-fill me-2"></i>Notícias
            </a>

            <a href="{{ route('participantes.index') }}"
                class="{{ request()->routeIs('participantes.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                <i class="bi bi-trophy-fill me-2"></i>Participantes
            </a>

            <a href="{{ route('partidas.index') }}"
                class="{{ request()->routeIs('Partidas.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                <i class="bi bi-trophy-fill me-2"></i>Partidas
            </a>

            <a href="{{ route('user.index') }}"
                class="{{ request()->routeIs('user.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                <i class="bi bi-trophy-fill me-2"></i>Usuários
            </a>
        </aside>

        {{-- menu lateral exibido apenas no mobile --}}
        <div class="offcanvas offcanvas-end menu-sidebar-mobile menu-lateral-sidebar" id="menuSidebar">
            <div class="offcanvas-header cor-fundo-header-sidebar">
                <h5 class="offcanvas-title fw-bold text-uppercase text-white">
                    Esporte<span class="cor-logo-sidebar">Total</span>
                </h5>
                {{-- Botão fechar menu --}}
                <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            {{-- opções de navegação do menu mobile --}}
            <div class="offcanvas-body">
                <a href="{{ route('Dashboard') }}"
                    class="links-sidebar p-2 mb-2 {{ request()->routeIs('Dashboard') ? 'links-ativos-sidebar' : 'text-black' }}">
                    <i class="bi bi-house me-2"></i>Dashboard
                </a>
                <a href="{{ route('campeonatos.index') }}"
                    class="links-sidebar p-2 mb-2 {{ request()->routeIs('campeonatos.index') ? 'links-ativos-sidebar' : 'text-black' }}">
                    <i class="bi bi-trophy-fill me-2"></i>Campeonatos
                </a>

                <a href="{{ route('contratos.index') }}"
                    class="{{ request()->routeIs('contratos.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                    <i class="bi bi-trophy-fill me-2"></i>Contratos
                </a>

                <a href="{{ route('equipes.index') }}"
                    class="{{ request()->routeIs('equipes.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                    <i class="bi bi-trophy-fill me-2"></i>Equipes
                </a>

                <a href="{{ route('eventoPartidas.index') }}"
                    class="{{ request()->routeIs('eventoPartidas.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                    <i class="bi bi-trophy-fill me-2"></i>Eventos das Partidas
                </a>

                <a href="{{ route('inscricoes.index') }}"
                    class="{{ request()->routeIs('inscricoes.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                    <i class="bi bi-trophy-fill me-2"></i>Inscrições
                </a>

                <a href="{{ route('noticas.index') }}"
                    class="{{ request()->routeIs('noticas.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                    <i class="bi bi-trophy-fill me-2"></i>Notícias
                </a>

                <a href="{{ route('participantes.index') }}"
                    class="{{ request()->routeIs('participantes.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                    <i class="bi bi-trophy-fill me-2"></i>Participantes
                </a>

                <a href="{{ route('partidas.index') }}"
                    class="{{ request()->routeIs('Partidas.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                    <i class="bi bi-trophy-fill me-2"></i>Partidas
                </a>

                <a href="{{ route('user.index') }}"
                    class="{{ request()->routeIs('user.index') ? 'links-ativos-sidebar' : 'text-black' }} links-sidebar mb-2 p-2">
                    <i class="bi bi-trophy-fill me-2"></i>Usuários
                </a>
            </div>
        </div>

        {{-- Conteúdo da página --}}
        <main class="flex-grow-1 p-4">
            @yield('content')
        </main>

    </div>
</body>

</html>