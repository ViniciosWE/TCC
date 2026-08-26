@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Campeonatos')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gerenciar Campeonatos</h2>
            </div>
            <a href="{{ route('campeonatos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Novo Campeonato
            </a>
        </div>
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
                <input type="text" class="form-control" id="pesquisaCampeonato" placeholder="Pesquisar campeonato...">
            </div>
        </div>
        {{-- Cards dos campeonatos --}}
        <div class="row g-4">
            @forelse($campeonatos as $campeonato)
                <div class="col-12 col-md-6 col-xl-4 campeonato-card">
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
                                <a href="{{ route('campeonatos.show', $campeonato) }}" class="btn btn-secondary w-100">
                                    <i class=" bi bi-eye me-1"></i>
                                    Detalhes
                                </a>
                                {{-- Gerar Partidas --}}
                                @if ($campeonato->status == 'INSCRICOES' && $campeonato->inscricoes_count == $campeonato->maximo_equipes)
                                    <form action="{{ route('campeonatos.gerarConfrontos', $campeonato) }}" method="POST"
                                        class="flex-fill w-100">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100"
                                            onclick="return confirm('Deseja realizar o sorteio das partidas?')">
                                            <i class="bi bi-shuffle me-1"></i>Gerar Partidas
                                        </button>
                                    </form>
                                @endif
                                {{-- Ver Partidas --}}
                                @if ($campeonato->status == 'EM_ANDAMENTO')
                                    {{-- Redireciona para a tela de partidas, enviando o ID e o nome do campeonato --}}
                                    <a href="{{ route('partidas.index', ['campeonato_id' => $campeonato->id, 'campeonato_nome' => $campeonato->nome]) }}"
                                        class="btn btn-primary w-100"> <i class="bi bi-eye me-1"></i>Ver Partidas
                                    </a>
                                @endif
                                {{-- Editar --}}
                                <a href="{{ route('campeonatos.edit', $campeonato) }}" class="btn btn-warning flex-fill">
                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                </a>
                                {{-- Excluir --}}
                                <form action="{{ route('campeonatos.destroy', $campeonato) }}" method="POST" class="flex-fill">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Deseja realmente excluir este campeonato?')">
                                        <i class="bi bi-trash me-1"></i>Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Mensagem exibida quando não houver nenhuma equipe cadastrada --}}
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Nenhum campeonato cadastrada.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection