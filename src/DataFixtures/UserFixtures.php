<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user= new User();
        $user->setEmail("email@g.com");
        $user->setPassword("password");
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
