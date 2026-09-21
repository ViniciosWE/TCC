@extends('LayoutInicial')

@section('title', 'Página de Notícias')

@section('content')
    <div class="p-4">
        <div class="row g-4">
            @forelse($noticias as $noticia)
                <div class="col-12 col-md-6 col-xl-4 noticia-card">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-body">
                            {{-- Informações da notícia --}}
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
                                <h5 class="fw-bold mb-1 mt-3 text-break descricao-noticia">{{ $noticia->titulo}}</h5>
                                <p class="text-break descricao-noticia">{{ $noticia->descricao }}</p>
                                @if ($noticia->campeonato)
                                    <span class="text-break descricao-noticia">
                                        Esta notícia está relacionada ao <b>{{ $noticia->campeonato->nome }}</b>.
                                    </span>
                                @else
                                    <span class="text-break descricao-noticia">Esta notícia não está relacionada a um
                                        campeonato.</span>
                                @endif
                            </div>
                            {{-- Botões --}}
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('noticiasDetalhes', $noticia) }}" class="btn btn-secondary w-100">
                                    <i class=" bi bi-eye me-1"></i>
                                    Detalhes
                                </a>
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