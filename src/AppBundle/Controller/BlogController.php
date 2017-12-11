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
//TODO: Add routes for deleting and updating posts 

class BlogController extends Controller
{
    /**
     * @Route("/{numb}", name="homepage", defaults={"numb" : 10}, requirements={"numb"="\d+0"})
     */
    public function indexAction(Request $request, $numb)
    {


        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Post');

        //FIXME: create request to get and give to the template only the 10 necesaries posts

        $posts = $repository->findAll();

        
        if(!$posts){
            throw $this->createNotFoundException(
                'No post At all'
            );
        }

        $user = $this->getUser();

        if(!$user){
            return $this->render('default/index.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                'posts' => $posts
            ]);
        }


        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'posts' => $posts,
            'user' => $user
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

        $user = $this->getUser();
        
        if(!$user){
            return $this->render('default/post.html.twig', ['post' => $post]);
        }


        return $this->render('default/post.html.twig', ['post' => $post,
                        'user' => $user
        ]);
    }


    /**
     * @Route("/createPage", name="createPage")
     */
    public function createPageAction()
    {
        $user = $this->getUser();
        
        if(!$user){
            return $this->render('default/posting.html.twig');
        }

        return $this->render('default/posting.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/myposts", name="myposts")
     */
    public function mypostsAction(){
        $user = $this->getUser();

        //FIXME: create request to get all current user's posts and give it to the template

        $repository = $this->getDoctrine()
        ->getRepository('AppBundle:Post');

        $posts = $repository->findAll();
        
        return $this->render('default/myposts.html.twig', ['user' => $user,
                    'posts' => $posts        
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function createAction(Request $request)
    {

        $user = $this->getUser();

        $form = $this->createFormBuilder()
          ->add('title', TextType::class)
          ->add('content', TextType::class)
          ->add('add', SubmitType::class)
          ->getForm();

        if($request->getMethod() == "POST"){
            $form->handleRequest($request);

            $title = $request->request->get('title');
            $content = $request->request->get('content');
        }

        $post = new Post();
        $post->setAuthor($user);
        $post->setTitle($title);
        $post->setUrlAlias('test');
        $post->setContent($content);
        $pub = "11-11-12";
        $post->setPublished(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        
        $em->persist($post);
        $em->flush();

        return $this->postAction($post->getId());



        //return new Response('New Post created! : '. $post->getId());

    }


}

