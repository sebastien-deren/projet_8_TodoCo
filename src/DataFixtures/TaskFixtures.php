<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use App\Service\UserService;
use function PHPUnit\Framework\isNull;
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
        $anonymous = $manager->getRepository(User::class)?->findOneByUsername('anonymous') ?? $this->userService->createAnon();


        for ($i = 0; $i < 10; $i++) {
            $task = new Task();
            $task->setContent('content: ' . $i);
            $task->setTitle('Test: ' . $i);
            $task->setCreatedAt(new DateTimeImmutable());
            $task->setCreator($anonymous);
            $manager->persist($task);
        }



        $manager->flush();
    }
}
