function EquipesSumir(idStatus, idEquipe) {
    const status = document.getElementById(idStatus); // pega o elemento pelo ID
    const equipe = document.getElementById(idEquipe); // pega o elemento pelo ID

    // Verifica se o valor do status é ATIVO e deixa o campo de selecionar equipe visível
    if (status.value === 'ATIVO') {
        equipe.style.display = 'block';
    } else { // Senão, esconde
        equipe.style.display = 'none';
        document.getElementById('equipe').value = '';
    }
}


// Verifica se o combo foi alterado. Se houver alguma alteração, o campo de selecionar equipe pode aparecer ou desaparecer
document.getElementById('status').addEventListener('change', function () {
    EquipesSumir('status', 'divEquipe');
});

//chama a função em casos de edição que já carrega a página
EquipesSumir('status', 'divEquipe');
