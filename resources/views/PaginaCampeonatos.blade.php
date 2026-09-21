@extends('LayoutInicial')

@section('title', 'Página de Campeonatos')

@section('content')
    {{-- Filtro--}}
    <div class="bg-body py-2 px-4">
        <div class="d-flex flex-wrap gap-3 text-uppercase">
            <a href="{{ route('PaginaCampeonatos', ['status' => 'EM_ANDAMENTO']) }}"
                class="btn rounded-pill px-4 py-2 shadow-sm fw-bold filtro-campeonato
                            {{ $status === 'EM_ANDAMENTO' ? 'btn-primary' : 'bg-secondary-subtle text-dark border-0 filtro-hover' }}">Em andamento
            </a>
            <a href="{{ route('PaginaCampeonatos', ['status' => 'INSCRICOES']) }}"
                class="btn rounded-pill px-4 py-2 shadow-sm fw-bold filtro-campeonato
                            {{ $status === 'INSCRICOES' ? 'btn-primary' : 'bg-secondary-subtle text-dark border-0 filtro-hover' }}">Inscrições
            </a>
            <a href="{{ route('PaginaCampeonatos', ['status' => 'FINALIZADO']) }}"
                class="btn rounded-pill px-4 py-2 shadow-sm fw-bold filtro-campeonato
                            {{ $status === 'FINALIZADO' ? 'btn-primary' : 'bg-secondary-subtle text-dark border-0 filtro-hover' }}">Finalizados
            </a>
        </div>
    </div>
    <div class="p-4">
        <div class="border-start border-primary border-5 ps-2 mb-3">
            <h4 class="fw-bold mb-1 text-uppercase">Campeonatos</h4>
        </div>
        <div class="row g-4">
            @forelse($campeonatos as $campeonato)
                <div class="col-12 col-md-6 col-lg-4 col-xl-3 campeonato-card">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-body">
                            {{-- Informações do campeonato --}}
                            <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                                <div>
                                    <h5 class="fw-bold mb-1 text-uppercase text-break">{{ $campeonato->nome }}</h5>
                                    <p> {{ date('d/m/Y', strtotime($campeonato->data_inicio))}} até
                                        {{date('d/m/Y', strtotime($campeonato->data_fim))}}
                                    </p>
                                    <p><span class="fw-bold">Categoria: </span>{{ $campeonato->categoria }}</p>
                                    @if ($campeonato->tipo == 'MATA_MATA')
                                        <span class="badge bg-success">Mata-mata </span>
                                    @elseif($campeonato->tipo == 'GRUPOS_MATA_MATA')
                                        <span class="badge bg-success">Grupos + Mata-mata</span>
                                    @elseif($campeonato->tipo == 'PONTOS_CORRIDOS')
                                        <span class="badge bg-success">Pontos Corridos</span>
                                    @endif

                                    @if ($campeonato->status == 'INSCRICOES')
                                        <span class="badge bg-success">Inscrições </span>
                                    @elseif($campeonato->status == 'EM_ANDAMENTO')
                                        <span class="badge bg-warning">Em Andamento</span>
                                    @elseif($campeonato->status == 'FINALIZADO')
                                        <span class="badge bg-danger">Finalizado</span>
                                    @endif

                                    <span class="badge bg-primary">{{ $campeonato->inscricoes_count }} /
                                        {{ $campeonato->maximo_equipes }} equipes</span>
                                </div>
                            </div>
                            {{-- Botões --}}
                            <div class="d-flex flex-wrap gap-2">
                                {{-- Detalhes --}}
                                <a href="{{ route('PaginaClassificacao', $campeonato) }}" class="btn btn-secondary w-100">
                                    <i class=" bi bi-eye me-1"></i>
                                    Classificação/Estatísticas
                                </a>
                                {{-- Ver Partidas --}}
                                @if ($campeonato->status == 'EM_ANDAMENTO' || $campeonato->status == 'FINALIZADO')
                                    <a href="{{ route('PaginaPartidas', ['campeonato' => $campeonato->id]) }}"
                                        class="btn btn-primary w-100">
                                        <i class="bi bi-list-ul me-1"></i>
                                        Listar Partidas
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Mensagem exibida quando não houver nenhuma equipe cadastrada --}}
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        @if ($status === 'INSCRICOES')
                            Nenhum campeonato com inscrições abertas.

                        @elseif ($status === 'EM_ANDAMENTO')
                            Nenhum campeonato em andamento.

                        @elseif ($status === 'FINALIZADO')
                            Nenhum campeonato finalizado.
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection