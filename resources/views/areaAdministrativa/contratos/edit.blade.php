@extends('areaAdministrativa.sidebar')

@section('title', 'Editar Contrato')

@section('content')

    <div class="container">
        <h1 class="mb-4">Editar Contrato</h1>

        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('contratos.update', $contrato) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="equipe_nome" class="form-label">Selecione uma Equipe</label>
                <input list="lista-equipes" class="form-control" id="equipe_nome" placeholder="Digite para pesquisar..."
                    required value=" {{ old('equipe_id') ? $equipes->firstWhere('id', old('equipe_id'))?->nome : $contrato->equipe->nome }}">
                <input type="hidden" name="equipe_id" id="equipe_id" value="{{ old('equipe_id', $contrato->equipe_id) }}">
                <datalist id="lista-equipes">
                    @foreach ($equipes as $equipe)
                        <option value="{{ $equipe->nome }}" data-id="{{ $equipe->id }}">
                        </option>
                    @endforeach
                </datalist>
            </div>
            {{-- Status --}}
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status" required>
                    <option value="ATIVO" {{ old('status', $contrato->status) == 'ATIVO' ? 'selected' : '' }}>ATIVO</option>
                    <option value="ENCERRADO" {{ old('status', $contrato->status) == 'ENCERRADO' ? 'selected' : '' }}>ENCERRADO</option>
                </select>
            </div>
            {{--Botões--}}
            <button type="submit" class="btn btn-primary">Editar Equipe</button>
            <a href="{{ route('contratos.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection