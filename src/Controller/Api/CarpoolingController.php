<?php 
namespace App\Controller\Api;

use App\Entity\Carpooling;
use App\Repository\CarpoolingRepository;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CarpoolingController extends AbstractController
{
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