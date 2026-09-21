@extends('LayoutInicial')

@section('title', 'Notícias completa')

@section('content')
    <div class="p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div class="border-start border-primary border-5 ps-2 mb-3">
                <h4 class="fw-bold mb-1 text-uppercase">Notícia Completa</h4>
            </div>
            <a href="{{ route('PaginaNoticias') }}" class="btn btn-secondary"></i>Voltar</a>
        </div>
        {{-- Noticia completa --}}
        <div class="p-2">
            {{-- título --}}
            <h5 class="text-break">{{ $noticia->titulo }}</h5>
            {{-- Imagem --}}
            <div class="row g-4 mb-4">
                @if ($noticia->imagem)
                    <img src="{{ asset('storage/' . $noticia->imagem) }}" alt="Imagem Notícia {{ $noticia->titulo }}"
                        class="img-fluid rounded object-fit-cover">
                @else
                    <div class="text-muted d-flex flex-column justify-content-center align-items-center border rounded">
                        <i class="bi bi-image fs-1 mb-3"></i>
                        <span>Notícia sem imagem</span>
                    </div>
                @endif
            </div>
            {{-- descrição --}}
            <p class="text-break mb-2">{{ $noticia->descricao }}</p>
            {{-- relação --}}
            @if ($noticia->campeonato)
                <span class="text-break">
                    Esta notícia está relacionada ao <b>{{ $noticia->campeonato->nome }}</b>.
                </span>
            @else
                <span class="text-break">Esta notícia não está relacionada a um
                    campeonato.</span>
            @endif
        </div>
    </div>
@endsection