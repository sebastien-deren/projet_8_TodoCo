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
    public const ANONYMOUS_USER ='anonymous-user';
    public function __construct(private UserPasswordHasherInterface $hasher)
    {


    }
    public function load(ObjectManager $manager ): void
    {
        $user= new User();
        $user->setEmail("anonymous@TODO.com");
        $user->setUsername("anonymous");
        $user->setPassword('');
        $manager->persist($user);
        $this->addReference(self::ANONYMOUS_USER, $user);

        $user= new User();
        $user->setEmail("email@g.com");
        $user->setPassword($this->hasher->hashPassword($user,"password"));
        $user->setUsername("seb");
        $manager->persist($user);
        // $product = new Product();
        // $manager->persist($product);
        $user= new User();
        $user->setEmail("test@g.com");
        $user->setPassword("test");
        $user->setUsername("test");
        $manager->persist($user);

        $manager->flush();
    }
}
