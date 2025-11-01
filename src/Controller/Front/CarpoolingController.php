<?php

namespace App\Controller\Front;

use App\Repository\CarpoolingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CarpoolingController extends AbstractController
{
    #[Route('/front/carpooling/details/{id}', name: 'app_front_carpooling')]
    public function show(int $id): Response
    {
        return $this->render('front/carpooling/show.html.twig', [
            'id' => $id,
        ]);
    }
}
