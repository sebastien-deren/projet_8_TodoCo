<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use App\Service\UserService;
use Doctrine\Persistence\ObjectManager;

use Doctrine\Bundle\FixturesBundle\Fixture;

/**
 * @codeCoverageIgnore
 */
class TaskFixtures extends Fixture
{
    public function __construct(private UserService $userService)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $anonymous = $manager->getRepository(User::class)->findOneBy(['username'=>'anonymous']) ?? $this->userService->createAnon();


        for ($i = 0; $i < 10; $i++) {
            $task = new Task();
            $task->setContent('content: ' . $i);
            $task->setTitle('Test: ' . $i);
            $task->setCreatedAt(new \DateTime());
            $task->setCreator($anonymous);
            $manager->persist($task);
        }



        $manager->flush();
    }
}
