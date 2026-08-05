@extends('areaAdministrativa.sidebar')

@section('title', 'Cadastrar Participante')

@section('content')

    <div class="container">
        <h1 class="mb-4">Cadastrar Participante</h1>

        <form action="{{ route('participantes.store') }}" method="POST">
            @csrf
            {{-- Nome --}}
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Participante</label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome') }}" required>
            </div>
            {{-- CPF --}}
            <div class="mb-3">
                <label for="cpf" class="form-label">CPF do Participante</label>
                <input type="text" class="form-control" id="cpf" name="cpf" value="{{ old('cpf') }}" maxlength="11"
                    required>
            </div>
            {{-- Número --}}
            <div class="mb-3">
                <label for="numero" class="form-label">Número do Participante</label>
                <input type="number" class="form-control" id="numero" name="numero" value="{{ old('numero') }} " inputmode="numeric">
            </div>
            {{-- Função --}}
            <div class="mb-3">
                <label for="funcao" class="form-label">Função</label>
                <select class="form-select" name="funcao" id="funcao">
                    <option value="">Selecione</option>
                    <option value="GOLEIRO" {{ old('funcao') == 'GOLEIRO' ? 'selected' : '' }}>Goleiro</option>
                    <option value="FIXO" {{ old('funcao') == 'FIXO' ? 'selected' : '' }}>Fixo</option>
                    <option value="ALA_DIREITO" {{ old('funcao') == 'ALA_DIREITO' ? 'selected' : '' }}>Ala Direito</option>
                    <option value="ALA_ESQUERDO" {{ old('funcao') == 'ALA_ESQUERDO' ? 'selected' : '' }}>Ala Esquerdo</option>
                    <option value="PIVO" {{ old('funcao') == 'PIVO' ? 'selected' : '' }}>Pivo</option>
                    <option value="GOLEIRO_LINHA" {{ old('funcao') == 'GOLEIRO_LINHA' ? 'selected' : '' }}>Goleiro Linha
                    </option>
                    <option value="TECNICO" {{ old('funcao') == 'TECNICO' ? 'selected' : '' }}>Tecnico</option>
                    <option value="AUXILIAR_TECNICO" {{ old('funcao') == 'AUXILIAR_TECNICO' ? 'selected' : '' }}>Auxiliar
                        Tecnico</option>
                    <option value="PREPARADOR_FISICO" {{ old('funcao') == 'PREPARADOR_FISICO' ? 'selected' : '' }}>Preparador
                        Físico</option>
                </select>
            </div>
            {{-- Status --}}
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status" required>
                    <option value="ATIVO" {{ old('status') == 'ATIVO' ? 'selected' : '' }}>Ativo</option>
                    <option value="SEM_EQUIPE" {{ old('status') == 'SEM_EQUIPE' ? 'selected' : '' }}>Sem Equipe</option>
                    <option value="APOSENTADO" {{ old('status') == 'APOSENTADO' ? 'selected' : '' }}>Aposentado</option>
                    <option value="SUSPENSO" {{ old('status') == 'SUSPENSO' ? 'selected' : '' }}>Suspenso</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar Equipe</button>

            <a href="{{ route('participantes.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection