<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/dashboard/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/dashboard/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/dashboard/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}', name: 'app_user_show', requirements: ['uuid' => '[0-9a-fA-F-]{36}'], methods: ['GET'])]
    public function show(string $uuid, UserRepository $repo): Response
    {
        $user = $repo->findOneBy(['uuid' => $uuid]);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        return $this->render('admin/dashboard/user/show.html.twig', [
            'user' => $user, // passe l'objet entier, pas juste l'UUID
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_user_toggle', methods: ['POST'])]
    public function toggle(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        Security $security
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // CSRF
        if (!$this->isCsrfTokenValid('user_toggle_' . $user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // empêcher de se bloquer soi-même
        if ($security->getUser() && $security->getUser()->getId() === $user->getId()) {
            $this->addFlash('warning', 'Vous ne pouvez pas vous bloquer vous-même.');
            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        // Toggle
        $user->setIsLocked(!$user->isLocked());
        $em->flush();

        $this->addFlash(
            $user->isLocked() ? 'warning' : 'success',
            $user->isLocked() ? 'Utilisateur bloqué avec succès.' : 'Utilisateur débloqué avec succès.'
        );

        // PRG
        return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/dashboard/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
