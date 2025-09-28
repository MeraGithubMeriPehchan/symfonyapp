<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ApiController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        return $this->render('profile/index.html.twig');
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_posts');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    
    #[Route('/posts/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    public function deletePost(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();
            $this->addFlash('success', 'Post deleted successfully!');
        }

        return $this->redirectToRoute('app_posts');
    }

    #[Route('/posts/{id}/edit', name: 'app_post_edit')]
    public function editPost(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class, ['label' => 'Title', 'attr' => ['class' => 'form-control']])
            ->add('body', TextareaType::class, ['label' => 'Content', 'attr' => ['class' => 'form-control', 'rows' => 5]])
            ->add('save', SubmitType::class, ['label' => 'Update', 'attr' => ['class' => 'btn btn-primary mt-2']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('app_posts');
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/posts/new', name: 'app_post_new')]
    public function newPost(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createFormBuilder($post)
        ->add('title', TextType::class, [
            'label' => 'Title',
            'attr' => ['class' => 'form-control']
        ])
        ->add('body', TextareaType::class, [
            'label' => 'Content',
            'attr' => ['class' => 'form-control', 'rows' => 5]
        ])
        ->add('save', SubmitType::class, [
            'label' => 'Create',
            'attr' => ['class' => 'btn btn-success mt-2']
        ])
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post created successfully!');

            return $this->redirectToRoute('app_posts');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/posts/save', name: 'app_posts_save')]
    public function savePosts(EntityManagerInterface $entityManager): Response
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://jsonplaceholder.typicode.com/posts');
        $postsData = $response->toArray();

        foreach ($postsData as $postData) {
            // Check if post already exists by API ID
            $existing = $entityManager->getRepository(Post::class)->findOneBy(['apiId' => $postData['id']]);
            if ($existing) {
                continue; // skip duplicates
            }

            $post = new Post();
            $post->setApiId($postData['id']);
            $post->setTitle($postData['title']);
            $post->setBody($postData['body']);

            $entityManager->persist($post);
        }

        $entityManager->flush();

        return new Response('Posts saved successfully!');
    }

    #[Route('/posts', name: 'app_posts')]
    public function listPosts(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
        $searchTerm = $request->query->get('q', ''); // get search keyword
        // Build query
        $qb = $em->getRepository(Post::class)->createQueryBuilder('p');

        if ($searchTerm) {
            $qb->where('p.title LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%');
        }

        $query = $qb->getQuery();

        // Paginate the results
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // posts per page
        );

        return $this->render('api/index.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
        ]);
    }

}
