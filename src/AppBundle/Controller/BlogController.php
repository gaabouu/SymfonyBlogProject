<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Post;


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
        $post = $this->getDoctrine()
            ->getRepository('AppBundle:Post')
            ->find($idPost);

        if(!$post)
        {
            throw $this->createNotFoundException(
                'No post found with that idea :/ ' . $idPost
            );
        }

        $author = $post->getAuthor(); 
        $title = $post->getTitle();
        $content = $post->getContent();
        $published = $post->getPublished();


        return $this->render('default/post.html.twig', ['idPost'=>$idPost,
                                                'author'=>$author,
                                                'title'=>$title,
                                                'content'=>$content,
                                                'published'=>$published                                                    
        ]);
    }


    /**
     * @Route("/createPage", name="createPage")
     */
    public function createPageAction()
    {
        return $this->render('default/posting.html.twig');
    }

    public function createAction()
    {
        $post = new Post();
        $post->setId(150);
        $post->setAuthor('gab');
        $post->setTitle('Post');
        $post->setUrlAlias('test');
        $post->setContent('Hello World!');
        $pub = "11-11-12";
        $post->setPublished(new \DateTime($pub));

        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();

        return new Response('New Post created! : '.$post->getTitle());

    }

}

