@extends('areaAdministrativa.sidebar')

@section('title', 'Editar Usuário')

@section('content')
    <div class="container">
        <h1 class="mb-4">Editar Usuário</h1>
        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('user.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            {{-- Nome --}}
            <div class="mb-3">
                <label for="name" class="form-label">Nome do Usuário</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}"
                    required>
            </div>
            {{-- email --}}
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}"
                    required>
            </div>
            {{-- senha --}}
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <div class="position-relative">
                    <input type="password" class="form-control" id="password" name="password">
                    <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0">
                        <i class="bi bi-eye" id="passwordIcon"></i>
                    </button>
                </div>
                <span class="form-text">Deixe em branco se não quiser alterar a senha.</span>
            </div>
            {{-- tipo --}}
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" name="tipo" id="tipo" required>
                        <option value="ADMINISTRADOR" {{ old('tipo', $user->tipo) == 'ADMINISTRADOR' ? 'selected' : '' }}>
                            ADMINISTRADOR
                        </option>
                        <option value="SUPER_ADMINISTRADOR" {{ old('tipo', $user->tipo) == 'SUPER_ADMINISTRADOR' ? 'selected' : '' }}>SUPER
                            ADMINISTRADOR</option>
                    </select>
            </div>
            {{-- Botões --}}
            <button type="submit" class="btn btn-primary">Salvar Alterações </button>
            <a href="{{ route('user.index') }}" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
@endsection