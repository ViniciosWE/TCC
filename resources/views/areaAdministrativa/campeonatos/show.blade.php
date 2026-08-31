@extends('areaAdministrativa.sidebar')

@section('title', 'Estatísticas do Campeonato')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Estatísticas do Campeonato</h2>
            </div>
            <a href="{{ route('campeonatos.index') }}" class="btn btn-secondary"></i>Voltar</a>
        </div>
        {{-- classificação pontos corridos e triangular --}}
        @if ($campeonato->tipo === 'PONTOS_CORRIDOS' || ($campeonato->tipo === 'MATA_MATA' && $temTriangular))
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">
                        @if ($campeonato->tipo === 'MATA_MATA' && $temTriangular)
                            Classificação da Triangular
                        @else
                            Classificação
                        @endif
                    </h4>
                    <div class="w-100 overflow-hidden">
                        <table class="table table-hover align-middle text-center table-sm small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="text-start">Equipe</th>
                                    <th>J</th>
                                    <th>V</th>
                                    <th>E</th>
                                    <th>D</th>
                                    <th>SG</th>
                                    <th>PTS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($classificacao as $item)
                                    <tr>
                                        <td class="fw-bold">{{ $item['posicao'] }}º</td>
                                        <td class="text-start fw-semibold text-break text-capitalize">{{ $item['equipe']->nome }}
                                        </td>
                                        <td>{{ $item['jogos'] }}</td>
                                        <td>{{ $item['vitorias'] }}</td>
                                        <td>{{ $item['empates'] }}</td>
                                        <td>{{ $item['derrotas'] }}</td>
                                        <td class="fw-semibold">{{ $item['saldo'] >= 0 ? '+' : '' }}{{ $item['saldo'] }}</td>
                                        <td class="fw-bold">{{ $item['pontos'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-muted py-4">Nenhuma partida finalizada ainda</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        {{-- classificação mata-mata --}}
        @if ($campeonato->tipo === 'MATA_MATA' && !$temTriangular)
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Classificação</h4>
                    <div class="row g-3">
                        @foreach ($classificacaoMataMata as $item)
                            <div class="col-12 col-md-4">
                                <div class="card border-0 shadow-sm rounded-4 h-100">
                                    <div class="card-body text-center">
                                        @if ($item['posicao'] == 1)
                                            <h5 class="fw-bold mb-3">1º Lugar</h5>
                                        @elseif ($item['posicao'] == 2)
                                            <h5 class="fw-bold mb-3">2º Lugar</h5>
                                        @elseif ($item['posicao'] == 3)
                                            <h5 class="fw-bold mb-3">3º Lugar</h5>
                                        @endif
                                        <img src="{{ asset('storage/' . $item['equipe']->escudo) }}"
                                            alt="Escudo {{ $item['equipe']->nome }}" width="70" height="70"
                                            class="rounded-circle border p-1 mb-2">
                                        <div class="fw-semibold text-capitalize">
                                            {{ $item['equipe']->nome }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            {{-- semifinais --}}
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Semifinais</h4>
                    <div class="row g-3">
                        @forelse ($semifinais as $partida)
                            {{-- Só ficam lado a lado em telas grandes --}}
                            <div class="col-12 col-xxl-6">
                                <div class="card border shadow-sm rounded-4 h-100">
                                    <div class="card-body">
                                        {{-- Data e Local --}}
                                        <div
                                            class="d-flex justify-content-center align-items-center flex-wrap gap-2 gap-md-4 text-muted small mb-3 partida-info">
                                            <span class="text-nowrap">
                                                <i
                                                    class="bi bi-calendar3 me-1"></i>{{ $partida->data_hora ? date('d/m/Y H\:i', strtotime($partida->data_hora)) : 'Data não definida' }}
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
                                                    {{-- Placar --}}
                                                    <h2 class="fw-bold mb-1 partida-placar">
                                                        {{ $partida->gols_mandante }}
                                                        <span class="mx-1">
                                                            ×
                                                        </span>
                                                        {{ $partida->gols_visitante }}
                                                    </h2>
                                                    {{-- Pênaltis --}}
                                                    @if ($partida->penaltisMandante > 0 || $partida->penaltisVisitante > 0)
                                                        <small class="text-muted small">
                                                            {{ $partida->penaltisMandante }}
                                                            ×
                                                            {{ $partida->penaltisVisitante }}
                                                        </small>
                                                    @endif
                                                    {{-- Status --}}
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
                                                <div
                                                    class="d-flex align-items-center justify-content-end partida-equipe partida-visitante">
                                                    <strong
                                                        class="text-capitalize partida-nome text-truncate">{{ $partida->visitante->nome }}</strong>
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
                                <div class="alert alert-info mb-0">Nenhuma semifinal encontrada.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            {{-- final --}}
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Final</h4>
                    @if ($final)
                        <div class="card border shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                {{-- Data e Local --}}
                                <div
                                    class="d-flex justify-content-center align-items-center flex-wrap gap-2 gap-md-4 text-muted small mb-3 partida-info">
                                    <span class="text-nowrap">
                                        <i
                                            class="bi bi-calendar3 me-1"></i>{{ $final->data_hora ? date('d/m/Y H\:i', strtotime($final->data_hora)) : 'Data não definida' }}
                                    </span>
                                    <span class="text-nowrap">
                                        <i class="bi bi-geo-alt me-1"></i> {{ $final->local ?? 'Local não definido' }}
                                    </span>
                                </div>
                                {{-- Equipes e Placar --}}
                                <div class="row align-items-center text-center g-0">
                                    {{-- Mandante --}}
                                    <div class="col-4">
                                        <div
                                            class="d-flex align-items-center justify-content-start partida-equipe partida-mandante">
                                            <img src="{{ asset('storage/' . $final->mandante->escudo) }}"
                                                alt="Escudo {{ $final->mandante->nome }}"
                                                class="rounded-circle border partida-escudo flex-shrink-0">
                                            <strong class="text-capitalize partida-nome text-truncate">
                                                {{ $final->mandante->nome }}</strong>
                                        </div>
                                    </div>
                                    {{-- Placar e Status --}}
                                    <div class="col-4">
                                        <div class="d-flex flex-column align-items-center">
                                            {{-- Placar --}}
                                            <h2 class="fw-bold mb-1 partida-placar">
                                                {{ $final->gols_mandante }}
                                                <span class="mx-1">
                                                    ×
                                                </span>
                                                {{ $final->gols_visitante }}
                                            </h2>
                                            {{-- Pênaltis --}}
                                            @if ($final->penaltisMandante > 0 || $final->penaltisVisitante > 0)
                                                <small class="text-muted small">
                                                    {{ $final->penaltisMandante }}
                                                    ×
                                                    {{ $final->penaltisVisitante }}
                                                </small>
                                            @endif
                                            {{-- Status --}}
                                            @if ($final->status == 'AGENDADA')
                                                <span class="badge bg-primary">Agendada</span>
                                            @elseif ($final->status == 'PENDENTE')
                                                <span class="badge bg-secondary">Pendente</span>
                                            @elseif ($final->status == 'FINALIZADA')
                                                <span class="badge bg-success">Finalizada</span>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- Visitante --}}
                                    <div class="col-4">
                                        <div class="d-flex align-items-center justify-content-end partida-equipe partida-visitante">
                                            <strong
                                                class="text-capitalize partida-nome text-truncate">{{ $final->visitante->nome }}</strong>
                                            <img src="{{ asset('storage/' . $final->visitante->escudo) }}"
                                                alt="Escudo {{ $final->visitante->nome }}"
                                                class="rounded-circle border partida-escudo flex-shrink-0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">A final ainda não foi finalizada.</div>
                    @endif
                </div>
            </div>
            {{-- 3º lugar --}}
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Disputa de 3º lugar</h4>
                    @if ($terceiroLugar)
                        <div class="card border shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                {{-- Data e Local --}}
                                <div
                                    class="d-flex justify-content-center align-items-center flex-wrap gap-2 gap-md-4 text-muted small mb-3 partida-info">
                                    <span class="text-nowrap">
                                        <i
                                            class="bi bi-calendar3 me-1"></i>{{ $terceiroLugar->data_hora ? date('d/m/Y H\:i', strtotime($terceiroLugar->data_hora)) : 'Data não definida' }}
                                    </span>
                                    <span class="text-nowrap">
                                        <i class="bi bi-geo-alt me-1"></i> {{ $terceiroLugar->local ?? 'Local não definido' }}
                                    </span>
                                </div>
                                {{-- Equipes e Placar --}}
                                <div class="row align-items-center text-center g-0">
                                    {{-- Mandante --}}
                                    <div class="col-4">
                                        <div
                                            class="d-flex align-items-center justify-content-start partida-equipe partida-mandante">
                                            <img src="{{ asset('storage/' . $terceiroLugar->mandante->escudo) }}"
                                                alt="Escudo {{ $terceiroLugar->mandante->nome }}"
                                                class="rounded-circle border partida-escudo flex-shrink-0">
                                            <strong
                                                class="text-capitalize partida-nome text-truncate">{{ $terceiroLugar->mandante->nome }}</strong>
                                        </div>
                                    </div>
                                    {{-- Placar e Status --}}
                                    <div class="col-4">
                                        <div class="d-flex flex-column align-items-center">
                                            {{-- Placar --}}
                                            <h2 class="fw-bold mb-1 partida-placar">
                                                {{ $terceiroLugar->gols_mandante }}
                                                <span class="mx-1">
                                                    ×
                                                </span>
                                                {{ $terceiroLugar->gols_visitante }}
                                            </h2>
                                            {{-- Pênaltis --}}
                                            @if ($terceiroLugar->penaltisMandante > 0 || $terceiroLugar->penaltisVisitante > 0)
                                                <small class="text-muted small">
                                                    {{ $terceiroLugar->penaltisMandante }}
                                                    ×
                                                    {{ $terceiroLugar->penaltisVisitante }}
                                                </small>
                                            @endif
                                            {{-- Status --}}
                                            @if ($terceiroLugar->status == 'AGENDADA')
                                                <span class="badge bg-primary">Agendada</span>
                                            @elseif ($terceiroLugar->status == 'PENDENTE')
                                                <span class="badge bg-secondary"> Pendente</span>
                                            @elseif ($terceiroLugar->status == 'FINALIZADA')
                                                <span class="badge bg-success">Finalizada</span>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- Visitante --}}
                                    <div class="col-4">
                                        <div class="d-flex align-items-center justify-content-end partida-equipe partida-visitante">
                                            <strong
                                                class="text-capitalize partida-nome text-truncate">{{ $terceiroLugar->visitante->nome }}</strong>
                                            <img src="{{ asset('storage/' . $terceiroLugar->visitante->escudo) }}"
                                                alt="Escudo {{ $terceiroLugar->visitante->nome }}"
                                                class="rounded-circle border partida-escudo flex-shrink-0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">A disputa de 3º lugar ainda não foi finalizada.</div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection