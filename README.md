# CRUD #
Exemplo de PHP crud em MVC orientado a objetos

### 1º passo  ###
Lembre-se de executar o comando para instalar as dependências
```sh
composer install
```

### 2º passo  ###
Após criar a pasta com os arquivos necessários para rodar seu sistema, você precisa atualizar seu config, ele está localizado em `source/Boot/Config.php` preencha todas as informações de acordo com seu ambiente.

### 3º passo  ###
Execute o arquivo `database.sql` na raiz do projeto para criar o banco de dados.

### *Resultado no link abaixo* ###
https://crud-01.000webhostapp.com/crud-01/users

#### Estrutura banco de dados  ####

`crud`
- `users->id` - (INT)
- `users->name` - (VARCHAR 50)
- `users->email` - (VARCHAR 50)