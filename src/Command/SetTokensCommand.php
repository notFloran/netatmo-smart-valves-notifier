<?php

namespace App\Command;

use App\NetatmoApi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:set-tokens',
    description: 'Set API tokens',
)]
class SetTokensCommand extends Command
{
    public function __construct(
        private readonly NetatmoApi $api,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            >$this->setHelp('Get tokens from https://dev.netatmo.com/apps/')
            ->addArgument('accessToken', InputArgument::REQUIRED, 'Access token')
            ->addArgument('refreshToken', InputArgument::REQUIRED, 'Refresh token')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Set tokens');

        $this->api->setTokens(
            $input->getArgument('accessToken'),
            $input->getArgument('refreshToken'),
        );

        return Command::SUCCESS;
    }
}
