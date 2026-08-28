@extends('areaAdministrativa.sidebar')

@section('title', 'Dashboard')

@section('content')

    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Dashboard</h2>
            </div>
            {{-- Voltar para a página inicial sem deslogar --}}
            <a href="{{ route('PaginaInicial') }}" class="btn btn-primary">
                <i class="bi bi-house me-2"></i>Página Inicial
            </a>
        </div>
        {{-- cards --}}
        <div class="row g-3">
            @if(auth()->user()->tipo === 'SUPER_ADMINISTRADOR')
                <div class="col-12 col-md-4">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <div class="bg-primary text-white rounded-4 d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-uppercase small"> USUÁRIOS</div>
                                <div class="fs-2 fw-bold lh-1">{{ $totalUsuarios }}</div>
                                <div class="text-primary fw-semibold small">CADASTRADOS</div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif (auth()->user()->tipo === 'ADMINISTRADOR')
                    {{-- Campeonatos --}}
                    <div class="col-12 col-md-4">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-body d-flex align-items-center gap-3 p-3">
                                <div class="bg-primary text-white rounded-4 d-flex align-items-center justify-content-center"
                                    style="width: 60px; height: 60px;">
                                    <i class="bi bi-trophy-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-uppercase small">CAMPEONATOS</div>
                                    <div class="fs-2 fw-bold lh-1">{{ $totalCampeonatosEmAndamento }}</div>
                                    <div class="text-primary fw-semibold small">EM ANDAMENTO</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Equipes --}}
                    <div class="col-12 col-md-4">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-body d-flex align-items-center gap-3 p-3">
                                <div class="bg-success text-white rounded-4 d-flex align-items-center justify-content-center"
                                    style="width: 60px; height: 60px;">
                                    <i class="bi bi-people-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-uppercase small">EQUIPES</div>
                                    <div class="fs-2 fw-bold lh-1">{{ $equipesCadastradas }}</div>
                                    <div class="text-success fw-semibold small">CADASTRADAS</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Atletas --}}
                    <div class="col-12 col-md-4">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-body d-flex align-items-center gap-3 p-3">

                                <div class="bg-danger text-white rounded-4 d-flex align-items-center justify-content-center"
                                    style="width: 60px; height: 60px;">
                                    <i class="bi bi-person-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-uppercase small">ATLETAS</div>
                                    <div class="fs-2 fw-bold lh-1">{{ $participantesCadastrados }}</div>
                                    <div class="text-danger fw-semibold small">CADASTRADOS</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Partidas de hoje --}}
                <div class="mt-4">
                    <div class="mb-3">
                        <div class="border-start border-primary border-5 ps-2 mb-3">
                            <h4 class="fw-bold mb-1">Partidas de hoje</h4>
                        </div>
                        <small class="text-muted">{{ now()->format('d/m/Y') }}</small>
                    </div>
                    <div class="row g-3">
                        @forelse ($partidasHoje as $partida)
                            <div class="col-12 partida-card">
                                <div class="card border-0 rounded-4 shadow-sm">
                                    <div class="card-body partida-card-body">
                                        {{-- Campeonato --}}
                                        <div class="text-center mb-2">
                                            <span class="fw-bold">{{ $partida->campeonato->nome }}</span>
                                        </div>
                                        {{-- Data e local --}}
                                        <div class="d-flex justify-content-center flex-wrap gap-3 text-muted small mb-3 partida-info">
                                            <span>
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $partida->data_hora ? date('H:i', strtotime($partida->data_hora)) : 'Horário não definido' }}
                                            </span>
                                            <span>
                                                <i class="bi bi-geo-alt me-1"></i>{{ $partida->local ?? 'Local não definido' }}
                                            </span>
                                        </div>
                                        {{-- Equipes --}}
                                        <div class="row align-items-center text-center g-0">
                                            {{-- Mandante --}}
                                            <div class="col-4">
                                                <div
                                                    class="d-flex align-items-center justify-content-start partida-equipe partida-mandante">
                                                    <img src="{{ asset('storage/' . $partida->mandante->escudo) }}"
                                                        alt="Escudo {{ $partida->mandante->nome }}"
                                                        class="rounded-circle border partida-escudo flex-shrink-0">
                                                    <strong class="partida-nome text-truncate"> {{ $partida->mandante->nome }}</strong>
                                                </div>
                                            </div>
                                            {{-- Placar --}}
                                            <div class="col-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h2 class="fw-bold mb-1 partida-placar">
                                                        {{ $partida->gols_mandante }}
                                                        <span class="mx-1">
                                                            ×
                                                        </span>
                                                        {{ $partida->gols_visitante }}
                                                    </h2>
                                                    @if ($partida->penaltisMandante > 0 || $partida->penaltisVisitante > 0)
                                                        <small class="text-muted small">{{ $partida->penaltisMandante }} ×
                                                            {{ $partida->penaltisVisitante }}</small>
                                                    @endif
                                                    @if ($partida->status == 'AGENDADA')
                                                        <span class="badge bg-primary">Agendada </span>
                                                    @elseif ($partida->status == 'FINALIZADA')
                                                        <span class="badge bg-success">Finalizada</span>
                                                    @endif
                                                </div>
                                            </div>
                                            {{-- Visitante --}}
                                            <div class="col-4">
                                                <div
                                                    class="d-flex align-items-center justify-content-end partida-equipe partida-visitante">
                                                    <strong class="partida-nome text-truncate">{{ $partida->visitante->nome }}</strong>
                                                    <img src="{{ asset('storage/' . $partida->visitante->escudo) }}"
                                                        alt="Escudo {{ $partida->visitante->nome }}"
                                                        class="rounded-circle border partida-escudo flex-shrink-0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    Nenhuma partida de campeonatos em andamento para hoje.
                                </div>
                            </div>
                        @endforelse
                    </div>
            @endif
        </div>
    </div>
@endsection