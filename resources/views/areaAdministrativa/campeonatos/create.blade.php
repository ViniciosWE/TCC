@extends('areaAdministrativa.sidebar')

@section('title', 'Cadastrar Campeonato')

@section('content')

    <div class="container">
        <h1 class="mb-4">Cadastrar Campeonato</h1>
        @if ($errors->any())
            <div class="alert alert-danger mb-3" id="sumirMensagem">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('campeonatos.store') }}" method="POST">
            @csrf
            {{-- Nome --}}
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Campeonato</label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome') }}" required>
            </div>
            {{-- Mínimo jogadores por equipe --}}
            <div class="mb-3">
                <label for="minimo_jogadores_equipes" class="form-label">Mínimo de jogadores por equipe(Mínimo de 5 participantes)</label>
                <input type="text" class="form-control" id="minimo_jogadores_equipes" name="minimo_jogadores_equipes"
                    value="{{ old('minimo_jogadores_equipes') }}" min="5" required>
            </div>
            {{-- Máximo equipes --}}
            <div class="mb-3">
                <label for="maximo_equipes" class="form-label">Quantidade máxima de equipes(Mínimo de 2 equipes)</label>
                <input type="text" class="form-control" id="maximo_equipes" name="maximo_equipes"
                    value="{{ old('maximo_equipes') }}" min="2" required>
            </div>
            {{-- Tipo --}}
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo do Campeonato</label>
                <select class="form-select" name="tipo" id="tipo" required>
                    <option value="">Selecione</option>
                    <option value="MATA_MATA" {{ old('tipo') == 'MATA_MATA' ? 'selected' : '' }}>Mata-mata</option>
                    <option value="GRUPOS_MATA_MATA" {{ old('tipo') == 'GRUPOS_MATA_MATA' ? 'selected' : '' }}>Grupos +
                        Mata-mata</option>
                    <option value="PONTOS_CORRIDOS" {{ old('tipo') == 'PONTOS_CORRIDOS' ? 'selected' : '' }}>Pontos corridos
                    </option>
                </select>
            </div>
            {{-- Categoria --}}
            <div class="mb-3">
                <label for="categoria" class="form-label">Categoria</label>
                <input type="text" class="form-control" id="categoria" name="categoria" value="{{ old('categoria') }}"
                    placeholder="Ex: Série A, Série B, Feminino..." required>
            </div>
            {{-- Data início --}}
            <div class="mb-3">
                <label for="data_inicio" class="form-label">Data de início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="{{ old('data_inicio') }}"
                    required>
            </div>
            {{-- Data fim --}}
            <div class="mb-3">
                <label for="data_fim" class="form-label">Data de término</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="{{ old('data_fim') }}"
                    required>
            </div>
            {{-- Status --}}
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status" required>
                    <option value="INSCRICOES" {{ old('status') == 'INSCRICOES' ? 'selected' : '' }}>Inscrições abertas
                    </option>
                    <option value="EM_ANDAMENTO" {{ old('status') == 'EM_ANDAMENTO' ? 'selected' : '' }}>Em andamento</option>
                    <option value="FINALIZADO" {{ old('status') == 'FINALIZADO' ? 'selected' : '' }}>Finalizado</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Cadastrar Campeonato</button>

            <a href="{{ route('campeonatos.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </form>
    </div>
@endsection