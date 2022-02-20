# CRUD #
Exemplo de PHP crud em MVC orientado a objetos

### 1º passo  ###
Estamos ultilizando composer, lembre de rodar o comando 
```sh
composer update
```
 para atualizar as dependencias e criar a pasta vendor.

### 2º passo  ###
Apos criado a pasta vendo com os arquivos necessarios para rodar seu sistema, voçe precisa atualizar seu Config, esta localizado em `source/Boot/Config.php` preencha todas as informações de acordo com seu ambiente.
 rendering.

### 3º passo  ###
Execute o arquivo `database.sql` na raiz do projeto para criar o banco de dados.

#### Estrutura banco de dados  ####

`crud`
- `users->id` - (INT)
- `users->name` - (VARCHAR 50)
- `users->email` - (VARCHAR 50)