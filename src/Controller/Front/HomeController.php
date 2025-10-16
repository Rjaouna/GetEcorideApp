<?php

namespace App\Controller\Front;

use App\Entity\DriverPreferences;
use App\Controller\Base\BaseController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends BaseController
{
    #[Route('/', name: 'app_front_home')]
    public function index(EntityManagerInterface $em): Response
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_EMPLOYE')) {
            return $this->redirectByRole();
        }
        $me = $this->getUser();
        if ($me) {
            if (!$me->getDriverPreferences()) {
                if (!$isDriver = \in_array('ROLE_DRIVER', $me->getRoles(), true)) {
                    $newRef = new DriverPreferences();
                    $newRef->setUser($me)
                        ->setSmokingAllowed(false)
                        ->setPetsAllowed(false);
                    $em->persist($newRef);
                    $em->flush();
                }
            }
        }

        return $this->render('front/home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
