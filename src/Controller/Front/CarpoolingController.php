<?php

namespace App\Controller\Front;

use App\Entity\Carpooling;
use App\Repository\CarpoolingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CarpoolingController extends AbstractController
{
    #[Route('/front/carpooling/details/{id}', name: 'app_front_carpooling')]
    public function show(int $id, Carpooling $carpooling): Response
    {
        $user = $this->getUser();
        if (!$user) {
            // 🔁 Redirection vers la page de login
            return $this->redirectToRoute('app_login');
        }
        return $this->render('front/carpooling/show.html.twig', [
            'id' => $id,
            'isParticipant' => $carpooling->getParticipants($this->getUser()),
        ]);
    }
}
