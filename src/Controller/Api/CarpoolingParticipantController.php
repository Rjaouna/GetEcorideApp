<?php

namespace App\Controller\Api;

use App\Entity\Carpooling;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/carpoolings', name: 'api_carpooling_')]
class CarpoolingParticipantController extends AbstractController
{
	#[Route('/{id}/join', name: 'join', methods: ['POST'])]
	#[IsGranted('ROLE_USER')]
	public function joinCarpooling(Carpooling $carpooling, EntityManagerInterface $em): JsonResponse
	{
		// ✅ Récupération de l'utilisateur connecté
		$user = $this->getUser();

		if (!$user) {
			return $this->json(['error' => 'Utilisateur non connecté'], 401);
		}

		// ✅ Vérifier si l’utilisateur est déjà participant
		if ($carpooling->getParticipants()->contains($user)) {
			return $this->json(['message' => 'Vous êtes déjà inscrit à ce covoiturage.'], 200);
		}

		// ✅ Ajouter le participant
		$carpooling->addParticipant($user);
		$em->persist($carpooling);
		$em->flush();

		return $this->json([
			'message' => 'Vous avez rejoint le covoiturage avec succès !',
			'carpooling_id' => $carpooling->getId(),
			'user_id' => $user->getId(),
		]);
	}
	#[Route('/{id}/leave', name: 'leave', methods: ['POST'])]
	#[IsGranted('ROLE_USER')]
	public function leaveCarpooling(Carpooling $carpooling, EntityManagerInterface $em): JsonResponse
	{
		$user = $this->getUser();

		if (!$user) {
			return $this->json(['error' => 'Utilisateur non connecté'], 401);
		}

		if (!$carpooling->getParticipants()->contains($user)) {
			return $this->json(['message' => 'Vous n’êtes pas inscrit à ce covoiturage.'], 200);
		}

		$carpooling->removeParticipant($user);
		$em->flush();

		return $this->json(['message' => 'Vous vous êtes désinscrit du covoiturage.']);
	}
}
