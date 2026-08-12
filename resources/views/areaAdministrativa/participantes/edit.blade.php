@extends('areaAdministrativa.sidebar')

@section('title', 'Editar Participante')

@section('content')
    <div class="container">
        <h1 class="mb-4">Editar Participante</h1>
        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('participantes.update', $participante) }}" method="POST">
            @csrf
            @method('PUT')
            {{-- Nome --}}
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Participante</label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome', $participante->nome) }}"
                    required>
            </div>
            {{-- CPF --}}
            <div class="mb-3">
                <label for="cpf" class="form-label">CPF do Participante</label>
                <input type="text" class="form-control" id="cpf" name="cpf" value="{{ old('cpf', $participante->cpf)}}"
                    maxlength="11" required>
            </div>
            {{-- Número --}}
            <div class="mb-3">
                <label for="numero" class="form-label">Número do Participante</label>
                <input type="text" class="form-control" id="numero" name="numero" maxlength="2"
                    value="{{ old('numero', $participante->numero) }} " inputmode="numeric">
            </div>
            {{-- Função --}}
            <div class="mb-3">
                <label for="funcao" class="form-label">Função</label>
                <select class="form-select" name="funcao" id="funcao">
                    <option value="">Selecione</option>
                    <option value="GOLEIRO" {{ old('funcao', $participante->funcao) == 'GOLEIRO' ? 'selected' : '' }}>Goleiro
                    </option>
                    <option value="FIXO" {{ old('funcao', $participante->funcao) == 'FIXO' ? 'selected' : '' }}>Fixo</option>
                    <option value="ALA_DIREITO" {{ old('funcao', $participante->funcao) == 'ALA_DIREITO' ? 'selected' : '' }}>
                        Ala Direito</option>
                    <option value="ALA_ESQUERDO" {{ old('funcao', $participante->funcao) == 'ALA_ESQUERDO' ? 'selected' : '' }}>Ala Esquerdo</option>
                    <option value="PIVO" {{ old('funcao', $participante->funcao) == 'PIVO' ? 'selected' : '' }}>Pivo</option>
                    <option value="GOLEIRO_LINHA" {{ old('funcao', $participante->funcao) == 'GOLEIRO_LINHA' ? 'selected' : '' }}>Goleiro Linha
                    </option>
                    <option value="TECNICO" {{ old('funcao', $participante->funcao) == 'TECNICO' ? 'selected' : '' }}>Tecnico
                    </option>
                    <option value="AUXILIAR_TECNICO" {{ old('funcao', $participante->funcao) == 'AUXILIAR_TECNICO' ? 'selected' : '' }}>Auxiliar
                        Tecnico</option>
                    <option value="PREPARADOR_FISICO" {{ old('funcao', $participante->funcao) == 'PREPARADOR_FISICO' ? 'selected' : '' }}>Preparador
                        Físico</option>
                </select>
            </div>
            {{-- Status --}}
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status" required>
                    <option value="ATIVO" {{ old('status', $participante->status) == 'ATIVO' ? 'selected' : '' }}>Ativo
                    </option>
                    <option value="SEM_EQUIPE" {{ old('status', $participante->status) == 'SEM_EQUIPE' ? 'selected' : '' }}>
                        Sem Equipe</option>
                    <option value="APOSENTADO" {{ old('status', $participante->status) == 'APOSENTADO' ? 'selected' : '' }}>
                        Aposentado</option>
                    <option value="SUSPENSO" {{ old('status', $participante->status) == 'SUSPENSO' ? 'selected' : '' }}>
                        Suspenso</option>
                </select>
            </div>

            {{-- Equipe --}}
            <div class="mb-3" id="divEquipe">
                <label for="equipe_nome" class="form-label">Selecione uma Equipe</label>
                <input list="lista-equipes" class="form-control" id="equipe_nome" placeholder="Digite para pesquisar..."
                    value="{{ old('equipe_id') ? $equipes->find(old('equipe_id'))?->nome : $contrato?->equipe?->nome }}">
                <input type="hidden" name="equipe_id" id="equipe_id" value="{{ old('equipe_id', $contrato?->equipe_id) }}">
                <datalist id="lista-equipes">
                    @foreach ($equipes as $equipe)
                        <option value="{{ $equipe->nome }}" data-id="{{ $equipe->id }}">
                        </option>
                    @endforeach
                </datalist>
            </div>
            {{-- Botões --}}
            <button type="submit" class="btn btn-primary">Salvar Alterações </button>
            <a href="{{ route('participantes.index') }}" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
@endsection