<?php

namespace App\MessageHandler;

use App\Message\GetNewsMessage;
use App\Helper\ScrapeHelper;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ArticleRepository;

class GetNewsMessageHandler
{
    private $articleRepository;
    private $scrapeHelper;
    private $doctrine;

    public function __construct(
        ArticleRepository $articleRepository,
        ScrapeHelper $scrapeHelper,
        ManagerRegistry $doctrine
    ) {
        $this->articleRepository = $articleRepository;
        $this->scrapeHelper = $scrapeHelper;
        $this->doctrine = $doctrine;
    }

    public function __invoke(GetNewsMessage $message)
    {
        $arg1 = $message->getArg1();

        $this->scrapeHelper->getNodes(
            $this->scrapeHelper->fetchContent('https://highload.today/category/novosti/'),
            $this->doctrine,
            $this->articleRepository
        );
    }
}
