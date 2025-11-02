<?php

namespace App\Controller\Front;

use App\Repository\CarpoolingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class SearchRideController extends AbstractController
{
    #[Route('/front/search/ride', name: 'app_front_search_ride', methods: ['GET'])]
    public function index(
        Request $request,
        SessionInterface $session,
        CarpoolingRepository $repo
    ): Response {
        // 🔹 1. Récupérer les filtres envoyés depuis le formulaire d’accueil
        $filters = [
            'deparatureCity' => trim((string) $request->query->get('deparatureCity')),
            'arrivalCity'    => trim((string) $request->query->get('arrivalCity')),
            'deparatureAt'   => $request->query->get('deparatureAt'),
            'seatsAvaible'   => $request->query->getInt('seatsAvaible', 1), // ✅ valeur par défaut à 1
            'price'          => $request->query->get('price') !== null ? (float) $request->query->get('price') : null,
            'ecoTag'         => $request->query->getBoolean('ecoTag', false),
        ];

        // 🔹 2. Sauvegarder les filtres dans la session
        $session->set('carpool_search_filters', $filters);

        // 🔹 3. Rechercher les covoiturages correspondants
        $carpoolings = $repo->filterCarpoolings(
            $filters['deparatureCity'] ?: null,
            $filters['arrivalCity'] ?: null,
            $filters['deparatureAt'] ?: null,
            $filters['seatsAvaible'] > 0 ? $filters['seatsAvaible'] : 1, // ✅ minimum 1 place
            $filters['price'] ?: null,
            $filters['ecoTag'] ?: null
        );

        // 🔹 4. Rendre la vue avec les résultats
        return $this->render('front/search_ride/index.html.twig', [
            'filters' => $filters,
            'carpoolings' => $carpoolings,
        ]);
    }
}
