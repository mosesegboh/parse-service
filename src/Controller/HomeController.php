<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;

class HomeController extends AbstractController
{
    private $articleRepository;
    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    #[Route('/home', name: 'app_home')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $this->articleRepository->createQueryBuilder('a')
            ->orderBy('a.dateAdded', 'DESC');
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('home/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_article')]
    public function deleteArticle($id, ManagerRegistry $doctrine)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'You are not authorized to delete any post');
            return $this->redirectToRoute('app_home');
        }

        $entityManager = $doctrine->getManager();

        $entity = $this->articleRepository->find($id);
        $entityManager->remove($entity);
        $entityManager->flush();
        $this->addFlash('success', 'Article was successfully deleted!!');
        return $this->redirectToRoute('app_home');
    }
}
