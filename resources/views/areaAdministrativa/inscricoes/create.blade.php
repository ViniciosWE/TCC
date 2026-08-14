@extends('areaAdministrativa.sidebar')

@section('title', 'Cadastrar Inscrição')

@section('content')

    <div class="container">
        <h1 class="mb-4">Cadastrar Inscrição</h1>

        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inscricoes.store') }}" method="POST">
            @csrf
            {{-- Campaonato --}}
            <div class="mb-3">
                <label for="campeonato_nome" class="form-label">Selecione um Campaonato</label>
                <input list="lista-campeonatos" class="form-control" id="campeonato_nome"
                    placeholder="Digite para pesquisar..." required>
                <input type="hidden" name="campeonato_id" id="campeonato_id" value="{{ old('campeonato_id') }}">
                <datalist id="lista-campeonatos">
                    @foreach ($campeonatos as $campeonato)
                        <option value="{{ $campeonato->nome }}" data-id="{{ $campeonato->id }}">
                        </option>
                    @endforeach
                </datalist>
            </div>
            {{--Equipe --}}
            <div class="mb-3">
                <label for="equipe_nome" class="form-label">Selecione uma Equipe</label>
                <input list="lista-equipes" class="form-control" id="equipe_nome" placeholder="Digite para pesquisar..."
                    required>
                <input type="hidden" name="equipe_id" id="equipe_id" value="{{ old('equipe_id') }}">
                <datalist id="lista-equipes">
                    @foreach ($equipes as $equipe)
                        <option value="{{ $equipe->nome }}" data-id="{{ $equipe->id }}">
                        </option>
                    @endforeach
                </datalist>
            </div>
            {{--Botões--}}
            <button type="submit" class="btn btn-primary">Cadastrar Inscrição</button>
            <a href="{{ route('inscricoes.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection