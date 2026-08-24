@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Equipes')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gerenciar Equipes</h2>
            </div>
            <a href="{{ route('equipes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nova Equipe
            </a>
        </div>
        {{-- Mensagem de sucesso --}}
        @if(session('success'))
            <div class="alert alert-success" id="sumirMensagem">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" id="sumirMensagem">
                {{ session('error') }}
            </div>
        @endif

        {{-- Campo de pesquisa --}}
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="pesquisaEquipe" placeholder="Pesquisar equipe...">
            </div>
        </div>
        {{-- Cards das equipes --}}
        <div class="row g-4">
            @forelse($equipes as $equipe)
                <div class="col-12 col-md-6 col-xl-4 equipe-card">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-body">
                            {{-- Informações da equipe --}}
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ asset('storage/' . $equipe->escudo) }}" alt="Escudo {{ $equipe->nome }}"
                                    width="70" height="70" class="rounded-circle border p-1">
                                <div>
                                    <h6 class="fw-bold mb-1 text-uppercase text-break">{{ $equipe->nome }}</h6>
                                    <span class="text-muted text-uppercase">{{ $equipe->sigla }}</span>
                                </div>
                            </div>
                            {{-- Status --}}
                            <div class="mb-4">
                                @if($equipe->status == 'ATIVA')
                                    <span class="badge bg-success">Ativa</span>
                                @elseif($equipe->status == 'ENCERRADA')
                                    <span class="badge bg-secondary"> Encerrada</span>
                                @else
                                    <span class="badge bg-warning text-dark">Suspensa</span>
                                @endif
                                <div class="mt-2">
                                    <span class="badge bg-primary">
                                        <i class="bi bi-people-fill me-1"></i>
                                        {{ $equipe->participantes_ativos_count }}
                                        {{ $equipe->participantes_ativos_count == 1 ? 'participante' : 'participantes' }}
                                    </span>
                                </div>
                            </div>
                            {{-- Botões --}}
                            <div class="d-flex gap-2">
                                <a href="{{ route('equipes.edit', $equipe) }}" class="btn btn-warning flex-fill">
                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                </a>
                                <form action="{{ route('equipes.destroy', $equipe) }}" method="POST" class="flex-fill">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Deseja realmente excluir esta equipe?')">
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
                        Nenhuma equipe cadastrada.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection