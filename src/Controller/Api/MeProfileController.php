<?php

namespace App\Controller\Api;

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
	public function view(SerializerInterface $serializer): Response
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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
	public function updateMe(Request $request, SerializerInterface $serializer): JsonResponse
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

		/** @var User $me */
		$me = $this->getUser();

		$data = json_decode($request->getContent(), true) ?? [];

		// Récupération des champs autorisés
		$firstName   = $data['firstName']   ?? null;
		$lastName    = $data['lastName']    ?? null;
		$pseudo      = $data['pseudo']      ?? null;
		$phone       = $data['phone']       ?? null;
		$address     = $data['address']     ?? null;
		$dateOfBirth = $data['dateOfBirth'] ?? null; // 'YYYY-MM-DD'

		// Petites validations basiques (tu peux remplacer par Validator si besoin)
		$errors = [];

		if ($firstName !== null && \mb_strlen($firstName) > 50) {
			$errors['firstName'] = 'Doit faire 50 caractères maximum.';
		}
		if ($lastName !== null && \mb_strlen($lastName) > 50) {
			$errors['lastName'] = 'Doit faire 50 caractères maximum.';
		}
		if ($pseudo !== null && \mb_strlen($pseudo) > 20) {
			$errors['pseudo'] = 'Doit faire 20 caractères maximum.';
		}
		if ($phone !== null && \mb_strlen($phone) > 20) {
			$errors['phone'] = 'Doit faire 20 caractères maximum.';
		}
		if ($address !== null && \mb_strlen($address) > 500) {
			$errors['address'] = 'Doit faire 500 caractères maximum.';
		}
		if ($dateOfBirth !== null && $dateOfBirth !== '') {
			$dob = \DateTimeImmutable::createFromFormat('Y-m-d', $dateOfBirth);
			$dobErrors = \DateTimeImmutable::getLastErrors();
			if (!$dob || $dobErrors['warning_count'] || $dobErrors['error_count']) {
				$errors['dateOfBirth'] = 'Format attendu: YYYY-MM-DD.';
			}
		}

		if (!empty($errors)) {
			return $this->json([
				'message' => 'Certaines valeurs sont invalides.',
				'errors'  => $errors,
			], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		// Application des changements
		if ($firstName !== null) {
			$me->setFirstName($firstName ?: null);
		}
		if ($lastName !== null) {
			$me->setLastName($lastName ?: null);
		}
		if ($pseudo !== null) {
			$me->setPseudo($pseudo ?: null);
		}
		if ($phone !== null) {
			$me->setPhone($phone ?: null);
		}
		if ($address !== null) {
			$me->setAddress($address ?: null);
		}
		if ($dateOfBirth !== null) {
			if ($dateOfBirth === '') {
				$me->setDateOfBirth(null);
			} else {
				$me->setDateOfBirth(\DateTimeImmutable::createFromFormat('Y-m-d', $dateOfBirth));
			}
		}

		$this->em->flush();

		// Optionnel: si firewall stateful, relog pour refresh token storage
		try {
			$this->security->login($me);
		} catch (\Throwable) {
		}

		return $this->json([
			'message' => 'Profil mis à jour.',
			'user'    => $serializer->normalize($me, 'json', ['groups' => ['profile:read']]),
		], Response::HTTP_OK);
	}
}
