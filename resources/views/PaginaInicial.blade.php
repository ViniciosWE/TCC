@extends('LayoutInicial')

@section('title', 'Página Inicial')

@section('content')
    <div class="bg-body py-2 px-4">
        <form action="{{ route('PaginaInicial') }}" method="GET" class="d-flex align-items-center gap-2">
            <label for="data" class="form-label mb-0 fw-bold">Data</label>
            <input type="date" name="data" id="data" class="form-control" style="width: auto;" value="{{ $data }}">
            <button type="submit" class="btn btn-primary filtro"><i class="bi bi-search me-1"></i>Buscar</button>
            @if(request('data'))
                <a href="{{ route('PaginaInicial') }}" class="btn btn-secondary filtro">Limpar</a>
            @endif
        </form>
    </div>
    <div class="p-4">
        <div class="row g-4">
            {{-- Jogos --}}
            <div class="col-12 col-lg-8">
                <div class="border-start border-primary border-5 ps-2 mb-3">
                    <h4 class="fw-bold mb-1 text-uppercase">Jogos</h4>
                </div>
                <div class="row g-4">
                    @forelse($partidas as $campeonatoId => $jogos)
                        @php
                            $campeonato = $jogos->first()->campeonato;
                        @endphp
                        <div class="col-12 partida-card">
                            <div class="border-start border-primary border-3 ps-2 mb-2">
                                <h6 class="fw-bold mb-0">{{ $campeonato->nome }}</h6>
                            </div>
                            <div class="row g-4">
                                @foreach($jogos as $partida)
                                    <div class="col-12 partida-card">
                                        <div class="card shadow-sm border-0 rounded-4">
                                            <div class="card-body partida-card-body">
                                                {{-- Data e Local --}}
                                                <div
                                                    class="d-flex justify-content-center align-items-center flex-wrap gap-2 gap-md-4 text-muted small mb-3 partida-info">
                                                    <span class="text-nowrap">
                                                        <i
                                                            class="bi bi-calendar3 me-1"></i>{{ $partida->data_hora ? date('d/m/Y H:i', strtotime($partida->data_hora)) : 'Data não definida' }}
                                                    </span>
                                                    <span class="text-nowrap"><i
                                                            class="bi bi-geo-alt me-1"></i>{{ $partida->local ?? 'Local não definido' }}</span>
                                                </div>
                                                <div class="row align-items-center text-center g-0">
                                                    {{-- Mandante --}}
                                                    <div class="col-4">
                                                        <div
                                                            class="d-flex align-items-center justify-content-start partida-equipe partida-mandante">
                                                            @if ($partida->mandante->escudo)
                                                                <img src="{{ asset('storage/' . $partida->mandante->escudo) }}"
                                                                    alt="Escudo {{ $partida->mandante->nome }}"
                                                                    class="rounded-circle border partida-escudo flex-shrink-0">
                                                            @else
                                                                <div class="text-muted d-flex justify-content-center align-items-center text-center border rounded-circle partida-escudo"
                                                                    style="font-size: 0.8rem;"> <span>Sem Escudo</span>
                                                                </div>
                                                            @endif

                                                            <strong
                                                                class="text-capitalize partida-nome text-truncate">{{ $partida->mandante->nome }}</strong>
                                                        </div>
                                                    </div>
                                                    {{-- Placar --}}
                                                    <div class="col-4">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <span
                                                                class="badge bg-light text-dark border mb-2 small">{{ str_replace('_', ' ', $partida->fase) }}</span>
                                                            <h2 class="fw-bold mb-1 partida-placar">
                                                                {{ $partida->gols_mandante }}
                                                                <span class="mx-1">×</span>
                                                                {{ $partida->gols_visitante }}
                                                            </h2>
                                                            @if ($partida->penaltisMandante > 0 || $partida->penaltisVisitante > 0)
                                                                <small class="text-muted">
                                                                    {{ $partida->penaltisMandante }}
                                                                    ×
                                                                    {{ $partida->penaltisVisitante }}
                                                                </small>
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
                                                        <div
                                                            class="d-flex align-items-center justify-content-end partida-equipe partida-visitante">
                                                            <strong
                                                                class="text-capitalize partida-nome text-truncate">{{ $partida->visitante->nome }}</strong>
                                                            @if ($partida->visitante->nome)
                                                                <img src="{{ asset('storage/' . $partida->visitante->escudo) }}"
                                                                    alt="Escudo {{ $partida->visitante->nome }}"
                                                                    class="rounded-circle border partida-escudo flex-shrink-0">
                                                            @else
                                                                <div class="text-muted d-flex justify-content-center align-items-center text-center border rounded-circle partida-escudo"
                                                                    style="font-size: 0.8rem;"> <span>Sem Escudo</span>
                                                                </div>
                                                            @endif

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center text-muted py-5">Nenhum jogo programado para esta data.</div>
                        </div>
                    @endforelse
                </div>
            </div>
            {{-- lado direito --}}
            <div class="col-12 col-lg-4">
                {{-- última notícia --}}
                <div class="border-start border-warning border-5 ps-2 mb-3">
                    <h4 class="fw-bold mb-1 text-uppercase">Última notícia</h4>
                </div>
                @if ($noticia)
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class=" card-body">
                            <div class="text-center mb-3">
                                @if ($noticia->imagem)
                                    <img src="{{ asset('storage/' . $noticia->imagem) }}"
                                        alt="Imagem Notícia {{ $noticia->titulo }}" class="img-fluid rounded object-fit-cover"
                                        style="width: 80%; height: 80%;">
                                @else
                                    <div class="text-muted d-flex flex-column justify-content-center align-items-center border rounded mx-auto"
                                        style="width: 80%; height: 160px;">
                                        <i class="bi bi-image fs-1 mb-2"></i><span>Notícia sem imagem</span>
                                </div> @endif
                            </div>
                            <h5 class="fw-bold mb-3 text-break descricao-noticia">{{ $noticia->titulo }}</h5>
                            <a href="{{ route('noticiasDetalhes', $noticia) }}" class="btn btn-secondary w-100">
                                <i class="bi bi-eye me-1"></i>Detalhes
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-4">Nenhuma notícia publicada.</div>
                @endif
                {{-- Canal de transmissão --}}
                <div class="border-start border-danger border-5 ps-2 mb-3">
                    <h4 class="fw-bold mb-1 text-uppercase"> Canal de transmissão</h4>
                </div>
                <div class="card shadow-sm border-0 rounded-4 p-3">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        {{-- Ícone --}}
                        <div class="d-flex align-items-center justify-content-center bg-danger rounded-circle flex-shrink-0"
                            style="width: 50px; height: 50px;">
                            <i class="bi bi-camera-video-fill text-white fs-5"></i>
                        </div>
                        {{-- Texto --}}
                        <div class="flex-grow-1">
                            <span class="fw-bold">Transmissão ao vivo</span>
                        </div>
                        {{-- Botão --}}
                        <a href="https://www.youtube.com" target="_blank" rel="noopener noreferrer"
                            class="btn btn-danger fw-bold rounded-pill px-3">Assistir
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection