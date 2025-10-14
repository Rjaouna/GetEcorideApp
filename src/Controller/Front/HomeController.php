<?php

namespace App\Controller\Front;

use App\Controller\Base\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends BaseController
{
    #[Route('/', name: 'app_front_home')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_EMPLOYE')) {
            return $this->redirectByRole();
        }

        return $this->render('front/home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
