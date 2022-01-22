<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryController extends AbstractController
{
    /**
     * @Route("/admin/ajouter-categorie", name="create_category", methods={"GET|POST"})
     * @param Request $request
     * @param SluggerInterface $slugger
     * @return Response
     */
    public function createCategory(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $category->setAlias($slugger->slug($category->getName()));

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', "La catégorie <strong>". $category->getName() . "</strong> a bien été ajouté à la base.");

            return $this->redirectToRoute("show_dashboard");
        }

        return $this->render('category/form_cat.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/modifier-categorie/{id}", name="update_category", methods={"GET|POST"})
     * @param Category $category
     * @param Request $request
     * @param SluggerInterface $slugger
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function updateCategory(Category $category, Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $originalName = $category->getName();

        $form = $this->createForm(CategoryType::class, $category)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $category->setUpdatedAt(new DateTime());
            $category->setAlias($slugger->slug($category->getName()));

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash(
                'success',
                "La catégorie <strong>". $originalName . "</strong> a bien été modifié en <strong>". $category->getName() . "</strong> dans la base."
            );

            return $this->redirectToRoute("show_dashboard");
        }

            return $this->render('category/form_cat.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }

    /**
     * @Route("/admin/archiver-categorie/{id}", name="soft_delete_category", methods={"GET"})
     * @param Category $category
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function softDeleteCategory(Category $category, EntityManagerInterface $entityManager): Response
    {
        $category->setDeletedAt(new DateTime());

        $entityManager->persist($category);
        $entityManager->flush();

        $this->addFlash('success', "La catégorie <strong>". $category->getName() . "</strong> a bien été archivé dans le système");

        return $this->redirectToRoute("show_dashboard");
    }

    /**
     * @Route("/admin/supprimer-categorie/{id}", name="hard_delete_category", methods={"GET"})
     * @param Category $category
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function hardDeleteCategory(Category $category, EntityManagerInterface $entityManager): Response
    {
        $originalName = $category->getName();

        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', "La catégorie <strong>". $originalName . "</strong> a bien été supprimé définitivement de la base.");
        return $this->redirectToRoute("show_dashboard");
    }

    /**
     * @Route("/admin/restaurer-category/{id}", name="restore_category", methods={"GET"})
     * @param Category $category
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     */
    public function restoreCategory(Category $category, EntityManagerInterface $entityManager): RedirectResponse
    {
        $category->setDeletedAt(null);

        $entityManager->persist($category);
        $entityManager->flush();

        $this->addFlash('success', "La catégorie <strong>". $category->getName() . "</strong> a bien été restauré dans la base.");
        return $this->redirectToRoute("show_dashboard");
    }
}