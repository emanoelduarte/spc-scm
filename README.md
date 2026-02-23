## Requisitos

* PHP 8.3 ou superior;
* MySQL 8.0 ou superior;
* Composer;

## Como rodar o projeto baixado

Instalar as dependências.
```
composer install
```
Executar as migrations.
```
vendor/bin/phinx migrate -c database/phinx.php
```
Executar as seed
```
vendor/bin/phinx seed:run -c database/phinx.php
```

uplicar o arquivo ".env.exemple" e renomear para ".env"<br>
Alterar no arquivo .env as credênciais do banco de dados.<br>

Instalar a biblioteca gerenciar variáveis de ambiente
```
composer require vlucas/phpdotenv
```

## Sequencia para criar o projeto
Criar o arquivo composer.jason com a instrução básica
```
composer init
```
Instalar a dependência Monolo, biblioteca PHP que permite criar arquivo de log.
```
composer require monolog/monolog
```
Instalar a biblioteca gerenciar variáveis de ambiente
```
composer require vlucas/phpdotenv
```

Instalar a biblioteca para criar/executar migrations e seed.
```
composer require robmorgan/phinx
```
Criar arquivo "phinx.php" com as configurações e alterar as mesmas
```
vendor/bin/phinx init -f php
```

Testar as configurações
```
vendor/bin/phinx test
```

Criar o diretório database.
```
mkdir database/
```

Criar o diretório para migrations.
```
mkdir database/migrations/
```

Criar a migrations.
```
vendor/bin/phinx create AdmsUsers -c database/phinx.php
```

Executar as migrations.
```
vendor/bin/phinx migrate -c database/phinx.php
```
Executar um rollback na ultima migration - reverter alterações realizadas
```
vendor/bin/phinx rollback -c database/phinx.php
```

Criar o diretório para seed
```
mkdir database/seeds/
```
Criar seed (arquivo)
```
vendor/bin/phinx seed:create AddAdmsUsers -c database/phinx.php
```
Executar as seed
```
vendor/bin/phinx seed:run -c database/phinx.php
```

## Como usar o GitHub
Baixar os arquivos do Git.
```
git clone --branch <branch_name> <repository_url> .
```

Definir as configurações do usuario.
```
git config --local user.name <Emanoel>
```
```
git config --local user.email <emanoel.c.duarte@hotmail.com>
```

Verificar a branch que estamos trabalhando
```
git branch
```

## Lista de erros 
001 - DbConnection.php - Erro de conexão com o banco de dados
002 - LoadPageAdm.php - Página não encontrada
003 - LoadPageAdm.php - Controller não encontrada
004 - LoadPageAdm.php - Método não encontrada

