function EquipesSumir(idStatus, idEquipe) { 
    const status = document.getElementById(idStatus); // Busca o campo de status
    const equipe = document.getElementById(idEquipe); // Busca o campo da equipe
    if (!status || !equipe) { // Verifica se algum campo não existe
        return; // Encerra a função
    }
    if (status.value === 'ATIVO') { // Verifica se o status é ATIVO
        equipe.style.display = 'block'; // Mostra o campo da equipe
    } else { // Caso o status não seja ATIVO
        equipe.style.display = 'none'; // Esconde o campo da equipe
        const campoEquipe = document.getElementById('equipe'); // Busca o campo equipe
        if (campoEquipe) { // Verifica se o campo equipe existe
            campoEquipe.value = ''; // Limpa o valor do campo
        }
    }
}

export { EquipesSumir }; // Permite usar a função em outro arquivo