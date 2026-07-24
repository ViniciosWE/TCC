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
    <header class="cor-fundo-header-sidebar">
        {{--navbar do Bootstrap responsável pela navegação do site--}}
        <nav class="navbar navbar-expand-lg ">
            {{-- container responsivo ocupando toda a largura disponível, com espaçamento lateral usando px-4--}}
            <div class="container-fluid px-4">
                {{--nome do site que recebe o link da página incial--}}
                <a class="navbar-brand fw-bold text-uppercase text-white fs-1" href="{{ route('PaginaInicial') }}">
                    Esporte <span class="textos-navegacao-inicial">Total</span>
                </a>
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>