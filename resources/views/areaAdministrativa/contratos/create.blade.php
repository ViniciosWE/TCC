@extends('areaAdministrativa.sidebar')

@section('title', 'Cadastrar Contrato')

@section('content')

    <div class="container">
        <h1 class="mb-4">Cadastrar Contrato</h1>

        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('contratos.store') }}" method="POST">
            @csrf
            {{-- Nome da Equipe --}}
            <div class="mb-3">
                <label for="equipe_nome" class="form-label">Selecione uma Equipe</label>
                <input list="lista-equipes" class="form-control" id="equipe_nome" name="equipe_nome"
                    placeholder="Digite para pesquisar..." value="{{ old('equipe_nome') }}" autocomplete="off" required>
                <input type="hidden" name="equipe_id" id="equipe_id" value="{{ old('equipe_id') }}">
                <datalist id="lista-equipes">
                    @foreach ($equipes as $equipe)
                        <option value="{{ $equipe->nome }}" data-id="{{ $equipe->id }}">
                        </option>
                    @endforeach
                </datalist>
            </div>
            {{-- Nome do participante --}}
            <div class="mb-3">
                <label for="participante_nome" class="form-label">Selecione um Participante</label>
                <input list="lista-participantes" class="form-control" id="participante_nome" name="participante_nome"
                    placeholder="Digite para pesquisar..." required value="{{ old('participante_nome') }}" autocomplete="off">
                <input type="hidden" name="participante_id" id="participante_id" value="{{ old('participante_id') }}">
                <datalist id="lista-participantes">
                    @foreach ($participantes as $participante)
                        <option value="{{ $participante->nome }}" data-id="{{ $participante->id }}">
                        </option>
                    @endforeach
                </datalist>
            </div>
            {{-- Status --}}
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status" required>
                    <option value="ATIVO" {{ old('status') == 'ATIVO' ? 'selected' : '' }}>ATIVO</option>
                    <option value="ENCERRADO" {{ old('status') == 'ENCERRADO' ? 'selected' : '' }}>ENCERRADO</option>
                </select>
            </div>
            {{--Botões de cadastrar e de voltar --}}
            <button type="submit" class="btn btn-primary">Cadastrar Equipe</button>
            <a href="{{ route('contratos.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection