function removerMensagem(id, tempo) {
    /*Buca o elemento pelo o id*/
    const elemento = document.getElementById(id);

    /*Verifica se o elemento existe e remove */
    if (elemento) {
        setTimeout(() => elemento.remove(), tempo);
    }
}


removerMensagem('sumirMensagem', 3000);
