@extends('areaAdministrativa.sidebar')

@section('title', 'Estatísticas do Campeonato')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Estatísticas do Campeonato</h2>
            </div>
            <a href="{{ route('campeonatos.index') }}" class="btn btn-secondary"></i>Voltar</a>
        </div>
    </div>
@endsection