<?php

namespace App\Controller\Api;

use App\Entity\Wallet;
use App\Entity\Booking;
use App\Entity\Carpooling;
use App\Entity\WalletTransaction;
use App\Repository\WalletRepository;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\WalletTransactionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/carpoolings', name: 'api_carpooling_')]
class CarpoolingParticipantController extends AbstractController
{
	#[Route('/{id}/join', name: 'join', methods: ['POST'])]
	#[IsGranted('ROLE_USER')]
	public function joinCarpooling(Carpooling $carpooling, EntityManagerInterface $em, WalletRepository $wallet): JsonResponse
	{
		// ✅ Récupération de l'utilisateur connecté
		$user = $this->getUser();

		if (!$user) {
			return $this->json(['error' => 'Utilisateur non connecté'], 401);
		}

		$walletUser = $wallet->findOneBy(['owner' => $user]);
		if (!$walletUser) {
			return $this->json(['error' => 'Portefeuille non trouvé'], 404);
		}
		if ($walletUser->getBalance() < 2) {
			return $this->json(['error' => 'Solde insuffisant dans le portefeuille'], 400);
		}
		// Déduire 2 euros du portefeuille de l'utilisateur
		$newBalance = $walletUser->getBalance() - 2;
		$walletUser->setBalance($newBalance);
		$em->persist($walletUser);

		// transaction de type "debit"
		$transaction = new WalletTransaction();
		$transaction->setWallet($walletUser);
		$transaction->setType('debit');
		$transaction->setAmount(2);
		$em->persist($transaction);

		// ✅ Vérifier si l’utilisateur est déjà participant
		if ($carpooling->getParticipants()->contains($user)) {
			return $this->json(['message' => 'Vous êtes déjà inscrit à ce covoiturage.'], 200);
		}
		// ✅ Créer une nouvelle réservation
		$booking = new Booking();
		$booking->setTrip($carpooling);
		$booking->setPassager($user);
		$booking->setStatus('confirmed');
		$em->persist($booking);

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
	public function leaveCarpooling(
		Carpooling $carpooling,
		EntityManagerInterface $em,
		BookingRepository $booking,
		WalletRepository $walletRepo,
		WalletRepository $wallet
	): JsonResponse {
		$user = $this->getUser();

		if (!$user) {
			return $this->json(['error' => 'Utilisateur non connecté'], 401);
		}

		if (!$carpooling->getParticipants()->contains($user)) {
			return $this->json(['message' => 'Vous n’êtes pas inscrit à ce covoiturage.'], 200);
		}

		// ✅ Récupération du portefeuille utilisateur
		$wallet = $walletRepo->findOneBy(['owner' => $user]);
		if (!$wallet) {
			return $this->json(['error' => 'Portefeuille non trouvé'], 404);
		}

		// ✅ Retirer le participant du covoiturage
		$carpooling->removeParticipant($user);
		$em->persist($carpooling);



		// ✅ Trouver la dernière réservation de cet utilisateur sur ce trajet
		$bookingEntry = $booking->findOneBy(
			[
				'trip' => $carpooling,
				'passager' => $user
			],
			['id' => 'DESC'] // la plus récente
		);

		if ($bookingEntry) {
			// Marquer la réservation comme annulée
			$bookingEntry->setStatus('canceled');
			$em->persist($bookingEntry);

			// ✅ Rembourser 2 euros dans le portefeuille
			$wallet->setBalance($wallet->getBalance() + 2);
			$em->persist($wallet);


			// ✅ Enregistrer une transaction de type "crédit"
			$refundTransaction = new WalletTransaction();
			$refundTransaction->setWallet($wallet);
			$refundTransaction->setType('credit');
			$refundTransaction->setAmount(2);
			$em->persist($refundTransaction);

			$em->flush();

			return $this->json([
				'message' => 'Vous vous êtes désinscrit du covoiturage. Le montant de 2€ vous a été remboursé.',
				'new_balance' => $wallet->getBalance()
			]);
		}

		$em->flush();

		return $this->json(['message' => 'Vous vous êtes désinscrit du covoiturage.']);
	}
}
