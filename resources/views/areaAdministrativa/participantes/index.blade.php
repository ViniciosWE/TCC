@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Participantes')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gerenciar Participantes</h2>
            </div>
            <a href="{{ route('participantes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Novo Participante
            </a>
        </div>
        {{-- Mensagem de sucesso --}}
        @if(session('success'))
            <div class="alert alert-success" id="sumirMensagem">
                {{ session('success') }}
            </div>
        @endif
        {{-- Cards dos participantes --}}
        <div class="row g-4">
            @forelse($participantes as $participante)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-body">
                            {{-- Informações do participante --}}
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1 text-uppercase">
                                        {{ $participante->nome }}
                                    </h5>
                                    <p class=" mb-1">
                                        {{ $participante->cpf }}
                                    </p>
                                    @if ($participante->funcao == 'ALA_DIREITO')
                                        <span class="text-muted text-uppercase">
                                            Ala Direito
                                        </span>
                                    @elseif($participante->funcao == 'ALA_ESQUERDO')
                                        <span class="text-muted text-uppercase">
                                            Ala Esquerdo
                                        </span>
                                    @elseif($participante->funcao == 'GOLEIRO_LINHA')
                                        <span class="text-muted text-uppercase">
                                            Goleiro Linha
                                        </span>
                                    @elseif($participante->funcao == 'TECNICO')
                                        <span class="text-muted text-uppercase">
                                            Técnico
                                        </span>
                                    @elseif($participante->funcao == 'AUXILIAR_TECNICO')
                                        <span class="text-muted text-uppercase">
                                            Auxiliar Técnico
                                        </span>
                                    @elseif($participante->funcao == 'PREPARADOR_FISICO')
                                        <span class="text-muted text-uppercase">
                                            Preparador Físico
                                        </span>
                                    @else
                                        <span class="text-muted text-uppercase">
                                            {{ $participante->funcao }}
                                        </span>
                                    @endif
                                    <span class="text-muted text-uppercase">
                                        {{ $participante->numero }}
                                    </span>
                                </div>
                            </div>
                            {{-- Status --}}
                            <div class="mb-4">
                                @if($participante->status == 'ATIVO')
                                    <span class="badge bg-success">
                                        Ativo
                                    </span>
                                @elseif($participante->status == 'APOSENTADO')
                                    <span class="badge bg-secondary">
                                        Aposentado
                                    </span>
                                @elseif($participante->status == 'SEM_EQUIPE')
                                    <span class="badge bg-info">
                                        Sem Equipe
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        Suspenso
                                    </span>
                                @endif
                            </div>
                            {{-- Botões --}}
                            <div class="d-flex gap-2">
                                <a href="{{ route('participantes.edit', $participante) }}" class="btn btn-warning flex-fill">
                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                </a>
                                <form action="{{ route('participantes.destroy', $participante) }}" method="POST" class="flex-fill">
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
                {{-- Mensagem exibida quando não houver nenhum participante cadastrado --}}
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Nenhum participante cadastrado.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection