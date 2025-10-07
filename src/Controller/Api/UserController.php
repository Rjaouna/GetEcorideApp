<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

final class UserController extends AbstractController
{
    #[Route('/aadmin/users/profile/{id}', name: 'user_profil', methods: ['GET'])]
    public function userProfile(string $id, UserRepository $repo): JsonResponse
    {

        $user = $repo->find($id);
        return $this->json($user, 200, [], ['groups' => 'profil.details']);
    }
    #[Route('/aadmin/users/edit/profile/{id}', name: 'user_profil', methods: ['POST'])]
    public function userProfileEdit(string $id, UserRepository $repo, Request $request, SerializerInterface $serializer): JsonResponse
    {

        $content = $request->getContent();
        $user = $serializer->deserialize($content, User::class, 'json');

        dd($user);
    }

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
