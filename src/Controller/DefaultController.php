<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }
    /**
     * @Route("/", name="default_home", methods={"GET"})
     */
  public function home() {
    $posts = $this->entityManager->getRepository(Post::class)->findBy(['deletedAt' => null]);

    return $this->render('default/home.html.twig', [
        'posts' => $posts
    ]);
  }
}
