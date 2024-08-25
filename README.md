# Projeto de Contatos

## Descrição

Este projeto é uma aplicação de gerenciamento de contatos utilizando o framework CodeIgniter 4. A aplicação permite criar, ler, atualizar e excluir contatos, além de gerenciar suas informações associadas, como endereços, números de telefone e e-mails.

## Funcionalidades

- **Visualizar Contatos**: Lista todos os contatos com suas informações associadas.
- **Criar Contato**: Adiciona um novo contato com informações de nome, descrição, endereço, telefone e e-mail.
- **Atualizar Contato**: Atualiza as informações de um contato existente.
- **Excluir Contato**: Remove um contato e suas informações associadas.

## Tecnologias Utilizadas

- **Framework**: CodeIgniter 4
- **Banco de Dados**: MySQL
- **Cache**: CodeIgniter Cache
- **Validação**: CodeIgniter Validation
- **API Externa**: ViaCep (para validação e busca de endereços)

## Requisitos

- PHP 7.4 ou superior
- MySQL
- Composer
- CodeIgniter 4

## Instalação

1. **Clone o Repositório**:
   ```bash
   git clone https://github.com/gustahenry/hbi-api.git
   cd hbi-api

2. **Instale as Dependências**:
   ```bash
   composer install

3. **Configure o Banco de Dados**:

- Copie o arquivo .env.example para um novo arquivo .env:
   ```bash
   cp .env.example .env

- Abra o arquivo .env em um editor de texto e configure as seguintes variáveis para conectar ao seu banco de dados:

 ```bash
   # Configurações do Banco de Dados
    database.default.hostname = localhost
    database.default.database = seu_banco_de_dados
    database.default.username = seu_usuario
    database.default.password = sua_senha
    database.default.DBDriver = MySQLi  # Altere para o driver de banco de dados que você está usando
```
- database.default.hostname: O hostname do servidor de banco de dados (por exemplo, localhost ou o IP do servidor).
- database.default.database: O nome do banco de dados que você criou para o projeto.
- database.default.username: O nome de usuário para acessar o banco de dados.
- database.default.password: A senha correspondente ao nome de usuário.
- database.default.DBDriver: O driver do banco de dados utilizado (por exemplo, MySQLi, Postgre, SQLite, etc.).

4. **Crie as Tabelas no Banco de Dados**:

Você pode excultar o comando:

```bash
php spark db:create
```
que ira gerar o banco de dados e rodar as migrates, caso ja tenha o banco de dados criado pode usar o:

```bash
php spark migrate
```

4. **Inicie o Servidor de Desenvolvimento**:

Use o comando abaixo para iniciar o servidor local de desenvolvimento:

```bash
php spark serve
```

A aplicação estará disponível em http://localhost:8080.

# Documentação da API

Você pode visualizar a documentação da API no Postman [aqui](https://documenter.getpostman.com/view/32948611/2sAXjF9uoW).



