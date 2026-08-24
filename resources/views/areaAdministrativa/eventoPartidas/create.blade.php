@extends('areaAdministrativa.sidebar')

@section('title', 'Cadastrar Evento Partida')

@section('content')

    <div class="container">
        <h1 class="mb-4">Cadastrar Evento Partida</h1>

        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-info">
            <strong>Partida:</strong>
            {{ $partida->mandante->nome }}
            ×
            {{ $partida->visitante->nome }}
        </div>
        <form action="{{ route('eventoPartidas.store') }}" method="POST">
            @csrf
            {{-- Manda o id da partida--}}
            <input type="hidden" name="partida_id" value="{{ $partida->id }}">
            {{-- Mantém o campeonato pesquisado após salvar --}}
            <input type="hidden" name="campeonato_id" value="{{ request('campeonato_id') }}">
            <input type="hidden" name="campeonato_nome" value="{{ request('campeonato_nome') }}">
            {{-- Participante --}}
            <div class="mb-3">
                <label for="participante_nome" class="form-label">Selecione um Participante</label>
                <input list="lista-participantes" class="form-control" id="participante_nome" name="participante_nome"
                    placeholder="Digite para pesquisar..." required value="{{ old('participante_nome') }}"
                    autocomplete="off">
                <input type="hidden" name="participante_id" id="participante_id" value="{{ old('participante_id') }}">
                <datalist id="lista-participantes">
                    @foreach ($participantes as $participante)
                        <option value="{{ $participante->nome }} - {{ $participante->cpf }}" data-id="{{ $participante->id }}">
                        </option>
                    @endforeach
                </datalist>
            </div>
            {{-- tempo --}}
            <div class="mb-3">
                <label for="tempo" class="form-label">Tempo</label>
                <input type="time" class="form-control" id="tempo" name="tempo" step="1"
                    value="{{ old('tempo', '00:00:00') }}" required>
            </div>

            {{-- Tipo --}}
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo do evento</label>
                <select class="form-select" name="tipo" id="tipo">
                    <option value="">Selecione</option>
                    <option value="GOL" {{ old('tipo') == 'GOL' ? 'selected' : '' }}>Gol</option>
                    <option value="CARTAO_AMARELO" {{ old('tipo') == 'CARTAO_AMARELO' ? 'selected' : '' }}>Cartão Amarelo
                    </option>
                    <option value="CARTAO_VERMELHO" {{ old('tipo') == 'CARTAO_VERMELHO' ? 'selected' : '' }}>Cartão Vermelho
                    </option>
                    <option value="ASSISTENCIA" {{ old('tipo') == 'ASSISTENCIA' ? 'selected' : '' }}>Assistência</option>
                    <option value="GOL_CONTRA" {{ old('tipo') == 'GOL_CONTRA' ? 'selected' : '' }}>Gol Contra</option>
                    <option value="GOLS_SOFRIDOS" {{ old('tipo') == 'GOLS_SOFRIDOS' ? 'selected' : '' }}>Gols Sofridos
                    </option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Cadastrar Equipe</button>
            {{-- Redireciona para a tela de partidas, enviando o ID e o nome do campeonato --}}
            <a href="{{ route('partidas.index', ['campeonato_id' => request('campeonato_id'), 'campeonato_nome' => request('campeonato_nome')]) }}"
                class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection