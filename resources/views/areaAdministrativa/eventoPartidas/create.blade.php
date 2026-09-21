@extends('areaAdministrativa.sidebar')

@section('title', 'Cadastrar Evento Partida')

@section('content')

    <div class="container">
        <h1 class="mb-4">Cadastrar Evento Partida</h1>
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
        {{-- Mensagem de sucesso --}}
        @if(session('success'))
            <div class="alert alert-success" id="sumirMensagem">
                {{ session('success') }}
            </div>
        @endif
        {{-- Informações da partida --}}
        <div class="alert alert-info">
            <strong>Partida:</strong>
            {{ $partida->mandante->nome }}×{{ $partida->visitante->nome }}
        </div>
        {{-- Situação dos titulares --}}
        <div class="alert alert-secondary">
            <strong>Titulares:</strong>
            {{ $partida->mandante->nome }}:{{ $titularesMandante }}/5 |
            {{ $partida->visitante->nome }}:{{ $titularesVisitante }}/5
        </div>
        {{-- Aviso enquanto não estiver 5x5 --}}
        @if (!$titularesCompletos)
            <div class="alert alert-warning">
                Cadastre os 5 jogadores titulares de cada equipe
                antes de cadastrar outros eventos.
            </div>
        @endif
        {{-- Formulário --}}
        <form action="{{ route('eventoPartidas.store') }}" method="POST">
            @csrf
            {{-- ID da partida --}}
            <input type="hidden" name="partida_id" value="{{ $partida->id }}">
            {{-- Mantém o campeonato pesquisado --}}
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
                        <option value="{{ $participante->equipe_nome }} - {{ $participante->nome }} - {{ $participante->cpf }}"
                            data-id="{{ $participante->id }}"></option>
                    @endforeach
                </datalist>
            </div>
            {{-- Tempo --}}
            <div class="mb-3">
                <label for="tempo" class="form-label">Tempo</label>
                <input type="time" class="form-control" id="tempo" name="tempo" step="1"
                    value="{{ old('tempo', '00:00:00') }}" required>
            </div>
            {{-- Tipo --}}
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo do evento</label>
                <select class="form-select" name="tipo" id="tipo" required>
                    <option value="">Selecione</option>
                    {{-- antes do 5x5 --}}
                    @if (!$titularesCompletos)
                        <option value="TITULAR" {{ old('tipo') == 'TITULAR' ? 'selected' : '' }}>Titular</option>
                    @endif
                    {{-- depois do 5x5 --}}
                    @if ($titularesCompletos)
                        @if (!$disputaPenaltisIniciada)
                            <option value="GOL" {{ old('tipo') == 'GOL' ? 'selected' : '' }}>Gol</option>
                            <option value="ASSISTENCIA" {{ old('tipo') == 'ASSISTENCIA' ? 'selected' : '' }}>Assistência</option>
                            <option value="GOL_CONTRA" {{ old('tipo') == 'GOL_CONTRA' ? 'selected' : '' }}>Gol Contra</option>
                        @endif
                        <option value="ENTRADA_GOLEIRO" {{ old('tipo') == 'ENTRADA_GOLEIRO' ? 'selected' : '' }}>Entrada de
                            Goleiro</option>
                        <option value="CARTAO_AMARELO" {{ old('tipo') == 'CARTAO_AMARELO' ? 'selected' : '' }}>Cartão Amarelo
                        </option>
                        <option value="CARTAO_VERMELHO" {{ old('tipo') == 'CARTAO_VERMELHO' ? 'selected' : '' }}>Cartão Vermelho
                        </option>
                        @if ($penaltiDesempateDisponivel)
                            <option value="PENALTI_CONVERTIDO_DESEMPATE" {{ old('tipo') == 'PENALTI_CONVERTIDO_DESEMPATE' ? 'selected' : '' }}>Pênalti Convertido de Desempate</option>
                        @endif
                    @endif
                </select>
            </div>
            {{-- Botões --}}
            <button type="submit" class="btn btn-primary">Cadastrar Evento</button>
            <a href="{{ route('partidas.index', ['campeonato_id' => request('campeonato_id'), 'campeonato_nome' => request('campeonato_nome')]) }}"
                class="btn btn-secondary">Voltar</a>
        </form>
    </div>
@endsection