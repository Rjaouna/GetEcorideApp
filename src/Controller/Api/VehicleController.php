<?php

namespace App\Controller\Api;

use App\Entity\Vehicle;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/vehicles', name: 'api_vehicle_')]
final class VehicleController extends AbstractController
{
	public function __construct(private EntityManagerInterface $em) {}

	#[Route('', name: 'create', methods: ['POST'])]
	public function create(Request $request): Response
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		/** @var User $me */
		$me = $this->getUser();

		$data = json_decode($request->getContent() ?: '[]', true);

		$plate    = trim((string)($data['plateNumber'] ?? ''));
		$firstReg = $data['firstRegistrationAt'] ?? null;       // "YYYY-MM-DD"
		$brand    = trim((string)($data['brand'] ?? ''));
		$model    = trim((string)($data['model'] ?? ''));
		$seats    = $data['seats'] ?? null;
		$isElectric = array_key_exists('isElectric', $data) ? (bool)$data['isElectric'] : null;
		$isActive   = array_key_exists('isActive', $data)   ? (bool)$data['isActive']   : null;

		// Validations
		$errors = [];
		if ($plate === '') {
			$errors[] = 'La plaque est obligatoire.';
		}
		if (empty($firstReg)) {
			$errors[] = 'La date de première mise en circulation est obligatoire.';
		}
		if ($brand === '') {
			$errors[] = 'La marque est obligatoire.';
		}
		if ($model === '') {
			$errors[] = 'Le modèle est obligatoire.';
		}

		$seatsInt = filter_var($seats, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);
		if ($seatsInt === false) {
			$errors[] = 'Le nombre de places doit être un entier entre 1 et 9.';
		}

		try {
			$firstRegAt = new \DateTimeImmutable((string)$firstReg);
		} catch (\Throwable) {
			$errors[] = 'Format de date invalide pour la première mise en circulation.';
		}

		if ($errors) {
			return $this->json(['message' => implode(' ', $errors)], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		$vehicle = new Vehicle();
		$vehicle->setOwner($me);
		$vehicle->setPlateNumber($plate);
		$vehicle->setFirstRegistrationAt($firstRegAt); // non-nullable dans l’entité
		$vehicle->setBrand($brand);
		$vehicle->setModel($model);
		$vehicle->setSeats((int)$seatsInt);
		$vehicle->setIsElectric($isElectric);          // nullable
		$vehicle->setIsActive($isActive ?? true);      // par défaut true si non fourni

		$this->em->persist($vehicle);
		$this->em->flush();

		return $this->json([
			'message' => 'Véhicule créé.',
			'vehicle' => [
				'id' => $vehicle->getId(),
				'plateNumber' => $vehicle->getPlateNumber(),
				'firstRegistrationAt' => $vehicle->getFirstRegistrationAt()?->format(\DateTimeInterface::ATOM),
				'brand' => $vehicle->getBrand(),
				'model' => $vehicle->getModel(),
				'seats' => $vehicle->getSeats(),
				'isElectric' => $vehicle->isElectric(),
				'isActive' => $vehicle->isActive(),
			],
		], Response::HTTP_CREATED);
	}
}
