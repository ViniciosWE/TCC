@extends('areaAdministrativa.sidebar')

@section('title', 'Editar Evento das Partidas')

@section('content')

    <div class="container">
        <h1 class="mb-4">Editar Evento das Partidas</h1>
        {{-- Mensagens de erro --}}
        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{-- Informações da partida --}}
        <div class="alert alert-info">
            <strong>Partida:</strong>
            {{ $partida->mandante->nome }} × {{ $partida->visitante->nome }}
        </div>
        <form action="{{ route('eventoPartidas.update', $eventoPartida) }}" method="POST">
            @csrf
            @method('PUT')
            {{-- Participante --}}
            <div class="mb-3">
                <label for="participante_nome" class="form-label">Selecione um Participante</label>
                <input list="lista-participantes" class="form-control" id="participante_nome" name="participante_nome"
                    placeholder="Digite para pesquisar..." required
                    value="{{ old('participante_nome', $eventoPartida->participante->nome . ' - ' . $eventoPartida->participante->cpf . ' - ' . $eventoPartida->participante->funcao) }}"
                    autocomplete="off">
                <input type="hidden" name="participante_id" id="participante_id"
                    value="{{ old('participante_id', $eventoPartida->participante_id) }}">
                <datalist id="lista-participantes">
                    @foreach ($participantes as $participante)
                        <option value="{{ $participante->nome }} - {{ $participante->cpf }} - {{ $participante->funcao }}"
                            data-id="{{ $participante->id }}"></option>
                    @endforeach
                </datalist>
            </div>
            {{-- Tempo --}}
            <div class="mb-3">
                <label for="tempo" class="form-label">Tempo</label>
                <input type="time" class="form-control" id="tempo" name="tempo" step="1"
                    value="{{ old('tempo', $eventoPartida->tempo) }}" required>
            </div>
            {{-- Tipo --}}
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo do evento</label>
                <select class="form-select" name="tipo" id="tipo" required>
                    <option value="">Selecione</option>
                    {{-- Titular --}}
                    @if ($eventoPartida->tipo === 'TITULAR')
                        <option value="TITULAR" selected>Titular</option>
                    @endif
                    {{-- eventos normais --}}
                    @if ($eventoPartida->tipo !== 'TITULAR')
                        <option value="ENTRADA_GOLEIRO" {{ old('tipo', $eventoPartida->tipo) == 'ENTRADA_GOLEIRO' ? 'selected' : '' }}>Entrada de Goleiro</option>
                        <option value="GOL" {{ old('tipo', $eventoPartida->tipo) == 'GOL' ? 'selected' : '' }}>Gol</option>
                        <option value="CARTAO_AMARELO" {{ old('tipo', $eventoPartida->tipo) == 'CARTAO_AMARELO' ? 'selected' : '' }}>Cartão Amarelo</option>
                        <option value="CARTAO_VERMELHO" {{ old('tipo', $eventoPartida->tipo) == 'CARTAO_VERMELHO' ? 'selected' : '' }}>Cartão Vermelho</option>
                        <option value="ASSISTENCIA" {{ old('tipo', $eventoPartida->tipo) == 'ASSISTENCIA' ? 'selected' : '' }}>
                            Assistência</option>
                        <option value="GOL_CONTRA" {{ old('tipo', $eventoPartida->tipo) == 'GOL_CONTRA' ? 'selected' : '' }}>Gol
                            Contra</option>
                        <option value="PENALTI_CONVERTIDO_DESEMPATE" {{ old('tipo', $eventoPartida->tipo) == 'PENALTI_CONVERTIDO_DESEMPATE' ? 'selected' : '' }}>Pênalti Convertido de
                            Desempate</option>
                    @endif
                </select>
            </div>
            {{-- Botões --}}
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="{{ route('eventoPartidas.index') }}" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
@endsection