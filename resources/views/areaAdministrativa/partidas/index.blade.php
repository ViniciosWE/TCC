@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Partidas')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="mb-4">
            <h2 class="fw-bold mb-1">Gerenciar Partidas</h2>
        </div>
        <form action="{{ route('partidas.index') }}" method="GET" class="mb-4">
            <div class="row">
                {{-- Campeonato --}}
                <div class="col-12 col-md-8">
                    <label for="campeonato_nome" class="form-label">Campeonato</label>
                    <input list="lista-campeonatos" class="form-control" id="campeonato_nome" name="campeonato_nome"
                        placeholder="Digite para pesquisar..." value="{{ request('campeonato_nome') }}" autocomplete="off">
                    <input type="hidden" name="campeonato_id" id="campeonato_id" value="{{ request('campeonato_id') }}">
                    <datalist id="lista-campeonatos">
                        @foreach ($campeonatos as $campeonato)
                            <option value="{{ $campeonato->nome }}" data-id="{{ $campeonato->id }}">
                            </option>
                        @endforeach
                    </datalist>
                </div>
                {{-- Botão --}}
                <div class="col-12 col-md-4 d-flex align-items-end mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Ver Partidas
                    </button>
                </div>
            </div>
        </form>

        {{-- Mensagem de sucesso --}}
        @if(session('success'))
            <div class="alert alert-success" id="sumirMensagem">
                {{ session('success') }}
            </div>
        @endif
        {{-- Mensagem de erro --}}
        @if(session('error'))
            <div class="alert alert-danger" id="sumirMensagem">
                {{ session('error') }}
            </div>
        @endif
        {{-- Campo de pesquisa --}}
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="pesquisaPartida" placeholder="Pesquisar partida...">
            </div>
        </div>
        {{-- Cards dos partidas --}}
        <div class="row g-4">
            @forelse($partidas as $partida)
                <div class="col-12 partida-card">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body partida-card-body">
                            {{-- Data e Local --}}
                            <div
                                class="d-flex justify-content-center align-items-center flex-wrap gap-2 gap-md-4 text-muted small mb-3 partida-info">

                                <span class="text-nowrap">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ $partida->data_hora ? date('d/m/Y H:i', strtotime($partida->data_hora)) : 'Data não definida'}}
                                </span>
                                <span class="text-nowrap">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $partida->local ?? 'Local não definido' }}
                                </span>
                            </div>
                            <div class="row align-items-center text-center g-0">
                                {{-- Mandante --}}
                                <div class="col-4">
                                    <div
                                        class="d-flex align-items-center justify-content-start partida-equipe partida-mandante">
                                        <img src="{{ asset('storage/' . $partida->mandante->escudo) }}"
                                            alt="Escudo {{ $partida->mandante->nome }}"
                                            class="rounded-circle border partida-escudo flex-shrink-0">
                                        <strong
                                            class="text-capitalize partida-nome text-truncate">{{ $partida->mandante->nome }}</strong>
                                    </div>
                                </div>
                                {{-- Placar e status --}}
                                <div class="col-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <h2 class="fw-bold mb-1 partida-placar">
                                            {{ $partida->gols_mandante }}
                                            <span class="mx-1">
                                                ×
                                            </span>
                                            {{ $partida->gols_visitante }}
                                        </h2>
                                        @if ($partida->status == 'AGENDADA')
                                            <span class="badge bg-primary">Agendada</span>
                                        @elseif ($partida->status == 'PENDENTE')
                                            <span class="badge bg-secondary">Pendente</span>
                                        @elseif ($partida->status == 'FINALIZADA')
                                            <span class="badge bg-success">Finalizada</span>
                                        @endif
                                    </div>
                                </div>
                                {{-- Visitante --}}
                                <div class="col-4">
                                    <div class="d-flex align-items-center justify-content-end partida-equipe partida-visitante">
                                        <strong class="text-capitalize partida-nome text-truncate">
                                            {{ $partida->visitante->nome }}</strong>
                                        <img src="{{ asset('storage/' . $partida->visitante->escudo) }}"
                                            alt="Escudo {{ $partida->visitante->nome }}"
                                            class="rounded-circle border partida-escudo flex-shrink-0">
                                    </div>
                                </div>
                            </div>
                            {{-- Ações da partida --}}
                            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3 partida-acoes">
                                {{-- Definir data, hora e local, aparece caso a partida esta com status pendente --}}
                                @if ($partida->status == 'PENDENTE')
                                    <a href="{{ route('partidas.edit', ['partida' => $partida, 'campeonato_id' => request('campeonato_id'), 'campeonato_nome' => request('campeonato_nome')]) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="bi bi-calendar-plus me-1"></i>Definir data e hora
                                    </a>
                                    {{-- Senão aparece Para editar data e hora e local se já foi agendado --}}
                                @elseif ($partida->status == 'AGENDADA')
                                    <a href="{{ route('partidas.edit', ['partida' => $partida, 'campeonato_id' => request('campeonato_id'), 'campeonato_nome' => request('campeonato_nome')]) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="bi bi-calendar-event me-1"></i>Editar data/local
                                    </a>
                                @endif
                                {{-- Pode cadastrar eventos caso seja com status agendada e pode ver os eventos se
                                estiver com status Finalizada --}}
                                @if ($partida->status != 'PENDENTE')
                                    <a href="{{ route('eventoPartidas.index', ['partida_id' => $partida->id]) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="bi bi-clipboard2-pulse me-1"></i>
                                        {{ $partida->status == 'FINALIZADA' ? 'Ver eventos' : 'Cadastrar eventos'}}
                                    </a>
                                @endif
                                {{-- Pode gerar a sumula quando estiver com status de agendada --}}
                                @if ($partida->status == 'AGENDADA')
                                    <a href="#" class="btn btn-secondary btn-sm">
                                        <i class="bi bi-file-earmark-text me-1"></i>Gerar súmula
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Nenhuma partida encontrada --}}
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Nenhum campeonato selecionado que contenha partidas.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection