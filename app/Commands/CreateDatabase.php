<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class CreateDatabase extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'db:create';
    protected $description = 'Cria o banco de dados, caso não exista, e efetua as migrações.';

    public function run(array $params)
    {
        // Conectar ao banco de dados usando as configurações do arquivo Database.php
        $dbConfig = Database::connect();
        $databaseName = $dbConfig->getDatabase();

        // Criar o banco de dados se ele não existir
        $dbConnection = new \mysqli($dbConfig->hostname, $dbConfig->username, $dbConfig->password);

        if ($dbConnection->connect_error) {
            CLI::error('Erro ao conectar ao MySQL: ' . $dbConnection->connect_error);
            return;
        }

        if ($dbConnection->query('CREATE DATABASE IF NOT EXISTS `' . $databaseName . '`')) {
            CLI::write('Banco de dados `' . $databaseName . '` foi criado ou já existe.', 'green');
        } else {
            CLI::error('Erro ao criar o banco de dados: ' . $dbConnection->error);
            $dbConnection->close();
            return;
        }

        $dbConnection->close();

        // Executar as migrações
        CLI::write('Executando migrações...', 'yellow');
        chdir(ROOTPATH);
        exec('php spark migrate', $output, $returnVar);

        // Mostrar a saída do comando de migração
        CLI::write(implode("\n", $output), $returnVar === 0 ? 'green' : 'red');
    }
}
