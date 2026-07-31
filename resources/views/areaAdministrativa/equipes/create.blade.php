@extends('areaAdministrativa.sidebar')

@section('title', 'Cadastrar Equipe')

@section('content')

    <div class="container">
        <h1 class="mb-4">Cadastrar Equipe</h1>

        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('equipes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- Nome --}}
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Equipe</label>
                <input type="text" class="form-control text-uppercase" id="nome" name="nome" value="{{ old('nome') }}"
                    required>
            </div>
            {{-- Sigla --}}
            <div class="mb-3">
                <label for="sigla" class="form-label">Sigla da Equipe</label>
                <input type="text" class="form-control text-uppercase" id="sigla" name="sigla" value="{{ old('sigla') }}"
                    maxlength="3" placeholder="Ex: GRE" required>
            </div>
            {{-- Escudo --}}
            <div class="mb-3">
                <label for="escudo" class="form-label">Escudo da Equipe</label>
                <input type="file" class="form-control" id="escudo" name="escudo" accept="image/*" required>
            </div>
            {{-- Status --}}
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status" required>
                    <option value="ATIVA" {{ old('status') == 'ATIVA' ? 'selected' : '' }}>ATIVA</option>
                    <option value="ENCERRADA" {{ old('status') == 'ENCERRADA' ? 'selected' : '' }}>ENCERRADA</option>
                    <option value="SUSPENSA" {{ old('status') == 'SUSPENSA' ? 'selected' : '' }}>SUSPENSA</option>
                </select>
            </div>
            {{--Botões de cadastrar e de voltar --}}
            <button type="submit" class="btn btn-primary">Cadastrar Equipe</button>
            <a href="{{ route('equipes.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection