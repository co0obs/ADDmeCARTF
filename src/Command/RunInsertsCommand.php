<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:run-inserts',
    description: 'Runs the inserts.sql file to migrate data to Postgres.',
)]
class RunInsertsCommand extends Command
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sqlPath = __DIR__ . '/../../inserts.sql';
        
        if (!file_exists($sqlPath)) {
            $output->writeln("Error: inserts.sql not found at $sqlPath");
            return Command::FAILURE;
        }

        $sql = file_get_contents($sqlPath);
        
        // Split by semicolon and run each query
        $queries = explode(";\n", $sql);
        
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $this->connection->executeStatement($query);
            }
        }

        $output->writeln("Successfully migrated all data from inserts.sql to Postgres!");

        return Command::SUCCESS;
    }
}
