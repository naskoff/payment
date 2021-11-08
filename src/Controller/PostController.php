<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Form\PostType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    /**
     * @Route("/create", name="create_post")
     */
    public function index(Request $request): Response
    {

        $form = $this->createForm(PostType::class, new Post());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData()
                ->setUser($this->getUser())
                ->setCreatedAt(new DateTime());

            $this->getDoctrine()->getManager()->persist($data);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Successful added new post');

            return $this->redirect($this->generateUrl('home'));
        }

        return $this->render('post/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/view/{id}", name="view_post")
     * @param int $id
     * @return Response
     */
    public function view(int $id): Response
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);

        return $this->render('post/view.html.twig', ['data' => $post]);
    }
}
