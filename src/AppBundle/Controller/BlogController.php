<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class BlogController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/post/{idPost}", name="Post", requirements={"idPost" : "\d+" })
     */
    public function postAction($idPost)
    {
        return $this->render('default/post.html.twig', ['idPost'=>$idPost]);
    }

    /**
     * @Route("/post/{idPost}", name="wrong_post")
     */
    public function wrongPostAction($idPost)
    {
        return new Response('<h1>L\'identifiant ' . $idPost . ' ne correspond à aucun post</h1>');
    }

    /**
     * @Route("/create", name="create")
     */
    public function createAction()
    {
        return $this->render('default/posting.html.twig');
    }
}

