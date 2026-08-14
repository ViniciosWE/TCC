@extends('areaAdministrativa.sidebar')

@section('title', 'Editar Notícia')

@section('content')
    <div class="container">
        <h1 class="mb-4">Editar Notícia</h1>
        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('noticias.update', $noticia) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            {{-- título --}}
            <div class="mb-3">
                <label for="titulo" class="form-label">Título da Notícia</label>
                <input type="text" class="form-control" id="titulo" name="titulo"
                    value="{{ old('titulo', $noticia->titulo) }}" required>
            </div>
            {{-- Descrição --}}
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao"
                    required>{{ old('descricao', $noticia->descricao) }}</textarea>
            </div>
            {{-- Imagem atual --}}
            <div class="mb-3">
                <label class="form-label">Imagem atual</label>
                <br>
                <img src="{{ asset('storage/' . $noticia->imagem) }}" alt="imagem da notícia {{ $noticia->titulo }}"
                    width="80" height="80">
            </div>
            {{-- Imagem --}}
            <div class="mb-3">
                <label for="imagem" class="form-label">Alterar Imagem</label>
                <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
                <small class="text-muted">Deixe vazio caso não queira alterar o escudo.</small>
            </div>
            {{-- Campaonato --}}
            <div class="mb-3">
                <label for="campeonato_nome" class="form-label">Selecione um Campaonato(Se a notícia houver relação)</label>
                <input list="lista-campeonatos" class="form-control" id="campeonato_nome"
                    placeholder="Digite para pesquisar..."
                    value=" {{ old('campeonato_id') ? $campeonatos->firstWhere('id', old('campeonato_id'))?->nome : $noticia->campeonato->nome }}">
                <input type="hidden" name="campeonato_id" id="campeonato_id"
                    value="{{ old('campeonato_id', $noticia->campeonato_id) }}">
                <datalist id="lista-campeonatos">
                    @foreach ($campeonatos as $campeonato)
                        <option value="{{ $campeonato->nome }}" data-id="{{ $campeonato->id }}">
                        </option>
                    @endforeach
                </datalist>
            </div>
            {{-- Botões --}}
            <button type="submit" class="btn btn-primary">Salvar Alterações </button>
            <a href="{{ route('noticias.index') }}" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
@endsection