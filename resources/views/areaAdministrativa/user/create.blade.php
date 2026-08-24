@extends('areaAdministrativa.sidebar')

@section('title', 'Cadastrar Usuário')

@section('content')

    <div class="container">
        <h1 class="mb-4">Cadastrar Usuário</h1>

        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('user.store') }}" method="POST">
            @csrf
            {{-- Nome --}}
            <div class="mb-3">
                <label for="name" class="form-label">Nome do Usuário</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            {{-- email --}}
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            {{-- senha --}}
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            {{-- tipo --}}
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" name="tipo" id="tipo" required>
                    <option value="ADMINISTRADOR" {{ old('tipo') == 'ADMINISTRADOR' ? 'selected' : '' }}>ADMINISTRADOR
                    </option>
                    <option value="SUPER_ADMINISTRADOR" {{ old('tipo') == 'SUPER_ADMINISTRADOR' ? 'selected' : '' }}>SUPER
                        ADMINISTRADOR</option>

                </select>
            </div>
            {{--Botões de cadastrar e de voltar --}}
            <button type="submit" class="btn btn-primary">Cadastrar Usuário</button>
            <a href="{{ route('user.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection