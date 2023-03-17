<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Helper\ScrapeHelper;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ArticleRepository;
use App\Message\GetNewsMessage;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:get-news',
    description: 'Get News from site',
)]
class GetNewsCommand extends Command
{
    private $articleRepository;
    private $scrapeHelper;
    private $doctrine;
    private $bus;

    public function __construct(
        ArticleRepository $articleRepository,
        ScrapeHelper $scrapeHelper,
        ManagerRegistry $doctrine,
        MessageBusInterface $bus
    ) {
        parent::__construct();
        $this->articleRepository = $articleRepository;
        $this->scrapeHelper = $scrapeHelper;
        $this->doctrine = $doctrine;
        $this->bus = $bus;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->bus->dispatch(new GetNewsMessage());

        $io->success('Articles successfully downloaded');

        return Command::SUCCESS;
    }
}
