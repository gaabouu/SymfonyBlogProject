<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\ParameterBag;



use AppBundle\Entity\Post;


class BlogController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {


        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Post');

        $posts = $repository->findAll();

        
        if(!$posts){
            throw $this->createNotFoundException(
                'No post At all'
            );
        }


        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'posts' => $posts
        ]);
    }
    

    /**
     * @Route("/post/{idPost}", name="Post", requirements={"idPost" : "\d+" })
     */
    public function postAction($idPost)
    {

        $repository = $this->getDoctrine()
        ->getRepository('AppBundle:Post');

        $post = $repository->find($idPost);
        
        $posts = $repository->findAll();

        if(!$post)
        {
            throw $this->createNotFoundException(
                "nooooo"
            );
        }

        $author = $post->getAuthor(); 
        $title = $post->getTitle();
        $content = $post->getContent();
        $published = $post->getPublished();


        return $this->render('default/post.html.twig', ['post' => $post]);
    }


    /**
     * @Route("/createPage", name="createPage")
     */
    public function createPageAction()
    {
        return $this->render('default/posting.html.twig');
    }

    /**
     * @Route("/create", name="create")
     */
    public function createAction(Request $request)
    {

        $form = $this->createFormBuilder()
          ->add('name', TextType::class)
          ->add('title', TextType::class)
          ->add('content', TextType::class)
          ->add('add', SubmitType::class)
          ->getForm();

        if($request->getMethod() == "POST"){
            $form->handleRequest($request);

            $name = $request->request->get('name');
            $title = $request->request->get('title');
            $content = $request->request->get('content');
        }

        $post = new Post();
        $post->setAuthor($name);
        $post->setTitle($title);
        $post->setUrlAlias('test');
        $post->setContent($content);
        $pub = "11-11-12";
        $post->setPublished(new \DateTime($pub));

        $em = $this->getDoctrine()->getManager();
        
        $em->persist($post);
        $em->flush();

        return $this->postAction($post->getId());



        //return new Response('New Post created! : '. $post->getId());

    }

}

