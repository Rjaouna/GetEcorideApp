<?php

namespace App\Controller\Admin\Carpooling;

use App\Entity\Carpooling;
use App\Form\CarpoolingType;
use App\Repository\CarpoolingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/dashboard/carpooling', name: 'admin_carpooling_')]
final class CarpoolingController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(CarpoolingRepository $carpoolingRepository): Response
    {
        return $this->render('admin/dashboard/carpooling/index.html.twig', [
            'carpoolings' => $carpoolingRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $carpooling = new Carpooling();
        $form = $this->createForm(CarpoolingType::class, $carpooling);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($carpooling);
            $entityManager->flush();

            return $this->redirectToRoute('admin_carpooling_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/dashboard/carpooling/new.html.twig', [
            'carpooling' => $carpooling,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Carpooling $carpooling): Response
    {
        return $this->render('admin/dashboard/carpooling/show.html.twig', [
            'carpooling' => $carpooling,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Carpooling $carpooling, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CarpoolingType::class, $carpooling);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_carpooling_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/dashboard/carpooling/edit.html.twig', [
            'carpooling' => $carpooling,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Carpooling $carpooling, EntityManagerInterface $entityManager): Response
    {
        // Pour un formulaire classique, préfère:
        // if ($this->isCsrfTokenValid('delete'.$carpooling->getId(), $request->request->get('_token'))) {
        if ($this->isCsrfTokenValid('delete' . $carpooling->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($carpooling);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_carpooling_index', [], Response::HTTP_SEE_OTHER);
    }
}
