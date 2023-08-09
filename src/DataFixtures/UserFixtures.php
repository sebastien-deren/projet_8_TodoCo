<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {


    }
    public function load(ObjectManager $manager ): void
    {
        $user= new User();
        $user->setEmail("email@g.com");
        $user->setPassword($this->hasher->hashPassword($user,"password"));
        $user->setUsername("seb");
        $manager->persist($user);
        $user= new User();
        $user->setEmail("test@g.com");
        $user->setPassword("test");
        $user->setUsername("test");
        $manager->persist($user);

        $manager->flush();
    }
}
