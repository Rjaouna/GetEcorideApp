<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfilePhotoController extends AbstractController
{
	#[Route('/api/profile/photo', name: 'api_update_profile_photo', methods: ['POST'])]
	public function updateProfilePhoto(Request $request, EntityManagerInterface $em): JsonResponse
	{
		/** @var User $user */
		$user = $this->getUser();
		$file = $request->files->get('imageFile');

		if (!$file) {
			return $this->json(['error' => 'Aucun fichier reçu'], Response::HTTP_BAD_REQUEST);
		}

		try {
			$user->setImageFile($file);
			$em->persist($user);
			$em->flush();
			$user->setImageFile(null);


			return $this->json([
				'message' => 'Photo de profil mise à jour avec succès',
				'imageName' => $user->getImageName(),
				'imageUrl' => $this->generateUrl('app_front_home', [], UrlGeneratorInterface::ABSOLUTE_URL)
					. 'uploads/avatars/' . $user->getImageName(),
			]);
		} catch (\Throwable $e) {
			return $this->json([
				'error' => $e->getMessage(),
			], 500);
		}
	}

	#[Route('/api/profile/image', name: 'api_update_profile_image', methods: ['GET'])]
	public function getImageProfile(): JsonResponse
	{
		$user = $this->getUser();
		$image = $user->getImageName();
		return $this->json(['imageProfile' => $image]);
	}
}
