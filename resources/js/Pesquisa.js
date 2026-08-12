function pesquisar(inputId, cardsClass) { 
    const input = document.getElementById(inputId); // Busca o campo de pesquisa pelo ID
    const cards = document.querySelectorAll(`.${cardsClass}`); // Busca todos os cards pela classe
    if (!input) { // Verifica se o campo de pesquisa existe
        return; // Encerra a função
    }
    input.addEventListener('input', function () { // Executa quando o usuário digitar
        const pesquisa = this.value.toLowerCase(); // Pega o texto digitado e transforma em minúsculo
        cards.forEach(card => { // Percorre todos os cards
            const texto = card.textContent.toLowerCase(); // Pega todo o texto dentro do card
            if (texto.includes(pesquisa)) { // Verifica se o texto contém o que foi pesquisado
                card.style.display = ''; // Mostra o card
            } else {
                card.style.display = 'none'; // Esconde o card
            }
        });
    });
}

export { pesquisar }; // Permite usar a função em outro arquivo