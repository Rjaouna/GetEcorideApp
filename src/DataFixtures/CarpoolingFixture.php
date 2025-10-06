<?php
// src/DataFixtures/CarpoolingFixture.php

namespace App\DataFixtures;

use App\Entity\Carpooling;
use App\Repository\UserRepository;
use App\Repository\VehicleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CarpoolingFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly VehicleRepository $vehicleRepository,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 1) Récupérer les drivers (implémente findDrivers() dans UserRepository)
        $drivers = $this->userRepository->findDrivers();
        if (empty($drivers)) {
            // Pas de conducteurs -> rien à créer proprement
            return;
        }

        // 2) Tous les véhicules (fallback si un driver n'en a pas)
        $allVehicles = $this->vehicleRepository->findAll();

        // Liste de villes FR plausibles (peut être remplacée par $faker->city())
        $cities = [
            'Lille',
            'Paris',
            'Lyon',
            'Marseille',
            'Bordeaux',
            'Toulouse',
            'Nantes',
            'Rennes',
            'Strasbourg',
            'Nice',
            'Montpellier',
            'Rouen',
            'Reims',
            'Dijon',
            'Limoges',
            'Brest',
            'Amiens',
            'Tours',
            'Orléans',
            'Metz',
        ];

        for ($i = 0; $i < 15; $i++) {
            $carpooling = new Carpooling();

            // Driver aléatoire
            $driver = $drivers[array_rand($drivers)];

            // Tenter d'utiliser un véhicule du driver ; sinon, prendre n'importe lequel
            $driversVehicles = $this->vehicleRepository->findBy(['owner' => $driver]);
            $vehicle = !empty($driversVehicles)
                ? $driversVehicles[array_rand($driversVehicles)]
                : ($allVehicles ? $allVehicles[array_rand($allVehicles)] : null);

            // Si aucun véhicule dispo, on saute proprement cette itération
            if (!$vehicle) {
                continue;
            }

            $departureCity = $faker->randomElement($cities);
            do {
                $arrivalCity = $faker->randomElement($cities);
            } while ($arrivalCity === $departureCity);

            // Départ dans les 1–20 prochains jours, heure au quart d’heure
            $start = $faker->dateTimeBetween('+1 day', '+20 days');
            $start = \DateTimeImmutable::createFromMutable($start);
            $start = $start->setTime(
                (int)$start->format('H'),
                [0, 15, 30, 45][array_rand([0, 15, 30, 45])]
            );

            $durationHours = $faker->numberBetween(1, 8);
            $arrival = $start->modify(sprintf('+%d hours', $durationHours));

            // Sièges
            $seatsTotal   = $faker->numberBetween(1, 4);
            $seatsAvaible = $faker->numberBetween(0, $seatsTotal);

            // Prix simple : 8–45 €, deux décimales
            $price = round($faker->randomFloat(2, 8, 45), 2);

            // Statut parmi quelques valeurs convenables
            $status = $faker->randomElement(['draft', 'published', 'cancelled']);

            // EcoTag ~ 60% de chances
            $ecoTag = $faker->boolean(60);

            // Hydratation (⚠️ noms "deparature*" & "seatsAvaible" respectés)
            $carpooling
                ->setDriver($driver)
                ->setVehicle($vehicle)
                ->setDeparatureCity($departureCity)
                ->setArrivalCity($arrivalCity)
                ->setDeparatureAt($start)
                ->setArrivalAt($arrival)
                ->setSeatsTotal($seatsTotal)
                ->setSeatsAvaible($seatsAvaible)
                ->setPrice($price)
                ->setStatus($status)
                ->setEcoTag($ecoTag)
                ->setCreatedBy($driver)
                ->setUpdatedBy($driver)

            ;

            $manager->persist($carpooling);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }
}
