<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Form\PostType;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostController extends AbstractController
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
     /**
     * @Route("/admin/creer-un-article", name="post_create_post", methods={"GET|POST"})
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function createPost(Request $request, EntityManagerInterface $entityManager): Response
    {

        $post = new Post();

        $form = $this->createForm(PostType::class, $post)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $post->setAlias($this->slugger->slug($form->get('title')->getData()));

            $file = $form->get('photo')->getData();

            if($file) {
                $extension = '.' . $file->guessExtension();
                $originalFilename = pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . ' ' . uniqid() . $extension;

                try {
                    $file->move($this->getParameter('uploads_dir'), $newFilename);

                    $post->setPhoto($newFilename);
                }   catch (FileException $exception) {

                }
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Votre article est bien en ligne !');

            return $this->redirectToRoute('default_home');
        }

        return $this->render('post/form_post.html.twig',[
            'form'=> $form->createView()
        ]);
    }

     /**
     * @Route("/admin/modifier-un-article/{id}", name="update_post", methods={"GET|POST"})
     * @param Post $post
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
    public function updatePost(Post $post, EntityManagerInterface $entityManager, Request $request): Response
    {
        $originalPhoto = $post->getPhoto() ?? "pas de photo";

        $form = $this->createForm(PostType::class, $post, [
            'photo' => $originalPhoto
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $post->setUpdatedAt(new DateTime());
            $post->setAlias($this->slugger->slug($post->getTitle()));

            /** @var UploadedFile $file */
            $file = $form->get('photo')->getData();

            if($file) {
                $extension = '.' . $file->guessExtension();
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                # $safeFilename = $post->getAlias();
                $newFilename = $safeFilename . '_' . uniqid() . $extension;

                try {
                    # On a paramétré le chemin 'uploads_dir' dans le fichier config/services.yaml
                    $file->move($this->getParameter('uploads_dir'), $newFilename);

                    $post->setPhoto($newFilename);

                } catch (FileException $exception){
                    // code à exécuter si une erreur est attrapée.
                }
            } else {
                $post->setPhoto($originalPhoto);
            }# end if($file)

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', "L'article". $post->getTitle() ." à bien été modifié !");

            return $this->redirectToRoute('show_dashboard');

        }

        return $this->render('post/form_post.html.twig', [
            'form' => $form->createView(),
            'post' => $post
        ]);
    }



    /**
     * @Route("/voir/{cat_alias}/{post_alias}_{id}", name="show_post", methods={"GET"} )
     * @param Post $post
     * @return Response
     */
    public function showPost(Post $post, EntityManagerInterface $entityManager): Response
    {
        $commentaries = $entityManager->getRepository(Commentary::class)->findBy(['post' => $post->getId()]);
        return $this->render('post/show_post.html.twig', [
            'post' => $post,
            'commentaries' => $commentaries
        ]);
    }

    /**
     * @Route("/voir/categorie/{alias}", name="show_posts_from_category", methods={"GET"})
     * @param Category $category
     * @return Response
     */
    public function showPostsFromCategory(Category $category, EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findBy(['category' => $category->getId(), 'deletedAt' => null]);

        return $this->render('post/show_posts_from_category.html.twig', [
            'posts' => $posts,
            'category' => $category
        ]);
    }


    /**
     * @Route("/admin/archiver-un-article/{id}", name="soft_delete_post", methods={"GET"})
     * @param Post $post
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function softDeletePost(Post $post, EntityManagerInterface $entityManager): Response
    {
        $post->setDeletedAt(new DateTime());

        $entityManager->persist($post);
        $entityManager->flush();

        $this->addFlash('success', "L'article <strong>". $post->getTitle()."</strong> a bien été archivé");

        return $this->redirectToRoute("show_dashboard");
    }
}
