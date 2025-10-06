<?php 
namespace App\Controller\Api;

use App\Entity\Carpooling;
use App\Repository\CarpoolingRepository;
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

}