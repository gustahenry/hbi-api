<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class CreateDatabase extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'db:create';
    protected $description = 'Cria o banco de dados padrão, caso não exista, e executa as migrações.';

    public function run(array $params)
    {

        $dbConfigDefault = Database::connect('default');
        
        $this->createDatabaseIfNotExists($dbConfigDefault);

        CLI::write('Executando migrações para o banco de dados padrão...', 'yellow');
        $this->runMigrations('default');
    }

    private function createDatabaseIfNotExists($dbConfig)
    {
        $databaseName = $dbConfig->getDatabase();

        $dbConnection = new \mysqli($dbConfig->hostname, $dbConfig->username, $dbConfig->password);

        if ($dbConnection->connect_error) {
            CLI::error('Erro ao conectar ao MySQL: ' . $dbConnection->connect_error);
            return;
        }

        if ($dbConnection->query('CREATE DATABASE IF NOT EXISTS `' . $databaseName . '`')) {
            CLI::write('Banco de dados `' . $databaseName . '` foi criado ou já existe.', 'green');
        } else {
            CLI::error('Erro ao criar o banco de dados: ' . $dbConnection->error);
        }

        $dbConnection->close();
    }

    private function runMigrations($group)
    {
        CLI::write("Executando migrações para o grupo '$group'...", 'yellow');
        chdir(ROOTPATH);
        exec('php spark migrate --group ' . $group, $output, $returnVar);

        CLI::write(implode("\n", $output), $returnVar === 0 ? 'green' : 'red');
    }
}
