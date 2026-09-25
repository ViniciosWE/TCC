function iconeSenha(senha, senhaIcone) {
    const password = document.getElementById(senha); // busca id
    const icon = document.getElementById(senhaIcone); // busca id
    //evento de clicar
    password.parentElement.querySelector('button').addEventListener('click', function () {
        //verifica se a senha está escondida se estiver mostra
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else { //senão esconde
            password.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
}

export { iconeSenha };  // Permite usar a função em outro arquivo