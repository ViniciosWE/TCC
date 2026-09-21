@extends('LayoutInicial')

@section('title', 'Partidas do Campeonato')

@section('content')
<div class="p-4">
    {{-- Cabeçalho --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div class="border-start border-primary border-5 ps-2 mb-3">
            <h4 class="fw-bold mb-1 text-uppercase">Partidas do campeonato</h4>
        </div>
        <a href="{{ route('PaginaCampeonatos') }}" class="btn btn-secondary">Voltar</a>
    </div>
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
                                <span class="badge bg-light text-dark border mb-2 small">
                                    {{ str_replace('_', ' ', $partida->fase) }}
                                </span>
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
                                    <span class="badge bg-primary">Agendada</span>
                                @elseif ($partida->status == 'PENDENTE')
                                    <span class="badge bg-secondary">Pendente</span>
                                @elseif ($partida->status == 'FINALIZADA')
                                    <span class="badge bg-success">Finalizada</span>
                                @elseif ($partida->status == 'WO')
                                    <span class="badge bg-warning text-dark">WO</span>
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
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection