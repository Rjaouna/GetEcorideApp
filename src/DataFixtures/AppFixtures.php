<?php
// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
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

        // 4) DRIVER (ROLE_DRIVER)
        $driver = new User();
        $driver->setEmail('driver@example.com');
        $driver->setRoles(['ROLE_DRIVER']);
        $driver->setPseudo('DriverUser');

        $driver->setPassword($this->passwordHasher->hashPassword($driver, 'Driver@123'));
        $manager->persist($driver);

        $manager->flush();
    }
}
