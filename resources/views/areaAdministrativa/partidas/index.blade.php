@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciamento de Partidas')

@section('content')

    <div class="container">

        <div class="mb-4">
            <h2 class="fw-bold">Gerenciamento de Partidas</h2>
        </div>

        {{-- Selecionar campeonato --}}
        <form action="{{ route('partidas.index') }}" method="GET" class="mb-4">

            <div class="row">

                <div class="col-12 col-md-8">

                    <label for="campeonato_id" class="form-label">
                        Campeonato
                    </label>

                    <select name="campeonato_id" id="campeonato_id" class="form-select">

                        <option value="">
                            Selecione um campeonato
                        </option>

                        @foreach ($campeonatos as $campeonato)

                            <option value="{{ $campeonato->id }}" {{ request('campeonato_id') == $campeonato->id ? 'selected' : '' }}>

                                {{ $campeonato->nome }}

                            </option>

                        @endforeach

                    </select>
                </div>

                <div class="col-12 col-md-4 d-flex align-items-end">

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>
                        Ver Partidas
                    </button>

                </div>

            </div>

        </form>


        {{-- Partidas --}}
        @if (request('campeonato_id'))

            <h4 class="fw-bold mb-3">
                Partidas
            </h4>

            @forelse ($partidas as $partida)

                <div class="card shadow-sm border-0 rounded-4 mb-3">

                    <div class="card-body">

                        <div class="row align-items-center text-center">

                            {{-- Mandante --}}
                            <div class="col-4">

                                <strong>
                                    {{ $partida->mandante->nome }}
                                </strong>

                            </div>

                            {{-- Placar --}}
                            <div class="col-4">

                                <h4 class="fw-bold mb-1">

                                    {{ $partida->gols_mandante }}

                                    <span class="mx-2">×</span>

                                    {{ $partida->gols_visitante }}

                                </h4>

                                <span class="badge bg-secondary">
                                    {{ $partida->status }}
                                </span>

                            </div>

                            {{-- Visitante --}}
                            <div class="col-4">

                                <strong>
                                    {{ $partida->visitante->nome }}
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <div class="alert alert-info">
                    Nenhuma partida encontrada para este campeonato.
                </div>

            @endforelse

        @endif

    </div>

@endsection