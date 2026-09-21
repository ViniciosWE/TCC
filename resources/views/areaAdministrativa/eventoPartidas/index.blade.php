@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Eventos das Partidas')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="mb-4">
            <h2 class="fw-bold mb-1">Gerenciar Eventos das Partidas</h2>
        </div>
        {{-- Mensagem de sucesso --}}
        @if(session('success'))
            <div class="alert alert-success" id="sumirMensagem">
                {{ session('success') }}
            </div>
        @endif
        {{-- Mensagem de erro --}}
        @if(session('error'))
            <div class="alert alert-danger" id="sumirMensagem">
                {{ session('error') }}
            </div>
        @endif
        {{-- Campo de pesquisa --}}
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="pesquisaEventosPartidas"
                    placeholder="Pesquisar evento nas partidas...">
            </div>
        </div>
        {{-- Cards dos eventos das partidas --}}
        <div class="row g-4">
            @forelse($eventoPartidas as $eventoPartida)
                <div class="col-12 col-md-6 col-xl-4 eventoPartida-card">
                    <div class="card shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                        {{-- informações dos eventos --}}
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="mb-1">
                                    <strong class="text-capitalize">
                                        {{ $eventoPartida->partida->mandante->nome }}
                                        ×
                                        {{ $eventoPartida->partida->visitante->nome }}
                                    </strong>
                                </div>

                                <div>
                                    <small>Participante:</small>
                                    <strong>
                                        {{ $eventoPartida->participante->nome }}
                                    </strong>
                                </div>

                                <div>
                                    <small>CPF:</small>
                                    <small>
                                        {{ $eventoPartida->participante->cpf }}
                                    </small>
                                </div>
                                <div class="d-flex gap-1">
                                    <small class="text-muted d-block">Tempo:</small>
                                    <strong> {{$eventoPartida->tempo }}</strong>
                                </div>
                                <div class="d-flex gap-1">
                                    <small class="text-muted d-block">Evento: </small>
                                    @if ($eventoPartida->tipo == 'GOL')
                                        <span class="badge bg-success"></i>Gol</span>
                                    @elseif ($eventoPartida->tipo == 'GOL_CONTRA')
                                        <span class="badge bg-danger"></i>Gol Contra</span>
                                    @elseif ($eventoPartida->tipo == 'ASSISTENCIA')
                                        <span class="badge bg-primary">Assistência</span>
                                    @elseif ($eventoPartida->tipo == 'CARTAO_AMARELO')
                                        <span class="badge bg-warning text-dark">CartãoAmarelo</span>
                                    @elseif ($eventoPartida->tipo == 'CARTAO_VERMELHO')
                                        <span class="badge bg-danger"><i class="bi bi-square-fill"></i>Cartão Vermelho</span>
                                    @elseif ($eventoPartida->tipo == 'GOLS_SOFRIDOS')
                                        <span class="badge bg-secondary">Gols Sofridos</span>
                                    @elseif ($eventoPartida->tipo == 'PENALTI_CONVERTIDO_DESEMPATE')
                                        <span class="badge bg-dark"> Pênalti convertido (desempate)</span>
                                    @elseif ($eventoPartida->tipo == 'TITULAR')
                                        <span class="badge bg-info">Titular</span>
                                    @elseif ($eventoPartida->tipo == 'ENTRADA_GOLEIRO')
                                        <span class="badge bg-primary">Entrada do Goleiro</span>
                                    @elseif ($eventoPartida->tipo == 'SAIDA_GOLEIRO')
                                        <span class="badge bg-secondary">Saída do Goleiro</span>
                                    @endif
                                </div>
                            </div>
                            {{-- Botões --}}
                            <div class="d-flex gap-2">
                                <a href="{{ route('eventoPartidas.edit', $eventoPartida) }}" class="btn btn-warning flex-fill">
                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                </a>
                                <form action="{{ route('eventoPartidas.destroy', $eventoPartida) }}" method="POST"
                                    class="flex-fill">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Tem certeza que deseja excluir este evento? Esta ação removerá o evento da partida e poderá alterar o placar e o resultado final. Essa ação não poderá ser desfeita.')">
                                        <i class="bi bi-trash me-1"></i>Excluir
                                    </button>
                                </form>

                            </div>

                        </div>
                    </div>
                </div>
            @empty
                {{-- Mensagem exibida quando não houver contratos --}}
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Nenhum Evento de partida cadastrado.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection