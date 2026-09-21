@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Usuários')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gerenciar Usuários</h2>
            </div>
            <a href="{{ route('user.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Novo Usuário
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
                <input type="text" class="form-control" id="pesquisaUser" placeholder="Pesquisar Usuário...">
            </div>
        </div>
        {{-- Cards dos usuários --}}
        <div class="row g-4">
            @forelse($users as $user)
                <div class="col-12 col-md-6 col-xl-4 user-card">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-body">
                            {{-- informações do Usuário --}}
                            <div class="mb-2">
                                <h5 class="text-uppercase">{{ $user->name }}</h5>
                                @if ($user->tipo == 'ADMINISTRADOR')
                                    <span class="badge bg-success">ADMINISTRADOR</span>
                                @elseif($user->tipo == 'SUPER_ADMINISTRADOR')
                                    <span class="badge bg-info">SUPER ADMINISTRADOR</span>
                                @endif
                            </div>
                            {{-- Botões --}}
                            <div class="d-flex gap-2">
                                <a href="{{ route('user.ed', $user) }}" class="btn btn-warning flex-fill">
                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                </a>
                                <form action="{{ route('user.destroy', $user) }}" method="POST" class="flex-fill">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Deseja realmente excluir este usuário?')">
                                        <i class="bi bi-trash me-1"></i>Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Mensagem exibida quando não houver nenhum Usuário cadastrada --}}
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Nenhuma Usuário cadastrado.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection