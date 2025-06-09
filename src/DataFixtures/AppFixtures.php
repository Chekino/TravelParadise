<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ADMIN
        $admin = new User();
        $admin->setEmail('admin@site.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setNom('Admin');
        $admin->setPrenom('Super');
        $admin->setPassword($this->hasher->hashPassword($admin, 'adminpass'));
        $manager->persist($admin);

        // UTILISATEUR
        $user = new User();
        $user->setEmail('user@site.com');
        $user->setRoles(['ROLE_USER']);
        $user->setNom('User');
        $user->setPrenom('Normal');
        $user->setPassword($this->hasher->hashPassword($user, 'userpass'));
        $manager->persist($user);

        // GUIDE
        $guide = new User();
        $guide->setEmail('guide@site.com');
        $guide->setRoles(['ROLE_GUIDE']);
        $guide->setNom('Guide');
        $guide->setPrenom('Mobile');
        $guide->setPassword($this->hasher->hashPassword($guide, 'guidepass'));
        $guide->setPhoto('guide.jpg');
        $guide->setPaysAffectation('France');
        $guide->setStatut(true);
        $manager->persist($guide);

        $manager->flush();
    }
}
