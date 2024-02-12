<?php

namespace App\Command;

use App\NetatmoApi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-reachable',
    description: 'Check if smart valves are reachable',
)]
class CheckReachableCommand extends Command
{
    public function __construct(
        private readonly NetatmoApi $api,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Check if smart valves are reachable');

        $this->api->getStatus();

        return Command::SUCCESS;
    }
}
