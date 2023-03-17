<?php
namespace App\Helper;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ArticleRepository;
use Symfony\Component\DomCrawler\Crawler;
use Stichoza\GoogleTranslate\GoogleTranslate;

class ScrapeHelper
{
    public function fetchContent($url): String
    {
        $user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
        $options = array(
            CURLOPT_CUSTOMREQUEST  =>"GET",
            CURLOPT_POST           =>false,
            CURLOPT_USERAGENT      => $user_agent,
            CURLOPT_COOKIEFILE     =>"cookie.txt",
            CURLOPT_COOKIEJAR      =>"cookie.txt",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_MAXREDIRS      => 10,
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        curl_errno( $ch );
        curl_error( $ch );
        curl_getinfo( $ch );
        curl_close( $ch );

        $data = curl_exec( $ch );
        return $data;
    }
    public function getNodes($data, ManagerRegistry $doctrine=null, ArticleRepository $articleRepository=null)
    {
        $crawler = new Crawler($data);
        $entityManager = $doctrine->getManager();
        $tr = new GoogleTranslate('en');

        for($i=2; $i<count($crawler->filterXPath('//div[contains(@class, "lenta-item")]'))-1; $i++) {
            $title = $tr->translate($crawler->filter('#main > div > div.col.sidebar-center > div:nth-child('. $i .') > a:nth-child(3) > h2')->text());
            $description = $tr->translate($crawler->filter('#main > div > div.col.sidebar-center > div:nth-child('. $i .') > p')->text());
            $blogDate = mb_substr($crawler->filter('#main > div > div.col.sidebar-center > div:nth-child('. $i .') > span.meta-datetime')->text(), 0, 1);
            $datetime = date_create(date('Y-m-d H:i:s', strtotime('-'. $blogDate . ' hour')));
            $image = $crawler->filter('#main > div > div.col.sidebar-center > div:nth-child('. $i .') > a:nth-child(5) > div > img')->attr('data-lazy-src');

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
                $articleToUpdate->setNote('The last date updated is ' . $isArticleAlreadyInDatabase->getDateAdded()->format('d/m/Y'));
                $articleToUpdate->setLastUpdate(new \DateTime());
                $entityManager->flush();
                continue;
            }

            $entityManager->persist($article);
            $entityManager->flush();
            echo 'Successfully added with id '. $article->getId() . "\r\n";
        }
    }
}


