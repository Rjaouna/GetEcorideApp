<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/dashboard/user')]
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
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            $user->setCreatedBy($this->getUser());
            $user->setUpdatedBy($this->getUser());
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/dashboard/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/dashboard/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_user_toggle', methods: ['POST'])]
    public function toggle(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        Security $security
    ): Response {
        // Autorisations (à adapter : ROLE_ADMIN ou voter)
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // CSRF
        if (!$this->isCsrfTokenValid('user_toggle_' . $user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Option : empêcher de se bloquer soi-même
        if ($security->getUser() && $security->getUser()->getId() === $user->getId()) {
            $this->addFlash('warning', 'Vous ne pouvez pas vous bloquer vous-même.');
            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
        }

        // Toggle + persist
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
        $form = $this->createForm(UserType::class, $user, ['include_password' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedBy($this->getUser());
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
