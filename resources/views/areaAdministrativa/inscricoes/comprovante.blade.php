<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Comprovante de Inscrição</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }

        .cabecalho {
            text-align: center;
            margin-bottom: 30px;
        }

        .titulo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitulo {
            color: #666;
        }

        .dados {
            margin-top: 20px;
        }

        .linha {
            margin-bottom: 12px;
        }

        .label {
            font-weight: bold;
        }

        .sucesso {
            text-align: center;
            margin: 30px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .rodape {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="cabecalho">
        <div class="titulo">
            COMPROVANTE DE INSCRIÇÃO
        </div>

        <div class="subtitulo">
            Sistema de Gerenciamento Esportivo

        </div>
        <div class="subtitulo">
            {{ $inscricao->created_at->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="sucesso">
        Inscrição realizada com sucesso!
    </div>

    <div class="dados">

        <div class="linha">
            {{ $inscricao->campeonato->nome }}
        </div>

        <div class="linha">
            <span class="label">Categoria:</span>
            {{ $inscricao->campeonato->categoria }}
        </div>

        <div class="linha">
            <span class="label">Equipe:</span>
            {{ $inscricao->equipe->nome }}
        </div>

        <div class="linha">
            <span class="label">Tipo do campeonato:</span>
            @if ($inscricao->campeonato->tipo == 'MATA_MATA')
                Mata-mata
            @elseif($inscricao->campeonato->tipo == 'MATA_MATA')
                Grupos + Mata-mata
            @else
                Pontos corridos
            @endif
        </div>

        <div class="linha">
            <span class="label">Data de início:</span>
            {{ date('d/m/Y', strtotime($inscricao->campeonato->data_inicio))}}
        </div>

        <div class="linha">
            <span class="label">Data de término:</span>
            {{ date('d/m/Y', strtotime($inscricao->campeonato->data_fim))}}
        </div>
    </div>

    <div class="rodape">
        Este documento comprova que a equipe foi inscrita no campeonato informado.
    </div>

</body>

</html>