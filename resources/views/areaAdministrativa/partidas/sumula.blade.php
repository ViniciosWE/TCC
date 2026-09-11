<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Súmula - Partida {{ $partida->mandante->nome }} X {{ $partida->visitante->nome }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 10px;
        }

        .pagina {
            width: 100%;
            margin: 0 auto;
        }

        .pagina-2 {
            page-break-before: always;
        }

        .cabecalho {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .titulo {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .campeonato {
            font-size: 15px;
            font-weight: bold;
            margin-top: 4px;
        }

        /* Confronto */
        .confronto {
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 10px;
        }

        .confronto-table,
        .tabela,
        .comissao-table,
        .jogadores-table,
        .evento-table {
            width: 100%;
            border-collapse: collapse;
        }

        .confronto-table td {
            vertical-align: middle;
            text-align: center;
        }

        .equipe {
            width: 35%;
            height: 115px;
        }

        .logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            display: block;
            margin: 0 auto 5px auto;
        }

        .nome-equipe {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .placar-area {
            width: 30%;
            text-align: center;
            vertical-align: middle;
        }

        .resultado-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .placar {
            font-size: 30px;
            font-weight: bold;
            line-height: 1;
            margin: 0;
        }

        .campo-resultado {
            display: inline-block;
            width: 42px;
            height: 42px;
            border: 2px solid #000;
            vertical-align: middle;
        }

        .x {
            font-size: 20px;
            font-weight: bold;
            vertical-align: middle;
            margin: 0 4px;
        }

        .penaltis {
            margin-top: 10px;
            text-align: center;
        }

        .penaltis-titulo {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .penaltis-resultado {
            font-size: 14px;
            font-weight: bold;
            line-height: 1;
        }

        .campo-penaltis {
            display: inline-block;
            width: 28px;
            height: 25px;
            border: 2px solid #000;
            vertical-align: middle;
        }

        /* Títulos das seções */
        .secao {
            margin-top: 8px;
            margin-bottom: 0;
            padding: 5px 7px;
            border: 1px solid #000;
            background: #eeeeee;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Informações */
        .tabela th,
        .tabela td {
            border: 1px solid #000;
            padding: 3px 4px;
        }

        .tabela th {
            background: #eeeeee;
            font-weight: bold;
            text-align: center;
        }

        /* Comissão técnica */
        .comissao-table th,
        .comissao-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            height: 18px;
        }

        .comissao-table th {
            background: #eeeeee;
            text-align: center;
            font-size: 9px;
        }

        .comissao-funcao {
            width: 20%;
        }

        .comissao-nome {
            width: 35%;
        }

        .comissao-cpf {
            width: 20%;
        }

        .comissao-assinatura {
            width: 25%;
        }

        /* Jogadores */
        .jogadores-table th,
        .jogadores-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            height: 19px;
        }

        .jogadores-table th {
            background: #eeeeee;
            text-align: center;
            font-size: 9px;
        }

        .numero {
            width: 8%;
            text-align: center;
            font-weight: bold;
        }

        .jogador {
            width: 35%;
            text-transform: uppercase;
        }

        .cpf {
            width: 22%;
        }

        .assinatura-jogador {
            width: 35%;
        }

        .suspenso {
            background: #ffd6d6;
            color: #a00000;
            font-weight: bold;
        }

        .aviso-suspenso {
            display: block;
            font-size: 6px;
            font-weight: bold;
            color: #a00000;
            margin-top: 1px;
        }

        /* Eventos */
        .evento-table th,
        .evento-table td {
            border: 1px solid #000;
            padding: 3px 4px;
        }

        .evento-table th {
            background: #eeeeee;
            font-size: 9px;
            text-align: center;
        }

        .evento-table td {
            height: 25px;
        }

        .observacoes {
            width: 100%;
            height: 105px;
            border: 1px solid #000;
        }

        .assinaturas-arbitragem {
            width: 100%;
            margin-top: 35px;
        }

        .assinaturas-arbitragem td {
            width: 50%;
            text-align: center;
            padding: 0 20px;
        }

        .linha-arbitro {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 9px;
        }
    </style>
</head>

<body>
    {{-- Página 1 --}}
    <div class="pagina">
        <div class="cabecalho">
            <div class="titulo">Súmula da Partida</div>
            <div class="campeonato">{{ $partida->campeonato->nome }}</div>
        </div>
        {{-- Confronto --}}
        <div class="confronto">
            <table class="confronto-table">
                <tr>
                    <td class="equipe">
                        <img src="{{ public_path('storage/' . $partida->mandante->escudo) }}"
                            alt="Escudo {{ $partida->mandante->nome }}" class="logo">
                        <div class="nome-equipe">{{ $partida->mandante->nome }}</div>
                    </td>
                    <td class="placar-area">
                        <div class="resultado-label">Resultado</div>
                        {{-- Resultado sempre será preenchido manualmente --}}
                        <div class="placar">
                            <span class="campo-resultado"></span>
                            <span class="x">×</span>
                            <span class="campo-resultado"></span>
                        </div>
                        {{-- Pênaltis sempre serão preenchidos manualmente --}}
                        <div class="penaltis">
                            <div class="penaltis-titulo">Pênaltis</div>
                            <div class="penaltis-resultado">
                                <span class="campo-penaltis"></span>
                                <span class="x">×</span>
                                <span class="campo-penaltis"></span>
                            </div>
                        </div>
                    </td>
                    <td class="equipe">
                        <img src="{{ public_path('storage/' . $partida->visitante->escudo) }}"
                            alt="Escudo {{ $partida->visitante->nome }}" class="logo">
                        <div class="nome-equipe">
                            {{ $partida->visitante->nome }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        {{-- Informações da partida --}}
        <div class="secao">Informações da partida</div>
        <table class="tabela">
            <tr>
                <th style="width: 15%;">Data</th>
                <td style="width: 35%;">
                    @if ($partida->data_hora)
                        {{ date('d/m/Y', strtotime($partida->data_hora)) }}
                    @endif
                </td>
                <th style="width: 15%;">Horário</th>
                <td style="width: 35%;">
                    @if ($partida->data_hora)
                        {{ date('H:i', strtotime($partida->data_hora)) }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Local</th>
                <td colspan="3">{{ $partida->local ?? '' }}</td>
            </tr>
        </table>
        {{-- Comissão técnica do Mandante --}}
        @if ($comissaoMandante->count() > 0)
            <div class="secao">Comissão Técnica - {{ $partida->mandante->nome }}</div>
            <table class="comissao-table">
                <tr>
                    <th class="comissao-funcao">Função</th>
                    <th class="comissao-nome">Nome</th>
                    <th class="comissao-cpf">CPF</th>
                    <th class="comissao-assinatura">Ass.</th>
                </tr>
                @foreach ($comissaoMandante as $contrato)
                    @php $participante = $contrato->participante; @endphp
                    @if ($participante->status == 'SUSPENSO')
                        <tr class="suspenso">
                            <td>
                                @if ($participante->funcao == 'TECNICO') Técnico @endif
                                @if ($participante->funcao == 'AUXILIAR_TECNICO') Auxiliar Técnico @endif
                                @if ($participante->funcao == 'PREPARADOR_FISICO') Preparador Físico @endif
                            </td>
                            <td>
                                {{ $participante->nome }}
                                <span class="aviso-suspenso">NÃO PODE ATUAR - SUSPENSO</span>
                            </td>
                            <td>{{ $participante->cpf }}</td>
                            <td></td>
                        </tr>
                    @else
                        <tr>
                            <td>
                                @if ($participante->funcao == 'TECNICO') Técnico @endif
                                @if ($participante->funcao == 'AUXILIAR_TECNICO') Auxiliar Técnico @endif
                                @if ($participante->funcao == 'PREPARADOR_FISICO') Preparador Físico @endif
                            </td>
                            <td>{{ $participante->nome }}</td>
                            <td>{{ $participante->cpf }}</td>
                            <td></td>
                        </tr>
                    @endif
                @endforeach
            </table>
        @else
            <div class="secao">Comissão Técnica - {{ $partida->mandante->nome }}</div>
            <table class="comissao-table">
                <tr>
                    <th>Não possui comissão técnica</th>
                </tr>
            </table>
        @endif
        {{-- Comissão técnica do Visitante --}}
        @if ($comissaoVisitante->count() > 0)
            <div class="secao">Comissão Técnica - {{ $partida->visitante->nome }}</div>
            <table class="comissao-table">
                <tr>
                    <th class="comissao-funcao">Função</th>
                    <th class="comissao-nome">Nome</th>
                    <th class="comissao-cpf">CPF</th>
                    <th class="comissao-assinatura">Ass.</th>
                </tr>
                @foreach ($comissaoVisitante as $contrato)
                    @php $participante = $contrato->participante; @endphp
                    @if ($participante->status == 'SUSPENSO')
                        <tr class="suspenso">
                            <td>
                                @if ($participante->funcao == 'TECNICO') Técnico @endif
                                @if ($participante->funcao == 'AUXILIAR_TECNICO') Auxiliar Técnico @endif
                                @if ($participante->funcao == 'PREPARADOR_FISICO') Preparador Físico @endif
                            </td>
                            <td>
                                {{ $participante->nome }}
                                <span class="aviso-suspenso">NÃO PODE ATUAR - SUSPENSO</span>
                            </td>
                            <td>{{ $participante->cpf }}</td>
                            <td></td>
                        </tr>
                    @else
                        <tr>
                            <td>
                                @if ($participante->funcao == 'TECNICO') Técnico @endif
                                @if ($participante->funcao == 'AUXILIAR_TECNICO') Auxiliar Técnico @endif
                                @if ($participante->funcao == 'PREPARADOR_FISICO') Preparador Físico @endif
                            </td>
                            <td>{{ $participante->nome }}</td>
                            <td>{{ $participante->cpf }}</td>
                            <td></td>
                        </tr>
                    @endif
                @endforeach
            </table>
        @else
            <div class="secao">Comissão Técnica - {{ $partida->visitante->nome }}</div>
            <table class="comissao-table">
                <tr>
                    <th>Não possui comissão técnica</th>
                </tr>
            </table>
        @endif
        {{-- Jogadores do Mandante --}}
        <div class="secao">Jogadores - {{ $partida->mandante->nome }}</div>
        <table class="jogadores-table">
            <tr>
                <th class="numero">Nº</th>
                <th>Titular</th>
                <th class="jogador">Jogador</th>
                <th class="cpf">CPF</th>
                <th class="assinatura-jogador">Ass.</th>
            </tr>
            @foreach ($jogadoresMandante as $contrato)
                @php $participante = $contrato->participante; @endphp
                @if ($participante->status == 'SUSPENSO')
                    <tr class="suspenso">
                        <td class="numero">{{ $participante->numero }}</td>
                        <td></td>
                        <td class="jogador">
                            {{ $participante->nome }} -
                            @if ($participante->funcao == 'ALA_DIREITO')Ala Direito
                            @elseif($participante->funcao == 'ALA_ESQUERDO')Ala Esquerdo
                            @elseif($participante->funcao == 'GOLEIRO_LINHA')Goleiro Linha
                            @else{{ $participante->funcao }}
                            @endif
                            <span class="aviso-suspenso">NÃO PODE JOGAR - SUSPENSO</span>
                        </td>
                        <td class="cpf">{{ $participante->cpf }}</td>
                        <td class="assinatura-jogador"></td>
                    </tr>
                @else
                    <tr>
                        <td class="numero">{{ $participante->numero }}</td>
                        <td></td>
                        <td class="jogador">
                            {{ $participante->nome }} -
                            @if ($participante->funcao == 'ALA_DIREITO')Ala Direito
                            @elseif($participante->funcao == 'ALA_ESQUERDO')Ala Esquerdo
                            @elseif($participante->funcao == 'GOLEIRO_LINHA')Goleiro Linha
                            @else{{ $participante->funcao }}
                            @endif
                        </td>
                        <td class="cpf">{{ $participante->cpf }}</td>
                        <td class="assinatura-jogador"></td>
                    </tr>
                @endif
            @endforeach
        </table>
        {{-- Jogadores do Visitante --}}
        <div class="secao">Jogadores - {{ $partida->visitante->nome }}</div>
        <table class="jogadores-table">
            <tr>
                <th class="numero">Nº</th>
                <th>Titular</th>
                <th class="jogador">Jogador</th>
                <th class="cpf">CPF</th>
                <th class="assinatura-jogador">Ass.</th>
            </tr>
            @foreach ($jogadoresVisitante as $contrato)
                @php $participante = $contrato->participante; @endphp
                @if ($participante->status == 'SUSPENSO')
                    <tr class="suspenso">
                        <td class="numero">
                            {{ $participante->nome }} -
                            @if ($participante->funcao == 'ALA_DIREITO')Ala Direito
                            @elseif($participante->funcao == 'ALA_ESQUERDO')Ala Esquerdo
                            @elseif($participante->funcao == 'GOLEIRO_LINHA')Goleiro Linha
                            @else{{ $participante->funcao }}
                            @endif
                        </td>
                        <td></td>
                        <td class="jogador">
                            {{ $participante->nome }}
                            <span class="aviso-suspenso">NÃO PODE JOGAR - SUSPENSO</span>
                        </td>
                        <td class="cpf">{{ $participante->cpf }}</td>
                        <td class="assinatura-jogador"></td>
                    </tr>
                @else
                    <tr>
                        <td class="numero">{{ $participante->numero }}</td>
                        <td></td>
                        <td class="jogador">
                            {{ $participante->nome }} -
                            @if ($participante->funcao == 'ALA_DIREITO')Ala Direito
                            @elseif($participante->funcao == 'ALA_ESQUERDO')Ala Esquerdo
                            @elseif($participante->funcao == 'GOLEIRO_LINHA')Goleiro Linha
                            @else{{ $participante->funcao }}
                            @endif
                        </td>
                        <td class="cpf">{{ $participante->cpf }}</td>
                        <td class="assinatura-jogador"></td>
                    </tr>
                @endif
            @endforeach
        </table>
    </div>
    {{-- página 2--}}
    <div class="pagina pagina-2">
        <div class="cabecalho">
            <div class="titulo">Eventos da Partida</div>
            <div class="campeonato">{{ $partida->mandante->nome }} × {{ $partida->visitante->nome }}</div>
        </div>
        {{-- Gols --}}
        <div class="secao">Gols</div>
        <table class="evento-table">
            <tr>
                <th style="width: 12%;">Tempo</th>
                <th style="width: 22%;">Equipe</th>
                <th style="width: 10%;">Nº</th>
                <th style="width: 25%;">Jogador</th>
                <th>Observação</th>
            </tr>
            @for ($i = 0; $i < 12; $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </table>
        {{-- Assistências --}}
        <div class="secao">Assistências</div>
        <table class="evento-table">
            <tr>
                <th style="width: 12%;">Tempo</th>
                <th style="width: 22%;">Equipe</th>
                <th style="width: 10%;">Nº</th>
                <th style="width: 30%;">Jogador</th>
                <th>Observação</th>
            </tr>
            @for ($i = 0; $i < 12; $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </table>
        {{-- Substituição de goleiro --}}
        <div class="secao">Substituição de Goleiro</div>
        <table class="evento-table">
            <tr>
                <th style="width: 12%;">Tempo</th>
                <th style="width: 22%;">Equipe</th>
                <th style="width: 22%;">Goleiro Sai</th>
                <th style="width: 22%;">Goleiro Entra</th>
                <th>Observação</th>
            </tr>
            @for ($i = 0; $i < 3; $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </table>
        {{-- Pênaltis --}}
        <div class="secao">Pênaltis de decisão</div>
        <table class="evento-table">
            <tr>
                <th style="width: 25%;">Equipe</th>
                <th style="width: 12%;">Nº</th>
                <th style="width: 28%;">Jogador</th>
                <th style="width: 15%;">Convertido</th>
                <th>Observação</th>
            </tr>
            @for ($i = 0; $i < 12; $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </table>
        {{-- Cartões --}}
        <div class="secao">Cartões</div>
        <table class="evento-table">
            <tr>
                <th style="width: 12%;">Tempo</th>
                <th style="width: 22%;">Equipe</th>
                <th style="width: 10%;">Nº</th>
                <th style="width: 25%;">Jogador</th>
                <th style="width: 15%;">Cartão</th>
                <th>Observação</th>
            </tr>
            @for ($i = 0; $i < 7; $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </table>
        {{-- Observações --}}
        <div class="secao">Observações da Arbitragem</div>
        <div class="observacoes"></div>
        {{-- Assinaturas --}}
        <table class="assinaturas-arbitragem">
            <tr>
                <td>
                    <div style="height: 35px;"></div>
                    <div class="linha-arbitro">Assinatura do Árbitro 1</div>
                </td>

                <td>
                    <div style="height: 35px;"></div>
                    <div class="linha-arbitro">Assinatura do Árbitro 2</div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>