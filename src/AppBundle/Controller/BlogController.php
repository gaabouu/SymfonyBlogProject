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
     * @Route("/{numb}", name="homepage", defaults={"numb" : 10}, requirements={"numb"="\d+0"})
     */
    public function indexAction(Request $request, $numb)
    {


        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Post');

        //FIXME: create request to get and give to the template only the 10 necesaries posts

        $posts = $repository->findAll();

        

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
     * @Route("/updatePage/{idPost}", name="updatePage")
     */
    public function updatePageAction($idPost){
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Post');

        $post = $repository->findOneById($idPost);

        $user = $this->getUser();

        if(!$user){
            return $this->render('default/invaliduser.html.twig');
        }

        if(!$post){
            throw $this->createNotFoundException(
                "no post with that id" . $idPost
            );
        }

        return $this->render('default/updating.html.twig', ['post' => $post, 'user' => $user]);
    }

    /**
     * @Route("/myposts/{numb}", name="myposts", defaults={"numb" : 10}, requirements={"numb"="\d+0"})
     */
    public function mypostsAction($numb){
        $user = $this->getUser();

        $repository = $this->getDoctrine()
        ->getRepository('AppBundle:Post');

        //FIXME: create a request to get posts only 10 by 10
        $posts = $repository->findByAuthor($user);
        
        return $this->render('default/myposts.html.twig', ['user' => $user,
                    'posts' => $posts        
        ]);
    }

    /**
     * @Route("/delete/{idPost}", name="delete")
     */
    public function deleteAction($idPost){
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($idPost);
    
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id ' . $idPost
            );
        }

        $user = $this->getUser();

        if(!$user){
            return $this->render('default/invaliduser.html.twig');
        }
        else if($user != $post->getAuthor()){
            return $this->render('default/invaliduser.html.twig', ['user' => $user]);
        }

        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('myposts');
    }

    /**
     * @Route("/update/{idPost}", name="update",  requirements={"numb"="\d+0"})
     */
    public function updateAction(Request $request, $idPost)
    {

        $user = $this->getUser();

        $form = $this->createFormBuilder()
          ->add('title', TextType::class)
          ->add('content', TextType::class)
          ->add('add', SubmitType::class)
          ->getForm();

        $title = 0;

        if($request->getMethod() == "POST"){
            $form->handleRequest($request);

            $title = $request->request->get('title');
            
            $content = $request->request->get('content');
        }
        if($title == 0){
            return $this->render('default/invaliduser.html.twig', ['user' => $user]);
        }

        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($idPost);
    
        if (!$post) {
            throw $this->createNotFoundException(
                'No product found for id '.$productId
            );
        }


        $post->setTitle($title);
        $post->setContent($content);
        $post->setPublished(new \DateTime());

        
        $em->flush();

        return $this->postAction($post->getId());



        //return new Response('New Post created! : '. $post->getId());

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

