<?php 
namespace App\Controller\Api;

use App\Entity\Carpooling;
use App\Repository\CarpoolingRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CarpoolingController extends AbstractController
{
	#[Route('carpoolings/filter', name: 'carpooling_filter', methods: ['GET'])]
	public function filter(
		Request $request,
		CarpoolingRepository $carpoolingRepository,
		SerializerInterface $serializer
	): Response {
		// 1️⃣ Récupérer les paramètres du formulaire
		$departureCity = $request->query->get('deparatureCity');
		$arrivalCity = $request->query->get('arrivalCity');
		$departureAt = $request->query->get('deparatureAt');
		$seatsAvaible = $request->query->getInt('seatsAvaible');
		$price = $request->query->get('price');
		$ecoTag = $request->query->getBoolean('ecoTag');

		// 2️⃣ Construire un tableau de critères dynamiquement
		$criteria = [];
		if ($departureCity) {
			$criteria['deparatureCity'] = $departureCity;
		}
		if ($arrivalCity) {
			$criteria['arrivalCity'] = $arrivalCity;
		}
		if ($ecoTag) {
			$criteria['ecoTag'] = true;
		}

		// 3️⃣ Appeler le repository avec une méthode personnalisée
		$results = $carpoolingRepository->findByFilter(
			$criteria,
			$departureAt ? new \DateTimeImmutable($departureAt) : null,
			$seatsAvaible ?: null,
			$price ?: null
		);

		// 4️⃣ Retour JSON (API)
		$json = $serializer->serialize($results, 'json', ['groups' => ['carpooling.index']]);
		return new Response($json, 200, ['Content-Type' => 'application/json']);
	}

	#[Route("/api/carpoolings")]
	public function index(CarpoolingRepository $carpooling_repository)
	{
		$carpooling = $carpooling_repository->findAll();
		return $this->json($carpooling, 200, [], ['groups' => ['carpooling.index']]);
		
	}

	#[Route('/api/carpoolings/{id}', name: 'api_carpooling_show')]
	public function show(CarpoolingRepository $repo, int $id): Response
	{

		$carpooling = $repo->find($id);

		if (!$carpooling) {
			throw $this->createNotFoundException('Trajet introuvable.');
		}

		return $this->json($carpooling, 200, [], ['groups' => ['carpooling.index']]);
	}
}