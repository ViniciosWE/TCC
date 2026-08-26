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
        {{-- Classificação pontos corridos --}}
        @if ($campeonato->tipo === 'PONTOS_CORRIDOS')
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Classificação</h4>
                    <div class="w-100 overflow-hidden">
                        <table class="table table-hover align-middle text-center table-sm small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="text-start">Equipe</th>
                                    <th>J</th>
                                    <th>V</th>
                                    <th>E</th>
                                    <th>D</th>
                                    <th>SG</th>
                                    <th>PTS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classificacao as $item)
                                    <tr>
                                        <td class="fw-bold">{{ $item['posicao'] }}º</td>
                                        <td class="text-start fw-semibold text-break text-capitalize">{{ $item['equipe']->nome }}
                                        </td>
                                        <td>{{ $item['jogos'] }}</td>
                                        <td>{{ $item['vitorias'] }}</td>
                                        <td>{{ $item['empates'] }}</td>
                                        <td>{{ $item['derrotas'] }}</td>
                                        <td class="fw-semibold">{{ $item['saldo'] >= 0 ? '+' : '' }}{{ $item['saldo'] }}</td>
                                        <td class="fw-bold">{{ $item['pontos'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection