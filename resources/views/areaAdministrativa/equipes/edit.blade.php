@extends('areaAdministrativa.sidebar')

@section('title', 'Editar Equipe')

@section('content')
    <div class="container">
        <h1 class="mb-4">Editar Equipe</h1>
        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('equipes.update', $equipe) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            {{-- Nome --}}
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Equipe</label>
                <input type="text" class="form-control text-uppercase" id="nome" name="nome"
                    value="{{ old('nome', $equipe->nome) }}" required>
            </div>
            {{-- Sigla --}}
            <div class="mb-3">
                <label for="sigla" class="form-label">Sigla da Equipe</label>
                <input type="text" class="form-control text-uppercase" id="sigla" name="sigla"
                    value="{{ old('sigla', $equipe->sigla) }}" maxlength="3" required>
            </div>
            {{-- Escudo atual --}}
            <div class="mb-3">
                <label class="form-label">
                    Escudo atual
                </label>
                <br>
                <img src="{{ asset('storage/' . $equipe->escudo) }}" alt="Escudo {{ $equipe->nome }}" width="80" height="80"
                    class="rounded-circle border mb-3">
            </div>
            {{-- Novo escudo --}}
            <div class="mb-3">
                <label for="escudo" class="form-label">Alterar escudo</label>
                <input type="file" class="form-control" id="escudo" name="escudo" accept="image/*">
                <small class="text-muted">Deixe vazio caso não queira alterar o escudo.</small>
            </div>
            {{-- Status --}}
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status" required>
                    <option value="ATIVA" {{ old('status', $equipe->status) == 'ATIVA' ? 'selected' : '' }}>ATIVA</option>
                    <option value="ENCERRADA" {{ old('status', $equipe->status) == 'ENCERRADA' ? 'selected' : '' }}>ENCERRADA
                    </option>
                    <option value="SUSPENSA" {{ old('status', $equipe->status) == 'SUSPENSA' ? 'selected' : '' }}>SUSPENSA
                    </option>
                </select>
            </div>
            {{-- Botões --}}
            <button type="submit" class="btn btn-primary">Salvar Alterações </button>
            <a href="{{ route('equipes.index') }}" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
@endsection