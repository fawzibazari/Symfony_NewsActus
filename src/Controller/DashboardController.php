<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin")
 */
class DashboardController extends AbstractController
{

    /**
     * @Route("/tableau-de-bord", name="show_dashboard", methods={"GET"})
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function showDashboard(EntityManagerInterface $entityManager)
    {
        $posts = $entityManager->getRepository(Post::class)->findBy(['deletedAt' => null]);
        $categories = $entityManager->getRepository(Category::class)->findBy(['deletedAt' => null]);
        $users = $entityManager->getRepository(User::class)->findBy(['deletedAt' => null]);

        return $this->render('dashboard/show_dashboard',[
            'posts'=> $posts,
            'categories'=>$categories,
            'users'=>$users
        ]);
    }

 
}
