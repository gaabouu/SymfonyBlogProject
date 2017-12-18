<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

use AppBundle\Entity\Post;

//TODO: Don't forget to push to heroku

class BlogController extends Controller
{
    /**
     * @Route("/{numb}", name="homepage", defaults={"numb" : 10}, requirements={"numb"="\d+0"})
     */
    public function indexAction(Request $request, $numb)
    {
        //FIXME: change request to get and give to the template only the 10 necesaries posts
        $em = $this->getDoctrine()->getManager();
        
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('AppBundle\Entity\Post', 'p');

        $query = $em->createNativeQuery('SELECT * from post ORDER BY published DESC', $rsm);
        $posts = $query->getResult();

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
        
        $user = $this->getUser();
        
        if(!$user){
            return $this->render('default/post.html.twig', ['post' => $post]);
        }

        if(!$post)
        {
            if(!$user){
                return $this->render('default/invaliduser.html.twig');
            }
            return $this->render('default/invaliduser.html.twig', ['user' => $user]);
        }

        $author = $post->getAuthor(); 
        $title = $post->getTitle();
        $content = $post->getContent();
        $published = $post->getPublished();

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
            return $this->render('default/invaliduser.html.twig', ['user' => $user]);
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
        $em = $this->getDoctrine()->getManager();
        
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('AppBundle\Entity\Post', 'p');

        $query = $em->createNativeQuery('SELECT * from post WHERE author = ? ORDER BY published DESC', $rsm);
        $query->setParameter(1, $user);

        $posts = $query->getResult();
        
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
    
        $user = $this->getUser();

        if(!$user){
            return $this->render('default/invaliduser.html.twig');
        }
        else if (!$post) {
            return $this->render('default/invaliduser.html.twig', ['user' => $user]);
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

        if($request->getMethod() == "POST"){
            $form->handleRequest($request);
            $title = $request->request->get('title');
            $content = $request->request->get('content');
        }
        else{
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
    }

    /**
     * @Route("/create", name="create")
     */
    public function createAction(Request $request)
    {

        $user = $this->getUser();

        if(!$user){
            return $this->render('default/invaliduser.html.twig');
        }

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
        else{
            return $this->render('default/invaliduser.html.twig', ['user' => $user]);
        }

        $post = new Post();
        $post->setAuthor($user);
        $post->setTitle($title);
        $post->setUrlAlias("/post/" + $post->getId());
        $post->setContent($content);
        $post->setPublished(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        
        $em->persist($post);
        $em->flush();

        return $this->postAction($post->getId());

    }


}

