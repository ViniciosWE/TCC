@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Notícias')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gerenciar Notícias</h2>
            </div>
            <a href="{{ route('noticias.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nova Notícia
            </a>
        </div>
        {{-- Mensagem de sucesso --}}
        @if(session('success'))
            <div class="alert alert-success" id="sumirMensagem">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" id="sumirMensagem">
                {{ session('error') }}
            </div>
        @endif

        {{-- Campo de pesquisa --}}
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="pesquisaNoticia" placeholder="Pesquisar notícia...">
            </div>
        </div>
        {{-- Cards das equipes --}}
        <div class="row g-4">
            @forelse($noticias as $noticia)
                <div class="col-12 col-md-6 col-xl-4 noticia-card">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-body">
                            {{-- Informações da equipe --}}
                            <div class="mb-3">
                                <div class="text-center text-break mx-auto">
                                    <div class="ratio ratio-1x1">
                                        @if ($noticia->imagem)
                                            <img src="{{ asset('storage/' . $noticia->imagem) }}"
                                                alt="Imagem Notícia {{ $noticia->titulo }}"
                                                class="img-fluid rounded object-fit-cover">
                                        @else
                                            <div
                                                class="text-muted d-flex flex-column justify-content-center align-items-center border rounded">
                                                <i class="bi bi-image fs-1 mb-3"></i>
                                                <span>Notícia sem imagem</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-1 mt-3">{{ $noticia->titulo}}</h5>
                                <p>{{ $noticia->descricao }}</p>
                                @if ($noticia->campeonato)
                                    <span class="text-muted">
                                        Esta notícia está relacionada ao <b>{{ $noticia->campeonato->nome }}</b>.
                                    </span>
                                @endif
                            </div>
                            {{-- Botões --}}
                            <div class="d-flex gap-2">
                                <a href="{{ route('noticias.edit', $noticia) }}" class="btn btn-warning flex-fill">
                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                </a>
                                <form action="{{ route('noticias.destroy', $noticia) }}" method="POST" class="flex-fill">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Deseja realmente excluir esta equipe?')">
                                        <i class="bi bi-trash me-1"></i>Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Mensagem exibida quando não houver nenhuma notícia cadastrada --}}
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Nenhuma Notícia cadastrada.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection