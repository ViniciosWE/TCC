## 🚀 Como Executar o Projeto

1. Clonar o repositório
```
git clone https://github.com/ViniciosWE/TCC.git
cd TCC
```
---
2. Instalar dependências
```
composer install
```
---
3. Criar o arquivo de ambiente
```
cp .env.example .env
```
---
4. Configurar banco de dados
   
    Edite o arquivo .env:
```
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```
---
5. Gerar chave da aplicação
```
php artisan key:generate
```
---
6. Rodar migrations e seeders
```
php artisan migrate:fresh --seed
```
---
7. Iniciar o servidor
```
php artisan serve
```
   Acesse:

```
http://127.0.0.1:8000
```
