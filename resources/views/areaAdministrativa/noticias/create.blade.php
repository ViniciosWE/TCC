@extends('areaAdministrativa.sidebar')

@section('title', 'Cadastrar Notícia')

@section('content')

    <div class="container">
        <h1 class="mb-4">Cadastrar Notícia</h1>

        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('noticias.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- título --}}
            <div class="mb-3">
                <label for="titulo" class="form-label">Título da Notícia</label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="{{ old('titulo') }}" required>
            </div>
            {{-- Descrição --}}
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" required>{{ old('descricao') }}</textarea>
            </div>
            {{-- Imagem --}}
            <div class="mb-3">
                <label for="imagem" class="form-label">Imagem</label>
                <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
            </div>
            {{-- Campaonato --}}
            <div class="mb-3">
                <label for="campeonato_nome" class="form-label">Selecione um Campaonato(Se a notícia houver relação)</label>
                <input list="lista-campeonatos" class="form-control" id="campeonato_nome" name="campeonato_nome"
                    placeholder="Digite para pesquisar..." value="{{ old('campeonato_nome') }}">
                <input type="hidden" name="campeonato_id" id="campeonato_id" value="{{ old('campeonato_id') }}">
                <datalist id="lista-campeonatos">
                    @foreach ($campeonatos as $campeonato)
                        <option value="{{ $campeonato->nome }}" data-id="{{ $campeonato->id }}">
                        </option>
                    @endforeach
                </datalist>
            </div>
            {{--Botões --}}
            <button type="submit" class="btn btn-primary">Cadastrar Notícia</button>
            <a href="{{ route('noticias.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection