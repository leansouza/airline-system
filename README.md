# Sistema de Passagens Aéreas

Este é um projeto de um sistema de passagens aéreas desenvolvido em Laravel.

## Requisitos

- PHP >= 7.3
- Composer
- MySQL
- Node.js & NPM

## Instalação

### Passo 1: Clonar o Repositório

```bash
git clone https://github.com/seu-usuario/sistema-passagens-aereas.git
cd sistema-passagens-aereas
```

### Passo 2: Instalar Dependências
#### Instale as dependências do PHP e do Node.js:
```bash
composer install
npm install
```

### Passo 3: Configurar o Ambiente
#### Copie o arquivo .env.example para .env:
```bash
cp .env.example .env
```

### Passo 4: Gerar a Chave da Aplicação
```bash
php artisan key:generate
```

### Passo 5: Configurar o Banco de Dados
#### No arquivo .env, configure as informações do seu banco de dados
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_passagens_aereas
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### Passo 6: Migrar o Banco de Dados
```bash
php artisan migrate
```

### Passo 7: Seed do Banco de Dados
```bash
php artisan db:seed
```

### Passo 8: Iniciar o Servidor de Desenvolvimento
```bash
php artisan serve
```

## Autenticação

O sistema utiliza o Laravel Sanctum para autenticação. Certifique-se de configurar o Sanctum conforme a documentação oficial.

Publicar Configurações do Sanctum
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

Executar as Migrações do Sanctum
```bash
php artisan migrate
```

## Documentação da API

A documentação da API está disponível através do Swagger. Para acessar a documentação, inicie o servidor e acesse /api/documentation.

## Deploy em Produção

Para subir o projeto em um servidor de produção, siga os passos abaixo:

### Passo 1: Configurar o Ambiente de Produção

Certifique-se de configurar as variáveis de ambiente no arquivo .env para produção.

### Passo 2: Otimizar a Aplicação

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Passo 3: Configurar um Servidor Web

Configure o servidor web (Apache/Nginx) para apontar para o diretório public do seu projeto Laravel.

## Suporte

Para qualquer dúvida ou problema, entre em contato através do email <leandersonssouza1@gmail.com>.
