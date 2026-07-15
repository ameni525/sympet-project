<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setNom('Admin')
            ->setPrenom('SymPet')
            ->setEmail('admin@sympet.local')
            ->setRoles(['ROLE_ADMIN'])
            ->setIsVerified(true)
            ->setPassword($this->passwordHasher->hashPassword($admin, 'Admin1234!'));
        $manager->persist($admin);

        $client = new User();
        $client->setNom('Client')
            ->setPrenom('Demo')
            ->setEmail('client@sympet.local')
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(true)
            ->setPassword($this->passwordHasher->hashPassword($client, 'Client1234!'));
        $manager->persist($client);

        $manager->flush();
    }
}
