<?php
// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // 1) ADMIN
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPseudo('AdminUser');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin@123'));
        $manager->persist($admin);

        // 2) EMPLOYER (ROLE_EMLOYER)
        $employer = new User();
        $employer->setEmail('employer@example.com');
        $employer->setRoles(['ROLE_EMLOYER']);
        $employer->setPseudo('EmployerUser');
        $employer->setPassword($this->passwordHasher->hashPassword($employer, 'Employer@123'));
        $manager->persist($employer);

        // 3) PASSAGER (ROLE_PASSAGER)
        $passager = new User();
        $passager->setEmail('passager@example.com');
        $passager->setRoles(['ROLE_PASSAGER']);
        $passager->setPseudo('PassagerUser');
        $passager->setPassword($this->passwordHasher->hashPassword($passager, 'Passager@123'));
        $manager->persist($passager);

        // ----- DRIVERS -----
        $brands = [
            ['brand' => 'Renault',  'models' => ['Clio', 'Megane', 'Captur']],
            ['brand' => 'Peugeot',  'models' => ['208', '308', '3008']],
            ['brand' => 'Citroën',  'models' => ['C3', 'C4', 'C5 Aircross']],
            ['brand' => 'Tesla',    'models' => ['Model 3', 'Model Y']],
            ['brand' => 'Dacia',    'models' => ['Sandero', 'Duster']],
            ['brand' => 'Volkswagen', 'models' => ['Polo', 'Golf', 'Tiguan']],
        ];

        $driversCount = 5;

        for ($d = 1; $d <= $driversCount; $d++) {
            $driver = new User();
            $driver->setEmail(sprintf('driver%d@example.com', $d));
            $driver->setRoles(['ROLE_DRIVER']);
            $driver->setPseudo(sprintf('DriverUser%d', $d));
            $driver->setPassword($this->passwordHasher->hashPassword($driver, 'Driver@123'));
            $manager->persist($driver);

            // 3 à 5 véhicules par driver
            $vehiclesCount = $faker->numberBetween(3, 5);

            for ($i = 0; $i < $vehiclesCount; $i++) {
                $pick = $faker->randomElement($brands);

                $v = new Vehicle();
                $v->setOwner($driver);
                $v->setPlateNumber(strtoupper($faker->bothify('??-###-??'))); // ex: AA-123-AA

                $date = \DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-10 years', '-1 month')
                );
                if (method_exists($v, 'setFirstRegistrationAt')) {
                    $v->setFirstRegistrationAt($date);
                } else {
                    $v->setFirstRegistrationAt($date);
                }

                $v->setBrand($pick['brand']);
                $v->setModel($faker->randomElement($pick['models']));
                $v->setSeats($faker->numberBetween(4, 9));
                $v->setIsElectric($faker->boolean(25));
                $v->setIsActive($faker->boolean(90));

                if (method_exists($v, 'setCreatedBy')) {
                    $v->setCreatedBy($admin);
                }
                if (method_exists($v, 'setUpdatedBy')) {
                    $v->setUpdatedBy($admin);
                }

                $manager->persist($v);
            }
        }

        $manager->flush();
    }
}
