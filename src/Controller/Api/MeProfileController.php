<?php

namespace App\Controller\Api;

use App\Entity\DriverPreferences;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('', name: '')]
final class MeProfileController extends AbstractController
{
	public function __construct(
		private EntityManagerInterface $em,
		private Security $security
	) {}

	/**
	 * Vue du profil (préremplie).
	 */
	#[Route('/profile-view', name: 'profile_view', methods: ['GET'])]
	public function view(SerializerInterface $serializer, EntityManagerInterface $em): Response
	{
		// $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

		/** @var User $me */
		$me = $this->getUser();


		// on normalise pour réutiliser côté twig (pré-remplissage)
		$meNormalized = $serializer->normalize($me, 'json', ['groups' => ['profile:read']]);

		return $this->render('api/profile/show.html.twig', [
			'userId' => $me->getId(),
			'me'     => $meNormalized,
		]);
	}

	/**
	 * Mise à jour du profil du user connecté (champs autorisés uniquement).
	 * Champs modifiables: firstName, lastName, pseudo, phone, address, dateOfBirth (Y-m-d)
	 * Interdits ici: email, roles, password, isLocked, isVerified, etc.
	 */
	#[Route('/me/profile', name: 'api_me_profile_update', methods: ['PATCH'])]
	public function updateMe(Request $request, EntityManagerInterface $em): JsonResponse
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		/** @var User $me */
		$me = $this->getUser();

		try {
			$data = $request->toArray(); // nécessite header Content-Type: application/json
		} catch (\Throwable $e) {
			return $this->json(['message' => 'Payload JSON invalide'], 400);
		}

		// Champs autorisés uniquement
		$allowed = ['firstName', 'lastName', 'pseudo', 'phone', 'address', 'dateOfBirth'];
		foreach ($allowed as $k) {
			if (!\array_key_exists($k, $data)) continue;
			$val = $data[$k];

			if ($k === 'dateOfBirth') {
				if ($val === null || $val === '') {
					$me->setDateOfBirth(null);
				} else {
					try {
						// attend 'YYYY-MM-DD'
						$me->setDateOfBirth(new \DateTimeImmutable($val));
					} catch (\Throwable) {
						return $this->json(['message' => 'dateOfBirth invalide (format attendu: YYYY-MM-DD)'], 422);
					}
				}
				continue;
			}

			// setters simples
			match ($k) {
				'firstName' => $me->setFirstName($val ?: null),
				'lastName'  => $me->setLastName($val ?: null),
				'pseudo'    => $me->setPseudo($val ?: null),
				'phone'     => $me->setPhone($val ?: null),
				'address'   => $me->setAddress($val ?: null),
				default     => null,
			};
		}

		try {
			$em->flush();
		} catch (\Throwable $e) {
			return $this->json(['message' => 'Impossible d’enregistrer le profil'], 500);
		}

		// Réponse safe (pas l’entité brute)
		return $this->json([
			'user' => [
				'id' => $me->getId(),
				'firstName' => $me->getFirstName(),
				'lastName' => $me->getLastName(),
				'pseudo' => $me->getPseudo(),
				'phone' => $me->getPhone(),
				'address' => $me->getAddress(),
				'email' => $me->getEmail(),
				'dateOfBirth' => $me->getDateOfBirth()?->format('Y-m-d'),
			]
		], 200);
	}
}
