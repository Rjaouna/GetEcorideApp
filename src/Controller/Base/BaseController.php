<?php // src/Controller/Base/BaseController.php
namespace App\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BaseController extends AbstractController
{
    /** Redirige selon le rôle courant */
    protected function redirectByRole(): RedirectResponse
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_EMPLOYE')) {
            return $this->redirectToRoute('app_admin_dashboard');   // ← route admin
        }

        return $this->redirectToRoute('app_front_home');              // ← route publique/utilisateur
    }
}
