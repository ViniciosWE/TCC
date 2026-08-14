@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Inscrições')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gerenciar Inscrições</h2>
            </div>
            <a href="{{ route('inscricoes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nova Incrição
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
                <input type="text" class="form-control" id="pesquisaInscricao" placeholder="Pesquisar notícia...">
            </div>
        </div>
        {{-- Cards das Inscrições--}}
        <div class="row g-4">
            @forelse($inscricoes as $inscricao)
                <div class="col-12 col-md-6 col-xl-4 contrato-card">
                    <div class="card shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                        <div class="card-body">
                            {{-- Informações da Incrição --}}
                            <div class="mb-4">
                                <p class="mb-2 text-break">{{ $inscricao->campeonato->nome }}</p>
                                <p class="mb-2 text-break">{{ $inscricao->equipe->nome }}</p>
                            </div>
                            {{-- Botões --}}
                            <div class="d-flex gap-2">
                                <a href="{{ route('inscricoes.edit', $inscricao) }}" class="btn btn-warning flex-fill">
                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                </a>
                                <form action="{{ route('inscricoes.destroy', $inscricao) }}" method="POST" class="flex-fill">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Deseja realmente excluir esta inscrição?')">
                                        <i class="bi bi-trash me-1"></i>Excluir
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                {{-- Mensagem exibida quando não houver inscrições --}}
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Nenhuma inscrição cadastrada.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection