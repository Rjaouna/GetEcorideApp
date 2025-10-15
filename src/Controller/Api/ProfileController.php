<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

final class ProfileController extends AbstractController
{
    private const ROLE_BASE     = 'ROLE_USER';
    private const ROLE_DRIVER   = 'ROLE_DRIVER';
    private const ROLE_PASSAGER = 'ROLE_PASSAGER';

    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    /**
     * Basculer le rôle du user connecté.
     * Bloqué si PASSAGER -> DRIVER sans véhicule (422 + action "modal").
     */
    #[Route('/switch-role', name: 'me_switch_role', methods: ['POST'])]
    public function switchMyRole(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user  = $this->getUser();
        $roles = $user->getRoles();

        $isCurrentlyDriver = \in_array(self::ROLE_DRIVER, $roles, true);

        // PASSAGER -> DRIVER sans véhicule : on bloque
        if (!$isCurrentlyDriver && \count($user->getVehicles()) === 0) {
            return $this->json([
                'message'  => 'Impossible d’activer le mode driver : aucun véhicule associé à votre compte.',
                'blocked'  => true,
                'isDriver' => false,
                'roles'    => $roles,
                'action'   => [
                    'type'  => 'modal',          // le front ouvre le modal d’ajout de véhicule
                    'label' => 'Ajouter un véhicule',
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Toggle des rôles
        $newRoles = $this->toggleDriverPassager($roles);
        $user->setRoles($newRoles);
        $this->em->flush();

        // Re-login si firewall stateful (sinon ignoré)
        try {
            $this->security->login($user);
        } catch (\Throwable) {
        }

        return $this->json([
            'message'  => 'Role updated for current user',
            'roles'    => $user->getRoles(),
            'isDriver' => \in_array(self::ROLE_DRIVER, $user->getRoles(), true),
            'blocked'  => false,
        ], Response::HTTP_OK);
    }

    /**
     * API profil (retire vehicles/carpoolings si pas DRIVER).
     */
    #[Route('/profile/{id<\d+>}', name: 'app_profile', methods: ['GET'])]
    public function index(User $user, SerializerInterface $serializer): JsonResponse
    {
        $now = new \DateTimeImmutable('now');

        $vehiclesCount = \count($user->getVehicles());
        $activeCarpoolings = 0;
        foreach ($user->getCarpoolings() as $c) {
            $isFuture    = $c->getDeparatureAt() && $c->getDeparatureAt() > $now;
            $isPublished = \strtolower((string) $c->getStatus()) === 'published';
            if ($isFuture && $isPublished) {
                $activeCarpoolings++;
            }
        }

        $payloadUser = $serializer->normalize($user, 'json', ['groups' => ['profile:read']]);

        $isDriver = $this->isGranted(self::ROLE_DRIVER);
        if (!$isDriver) {
            $payloadUser['vehicles']    = [];
            $payloadUser['carpoolings'] = [];
        }

        return $this->json([
            'user' => $payloadUser,
            'meta' => [
                'isDriver'          => $isDriver,
                'vehiclesCount'     => $vehiclesCount,
                'activeCarpoolings' => $activeCarpoolings,
            ],
        ], Response::HTTP_OK);
    }



    // ============ Helpers rôles ============
    private function toggleDriverPassager(array $roles): array
    {
        $roles = $this->ensureBaseRole($roles);

        $hasDriver   = \in_array(self::ROLE_DRIVER,   $roles, true);
        $hasPassager = \in_array(self::ROLE_PASSAGER, $roles, true);

        // Retire les deux, puis ajoute l'autre
        $roles = \array_values(\array_diff($roles, [self::ROLE_DRIVER, self::ROLE_PASSAGER]));
        $roles[] = $hasDriver ? self::ROLE_PASSAGER : self::ROLE_DRIVER;

        return \array_values(\array_unique($roles));
    }

    private function ensureBaseRole(array $roles): array
    {
        if (!\in_array(self::ROLE_BASE, $roles, true)) {
            $roles[] = self::ROLE_BASE;
        }
        return $roles;
    }
}
