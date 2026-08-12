import './bootstrap';
import 'bootstrap';

import { removerMensagem } from './SumirMensagem';
import { EquipesSumir } from './EquipesSumir';
import { configurarDatalist } from './PesquisaDataList';
import { apenasNumeros } from './ApenasNumeros';
import { pesquisar } from './Pesquisa';


document.addEventListener('DOMContentLoaded', function () {
    apenasNumeros('cpf');
    apenasNumeros('numero');


    const status = document.getElementById('status'); // Busca o campo de status pelo ID
    if (status) { // Verifica se o campo status existe na página
        status.addEventListener('change', function () { // Executa quando o status for alterado
            EquipesSumir('status', 'divEquipe'); // Mostra ou esconde o campo equipe
        });
        EquipesSumir('status', 'divEquipe'); // Executa a função ao carregar a página
    }

    configurarDatalist('equipe_nome', 'equipe_id', 'lista-equipes');
    configurarDatalist('participante_nome', 'participante_id', 'lista-participantes');

    removerMensagem('sumirMensagem', 3000);
    pesquisar('pesquisaEquipe', 'equipe-card');
    pesquisar('pesquisaParticipantes', 'participantes-card');
    pesquisar('pesquisaContrato', 'contrato-card');
});