@extends('areaAdministrativa.sidebar')

@section('title', 'Definir data e hora')

@section('content')
    <div class="container">
        <h1 class="mb-4">Definir data e hora da partida</h1>
        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('partidas.update', $partida) }}" method="POST">
            @csrf
            @method('PUT')
            {{-- Mantém o campeonato pesquisado após salvar --}}
            <input type="hidden" name="campeonato_id" value="{{ request('campeonato_id') }}">
            <input type="hidden" name="campeonato_nome" value="{{ request('campeonato_nome') }}">
            {{--Data--}}
            <div class="mb-3">
                <label for="nome" class="form-label">Data</label>
                <input type="date" class="form-control" id="data" name="data"
                    value="{{ old('data', $partida->data_hora ? date('Y-m-d', strtotime($partida->data_hora)) : '') }}"
                    required>
            </div>
            {{-- Hora --}}
            <div class="mb-3">
                <label for="cpf" class="form-label">Hora</label>
                <input type="time" class="form-control" id="hora" name="hora"
                    value="{{ old('hora', $partida->data_hora ? date('H:i', strtotime($partida->data_hora)) : '') }}"
                    required>
            </div>

            {{-- Local --}}
            <div class="mb-3">
                <label for="cpf" class="form-label">Local</label>
                <input type="text" class="form-control" id="local" name="local" placeholder="Ex.: Ginásio Municipal"
                    value="{{ old('local', $partida->local) }}" required>
            </div>
            {{-- Botões --}}
            <button type="submit" class="btn btn-primary">Salvar Alterações </button>
              {{-- Redireciona para a tela de partidas, enviando o ID e o nome do campeonato --}}
            <a href="{{ route('partidas.index', ['campeonato_id' => request('campeonato_id'), 'campeonato_nome' => request('campeonato_nome')]) }}"
                class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection