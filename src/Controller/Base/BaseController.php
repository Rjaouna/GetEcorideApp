<?php

namespace App\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BaseController extends AbstractController
{
    #[Route('/base/base', name: 'app_base_base')]
    public function index(): Response
    {
        return $this->render('base/base/index.html.twig', [
            'controller_name' => 'BaseController',
        ]);
    }
}
