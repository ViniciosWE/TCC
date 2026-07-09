## 🚀 Como Executar o Projeto

### 1. Clonar o repositório

```bash
git clone https://github.com/ViniciosWE/TCC.git
cd TCC
```

---

### 2. Instalar dependências do PHP

```bash
composer install
```

---

### 3. Instalar o Node.js (caso ainda não esteja instalado)

Este projeto utiliza o Vite para gerenciar os arquivos CSS e JavaScript do Laravel.

Baixe e instale a versão LTS do Node.js:

https://nodejs.org/

Após a instalação, verifique se ocorreu corretamente executando:

```bash
node -v
npm -v
```

---

### 4. Instalar dependências do frontend

```bash
npm install
```

---

### 5. Criar o arquivo de ambiente

```bash
cp .env.example .env
```

---

### 6. Configurar banco de dados

Edite o arquivo `.env`:

```env
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

---

### 7. Gerar chave da aplicação

```bash
php artisan key:generate
```

---

### 8. Rodar migrations e seeders

```bash
php artisan migrate:fresh --seed
```

---

### 9. Iniciar o servidor do Vite

Durante o desenvolvimento é necessário executar:

```bash
npm run dev
```

Esse comando é responsável por carregar e atualizar automaticamente os arquivos CSS e JavaScript da aplicação.

---

### 10. Iniciar o servidor Laravel

```bash
php artisan serve
```

Acesse:

```text
http://127.0.0.1:8000
```
