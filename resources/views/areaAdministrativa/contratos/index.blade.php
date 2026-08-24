@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Contratos')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gerenciar Contratos</h2>
            </div>
            <a href="{{ route('contratos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Novo Contrato
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
                <input type="text" class="form-control" id="pesquisaContrato" placeholder="Pesquisar contrato...">
            </div>
        </div>
        {{-- Cards dos contratos --}}
        <div class="row g-4">
            @forelse($contratos as $contrato)
                <div class="col-12 col-md-6 col-xl-4 contrato-card">
                    <div class="card shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                        <div class="card-body">
                            {{-- Informações do contrato --}}
                            <div class="mb-4">
                                <h5 class="fw-bold text-uppercase mb-2 text-break">{{ $contrato->participante->nome }} </h5>

                                @if($contrato->participante->status == 'APOSENTADO')
                                    <span class="badge bg-secondary">Aposentado</span>
                                @elseif($contrato->participante->status == 'SEM_EQUIPE')
                                    <span class="badge bg-info">Sem Equipe</span>
                                @elseif($contrato->participante->status == 'SUSPENSO')
                                    <span class="badge bg-warning text-dark">Suspenso</span>
                                @endif
                                <p class="mb-2 text-break text-uppercase "><strong>Equipe:</strong> {{ $contrato->equipe->nome }}</p>

                                <p class="mb-0">
                                    <strong>Status:</strong>
                                    @if($contrato->status == 'ATIVO')
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Encerrado</span>
                                    @endif
                                </p>
                            </div>
                            {{-- Botões --}}
                            <div class="d-flex gap-2">
                                <a href="{{ route('contratos.edit', $contrato) }}" class="btn btn-warning flex-fill">
                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                </a>
                                <form action="{{ route('contratos.destroy', $contrato) }}" method="POST" class="flex-fill">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Deseja realmente excluir este contrato? Esta ação excluirá o registro do vínculo do jogador com a equipe e removerá seu histórico neste time.')">
                                        <i class="bi bi-trash me-1"></i>Excluir
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                {{-- Mensagem exibida quando não houver contratos --}}
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Nenhum contrato cadastrado.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection