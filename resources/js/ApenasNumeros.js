function apenasNumeros(inputId) { 
    const input = document.getElementById(inputId); // Busca o campo pelo ID
    if (!input) { // Verifica se o campo não existe
        return; // Encerra a função
    }
    input.addEventListener('input', function () { // Executa quando o usuário digitar
        this.value = this.value.replace(/[^0-9]/g, ''); // Remove tudo que não for número
    });
}

export { apenasNumeros }; // Permite usar a função em outro arquivo