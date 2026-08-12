function removerMensagem(id, tempo) { 
    const elemento = document.getElementById(id); // Busca o elemento pelo ID
    if (elemento) { // Verifica se o elemento existe
        setTimeout(() => elemento.remove(), tempo); // Remove o elemento depois do tempo informado
    }
}
export { removerMensagem }; // Permite usar a função em outro arquivo