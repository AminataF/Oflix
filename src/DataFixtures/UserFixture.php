<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements FixtureGroupInterface

{
    private $passwordhasher;

    // ? on utilise le constructeur pour utiliser l'injection de dépendance
    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->passwordhasher = $userPasswordHasherInterface;
    }

    // ! on ne peux pas modifier la méthode car cette méthode doit être compatible avec Doctrine
    // ! is not compatible with method 'Doctrine\Bundle\FixturesBundle\Fixture::load()
    public function load(ObjectManager $manager): void
    {
        $newUser = new User();
        $newUser->setEmail("user@user.com");
        $newUser->setRoles(["ROLE_USER"]);
        
        $pw = $this->passwordhasher->hashPassword($newUser, "user");
        $newUser->setPassword($pw);

        $manager->persist($newUser);

        $newUser = new User();
        $newUser->setEmail("admin@admin.com");
        $newUser->setRoles(["ROLE_ADMIN"]);
        
        $pw = $this->passwordhasher->hashPassword($newUser, "admin");
        $newUser->setPassword($pw);

        $manager->persist($newUser);

        $newUser = new User();
        $newUser->setEmail("manager@manager.com");
        $newUser->setRoles(["ROLE_MANAGER"]);
        
        $pw = $this->passwordhasher->hashPassword($newUser, "manager");
        $newUser->setPassword($pw);

        $manager->persist($newUser);

        $manager->flush();
    }
    
    // ? https://symfony.com/blog/new-in-fixturesbundle-group-your-fixtures
    public static function getGroups(): array
    {
        return ['usergroup'];
    }
}
