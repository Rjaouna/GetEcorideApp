<?php

namespace App\Controller\Api;

use App\Entity\DriverPreferences;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DriverPreferencesRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

final class UserPreferencesController extends AbstractController
{
    #[Route('/api/user/preferences', name: 'app_api_user_preferences', methods: ['GET'])]
    public function index(DriverPreferencesRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();

        $prefs = $repo->findOneBy(['user' => $user]);
        if (!$prefs) {
            return $this->json(['message' => 'Preferences not found'], 404);
        }

        // Réponse “à plat” + user minimal
        return $this->json([
            'id'             => $prefs->getId(),
            'smokingAllowed' => (bool) $prefs->isSmokingAllowed(),
            'petsAllowed'    => (bool) $prefs->isPetsAllowed(),
            'user' => [
                'id'        => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName'  => $user->getLastName(),
                'pseudo'    => $user->getPseudo(),
                'phone'     => $user->getPhone(),
                'address'   => $user->getAddress(),
                'email'     => $user->getEmail(),      // affichage seulement si tu veux
                'dateOfBirth' => $user->getDateOfBirth()?->format('Y-m-d'),
            ],
        ]);}

    #[Route('/api/user/preferences', name: 'app_api_user_preferences_edit', methods: ['POST'])]
    public function edit(
        Request $request,
        DriverPreferencesRepository $repo,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // 1) JSON -> array (et pas via le Serializer pour éviter les 500 si JSON invalide)
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException | NotEncodableValueException $e) {
            return $this->json([
                'message' => 'JSON invalide',
                'detail'  => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        // 2) Récupérer (ou créer) les prefs de l’utilisateur
        $prefs = $repo->findOneBy(['user' => $user]);
        if (!$prefs) {
            $prefs = new DriverPreferences();
            $prefs->setUser($user);
            $em->persist($prefs);
        }

        // 3) Hydrater uniquement les champs autorisés
        if (\array_key_exists('smokingAllowed', $data)) {
            $prefs->setSmokingAllowed((bool) $data['smokingAllowed']);
        }
        if (\array_key_exists('petsAllowed', $data)) {
            $prefs->setPetsAllowed((bool) $data['petsAllowed']);
        }

        // 4) Validation (si contraintes sur l’entité)
        $errors = $validator->validate($prefs);
        if (count($errors) > 0) {
            $errs = [];
            foreach ($errors as $err) {
                $errs[$err->getPropertyPath()][] = $err->getMessage();
            }
            return $this->json([
                'message' => 'Validation error',
                'errors'  => $errs,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 5) Flush sécurisé
        try {
            $em->flush();
        } catch (\Throwable $e) {
            return $this->json([
                'message' => 'Persist/flush error',
                'detail'  => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 6) Réponse JSON (évite de sérialiser la relation user pour prévenir boucles/500)
        return $this->json($prefs, 200, [], [
            'groups'             => ['preference:read'],
            'ignored_attributes' => ['user'], // au cas où l'entité a @Groups dessus
        ]);



    }
}
