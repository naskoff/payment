<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {

        $query = $postRepository->createQueryBuilder('p')
            ->addOrderBy('p.createdAt', 'DESC');

        $pagination = $paginator->paginate($query, $request->query->get('page', 1), 9);

        return $this->render('home/index.html.twig', [
            'pagination' => $pagination
        ]);
    }
}
