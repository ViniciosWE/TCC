@extends('areaAdministrativa.sidebar')

@section('title', 'Editar Evento das Partidas')

@section('content')

    <div class="container">
        <h1 class="mb-4">Editar Evento das Partidas</h1>

        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('eventoPartidas.update', $eventoPartida) }}" method="POST">
            @csrf
            @method('PUT')
            {{-- Participante --}}
            <div class="mb-3">
                <label for="participante_nome" class="form-label">Selecione um Participante</label>
                <input list="lista-participantes" class="form-control" id="participante_nome" name="participante_nome"
                    placeholder="Digite para pesquisar..." required
                    value="{{ old('participante_nome', $eventoPartida->participante->nome . ' - ' . $eventoPartida->participante->cpf) }}"
                    autocomplete="off">
                <input type="hidden" name="participante_id" id="participante_id"
                    value="{{ old('participante_id', $eventoPartida->participante_id) }}">
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
                    value="{{ old('tempo', $eventoPartida->tempo) }}" required>
            </div>

            {{-- Tipo --}}
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo do evento</label>
                <select class="form-select" name="tipo" id="tipo">
                    <option value="">Selecione</option>
                    <option value="GOL" {{ old('tipo', $eventoPartida->tipo) == 'GOL' ? 'selected' : '' }}>Gol</option>
                    <option value="CARTAO_AMARELO" {{ old('tipo', $eventoPartida->tipo) == 'CARTAO_AMARELO' ? 'selected' : '' }}>Cartão Amarelo
                    </option>
                    <option value="CARTAO_VERMELHO" {{ old('tipo', $eventoPartida->tipo) == 'CARTAO_VERMELHO' ? 'selected' : '' }}>Cartão Vermelho
                    </option>
                    <option value="ASSISTENCIA" {{ old('tipo', $eventoPartida->tipo) == 'ASSISTENCIA' ? 'selected' : '' }}>
                        Assistência</option>
                    <option value="GOL_CONTRA" {{ old('tipo', $eventoPartida->tipo) == 'GOL_CONTRA' ? 'selected' : '' }}>Gol
                        Contra</option>
                    <option value="GOLS_SOFRIDOS" {{ old('tipo', $eventoPartida->tipo) == 'GOLS_SOFRIDOS' ? 'selected' : '' }}>Gols Sofridos
                    </option>
                </select>
            </div>
            {{--Botões--}}
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="{{ route('eventoPartidas.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection