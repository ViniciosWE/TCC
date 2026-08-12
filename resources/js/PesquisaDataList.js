function configurarDatalist(inputId, hiddenId, datalistId) { 
    const input = document.getElementById(inputId); // Busca o campo de pesquisa
    const hidden = document.getElementById(hiddenId); // Busca o campo hidden
    const options = document.querySelectorAll(`#${datalistId} option`); // Busca as opções do DataList
    if (!input || !hidden) { // Verifica se os campos existem
        return; // Encerra a função
    }
    input.addEventListener('input', () => { // Executa quando o usuário digitar
        hidden.value = ''; // Limpa o valor do campo hidden
        options.forEach(option => { // Percorre todas as opções do DataList
            if (option.value === input.value) { // Verifica se encontrou a opção digitada
                hidden.value = option.dataset.id; // Coloca o ID da opção no campo hidden
            }
        });
    });
}
export { configurarDatalist }; // Permite usar a função em outro arquivo