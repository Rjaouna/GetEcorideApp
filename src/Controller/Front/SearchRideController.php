<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchRideController extends AbstractController
{
    #[Route('/front/search/ride', name: 'app_front_search_ride')]
    public function index(): Response
    {
        return $this->render('front/search_ride/index.html.twig', [
            'controller_name' => 'SearchRideController',
        ]);
    }
}
