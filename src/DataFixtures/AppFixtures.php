<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\Carpooling;
use App\Entity\DriverPreferences;
use App\Entity\DriverReview;
use App\Entity\Wallet;
use App\Entity\WalletTransaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $users = [];
        $vehicles = [];
        $carpoolings = [];

        // ---------------------------
        // 👤 1. Création de 20 Users
        // ---------------------------
        for ($i = 1; $i <= 20; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setPhone($faker->phoneNumber());
            $user->setPseudo($faker->userName());
            $user->setAddress($faker->address());
            $user->setDateOfBirth(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-60 years', '-18 years')));
            $user->setIsVerified(true);
            $user->setRoles(['ROLE_USER']);

            // 🔹 Préférences conducteur
            $pref = new DriverPreferences();
            $pref->setUser($user);
            $pref->setSmokingAllowed($faker->boolean());
            $pref->setPetsAllowed($faker->boolean());
            $user->setDriverPreferences($pref);
            $manager->persist($pref);

            // 💰 Wallet
            $wallet = new Wallet();
            $wallet->setOwner($user);
            $wallet->setBalance($faker->numberBetween(20, 500));

            // Transactions aléatoires
            $transactionCount = $faker->numberBetween(1, 3);
            for ($t = 0; $t < $transactionCount; $t++) {
                $transaction = new WalletTransaction();
                $transaction->setWallet($wallet);
                $transaction->setType($faker->randomElement(['credit', 'debit']));
                $transaction->setAmount($faker->numberBetween(5, 150));
                $manager->persist($transaction);
            }

            $user->setWallet($wallet);
            $manager->persist($wallet);
            $manager->persist($user);
            $users[] = $user;
        }

        // ---------------------------
        // 🚗 2. Création de 20 Vehicles
        // ---------------------------
        for ($i = 1; $i <= 20; $i++) {
            $vehicle = new Vehicle();
            $vehicle->setOwner($faker->randomElement($users));
            $vehicle->setPlateNumber(strtoupper($faker->bothify('??-###-??')));
            $vehicle->setBrand($faker->randomElement(['Renault', 'Peugeot', 'Tesla', 'BMW', 'Toyota', 'Volkswagen', 'Audi', 'Citroën']));
            $vehicle->setModel($faker->word());
            $vehicle->setSeats($faker->numberBetween(3, 7));
            $vehicle->setIsElectric($faker->boolean(30)); // 30% électriques
            $vehicle->setIsActive(true);
            $vehicle->setFirstRegistrationAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-8 years', '-1 years')));
            $manager->persist($vehicle);
            $vehicles[] = $vehicle;
        }

        // ---------------------------
        // 🤝 3. Création de 20 Carpoolings
        // ---------------------------
        $cities = ['Lille', 'Paris', 'Lyon', 'Marseille', 'Toulouse', 'Bordeaux', 'Nice', 'Nantes', 'Dijon', 'Rouen'];

        for ($i = 1; $i <= 20; $i++) {
            $carpooling = new Carpooling();
            $carpooling->setDriver($faker->randomElement($users));
            $carpooling->setVehicle($faker->randomElement($vehicles));

            $departureCity = $faker->randomElement($cities);
            do {
                $arrivalCity = $faker->randomElement($cities);
            } while ($arrivalCity === $departureCity);

            $carpooling->setDeparatureCity($departureCity);
            $carpooling->setArrivalCity($arrivalCity);

            $departureDate = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+15 days'));
            $arrivalDate = $departureDate->modify('+' . rand(2, 6) . ' hours');

            $carpooling->setDeparatureAt($departureDate);
            $carpooling->setArrivalAt($arrivalDate);
            $carpooling->setSeatsTotal($faker->numberBetween(2, 5));
            $carpooling->setSeatsAvaible($faker->numberBetween(1, 3));
            $carpooling->setPrice($faker->randomFloat(2, 5, 50));
            $carpooling->setStatus($faker->randomElement(['open', 'closed', 'cancelled']));
            $carpooling->setEcoTag($faker->boolean());

            // Participants aléatoires
            $nbParticipants = $faker->numberBetween(1, 4);
            $participants = $faker->randomElements($users, $nbParticipants);
            foreach ($participants as $p) {
                $carpooling->addParticipant($p);
            }

            $manager->persist($carpooling);
            $carpoolings[] = $carpooling;
        }

        // ---------------------------
        // ⭐ 4. Création de 30 DriverReviews
        // ---------------------------
        foreach ($carpoolings as $trip) {
            $reviewCount = $faker->numberBetween(1, 3);

            for ($r = 0; $r < $reviewCount; $r++) {
                $review = new DriverReview();
                $review->setTrip($trip);
                $review->setRater($faker->randomElement($users));
                $review->setRating($faker->randomElement(['5', '4', '3', '2', '1']));
                $review->setComment($faker->optional()->sentence($faker->numberBetween(10, 20)));

                $manager->persist($review);
            }
        }

        $manager->flush();
    }
}
