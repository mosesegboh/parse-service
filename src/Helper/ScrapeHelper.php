<?php
namespace App\Helper;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ArticleRepository;
use Symfony\Component\DomCrawler\Crawler;
use Stichoza\GoogleTranslate\GoogleTranslate;
use GuzzleHttp\Client;

class ScrapeHelper
{
    private $httpClient;
    private $tr;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 120,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0'
            ]
        ]);
        $this->tr = new GoogleTranslate('en');
    }

    public function fetchContent($url): String
    {
        $response = $this->httpClient->get($url);
        return (string) $response->getBody();
    }

    public function getNodes($data, ManagerRegistry $doctrine=null, ArticleRepository $articleRepository=null)
    {
        $crawler = new Crawler($data);

        for($i=2; $i<count($crawler->filterXPath('//div[contains(@class, "lenta-item")]'))-1; $i++) {
            $title = $this->tr->translate($crawler->filter('#main > div > div.col.sidebar-center > div:nth-child('. $i .') > a:nth-child(3) > h2')->text());
            $description = $this->tr->translate($crawler->filter('#main > div > div.col.sidebar-center > div:nth-child('. $i .') > p')->text());
            $blogDate = mb_substr($crawler->filter('#main > div > div.col.sidebar-center > div:nth-child('. $i .') > span.meta-datetime')->text(), 0, 1);
            $datetime = date_create(date('Y-m-d H:i:s', strtotime('-'. $blogDate . ' hour')));
            $image = $crawler->filter('#main > div > div.col.sidebar-center > div:nth-child('. $i .') > a:nth-child(5) > div > img')->attr('data-lazy-src');

            $this->processArticle($title, $description, $image, $datetime, $doctrine, $articleRepository);
        }
    }

    private function processArticle($title, $description, $image, $datetime, ManagerRegistry $doctrine, ArticleRepository $articleRepository)
    {
        $entityManager = $doctrine->getManager();

        $article = new Article();
        $article->setTitle($title);
        $article->setDescription($description);
        $article->setPicture($image);
        $article->setDateAdded($datetime);
        $article->setNote('');
        $article->setLastUpdate(new \DateTime());

        $isArticleAlreadyInDatabase = $articleRepository->findOneByTitle($title);
        if ($isArticleAlreadyInDatabase) {
            $articleToUpdate = $entityManager->getRepository(Article::class)->find($isArticleAlreadyInDatabase->getId());
            $articleToUpdate->setNote('The last date updated is ' . $isArticleAlreadyInDatabase->getLastUpdate()->format('d/m/Y'));
            $articleToUpdate->setLastUpdate(new \DateTime());
            $entityManager->persist($articleToUpdate);
            $entityManager->flush();
        } else {
            $entityManager->persist($article);
            $entityManager->flush();
            echo 'Successfully added with id ' . $article->getId() . "\r\n";
        }
    }
}
