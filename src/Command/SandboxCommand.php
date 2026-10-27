<?php

// src/Command/MercureTestCommand.php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsCommand(name: 'app:mercure:test')]
class SandboxCommand extends Command
{
    public function __construct(private HubInterface $hub)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->hub->publish(new Update('test', 'Hello Mercure'));
        $output->writeln('Publié !');
        return Command::SUCCESS;
    }
}
