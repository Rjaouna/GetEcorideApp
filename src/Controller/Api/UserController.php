<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UserController extends AbstractController
{
    #[Route('/admin/users/stats/roles', name: 'admin_user_role_stats', methods: ['GET'])]
    public function roles(UserRepository $users): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $rolesToCount = ['Admin' => 'ROLE_ADMIN', 'Passager' => 'ROLE_PASSAGER', 'Conducteur' => 'ROLE_DRIVER', 'Employé' => 'ROLE_EMPLOYE'];
        $counts = $users->countByRoles($rolesToCount);


        $total = array_sum($counts);

        return $this->json([
            'total'  => $total,
            'byRole' => $counts,
        ]);
    }
    #[Route('/admin/users/stats/locked-accounts', name: 'admin_user_locked_accounts', methods: ['GET'])]
    public function lockedAccounts(UserRepository $users): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $locked = $users->findBy(['isLocked' => true], ['id' => 'DESC'], 100);

        $data = array_map(fn(\App\Entity\User $u) => [
            'email'    => $u->getEmail(),
            'roles'    => $u->getRoles(),
        ], $locked);

        return $this->json([
            'lockedCount' => count($data),
            'lockedUsers' => $data,
        ]);
    }
}
