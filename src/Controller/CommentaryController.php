<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Commentary;
use App\Form\CommentaryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentaryController extends AbstractController
{
      /**
     * @Route("/ajouter-un-commentaire?post_id={id}", name="add_commentary", methods={"GET|POST"} )
     * @param Request $request
     * @param Post $post
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function addCommentary(Post $post, Request $request, EntityManagerInterface $entityManager)
    {
        $commentary = new Commentary();
        
        $form = $this->createForm( CommentaryType::class, $commentary)->handleRequest($request);
        

        if($form->isSubmitted() && ! $form->isValid()){

            $this->addFlash('warning', 'Le commentaire ne peut être vide');

            return $this->redirectToRoute('show_post', [
                'cat_alias' => $post->getCategory()->getAlias(),
                'post_alias' => $post->getAlias(),
                'id' => $post->getId()
            ]);
        }
        
        
        if($form->isSubmitted() && $form->isValid()){
            $commentary->setPost($post);

            $entityManager->persist($commentary);
            $entityManager->flush();

            $this->addFlash("success", "vous avez commenté l'article <strong>". $post->getTitle() ."</strong> avec succés !");

            return $this->redirectToRoute("show_post", [
                'cat_alias' => $post->getCategory()->getAlias(),
                'post_alias'=> $post->getAlias(),
                'id' => $post->getId()
            ]);
        }
        return $this->render('rendered/form_commentary.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
