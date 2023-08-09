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
        $user->setUsername("admin");
        $user->setRoles(['ROLE_USER','ROLE_ADMIN']);
        $manager->persist($user);
        $user= new User();
        $user->setEmail("test@g.com");
        $user->setPassword("test");
        $user->setUsername("test");
        $manager->persist($user);
        $user = new User();
        $user->setUsername('user');
        $user->setEmail('user@g.com');
        $user->setPassword($this->hasher->hashPassword($user,"password"));
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);
        $manager->flush();
    }
}
