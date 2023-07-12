<?php

namespace App\DataFixtures;

use App\Entity\Task;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
       
        for($i=0; $i<10;$i++){
            $task=new Task();
            $task->setContent('content: '.$i);
            $task->setTitle('Test: '.$i);
            $task->setCreatedAt(new DateTimeImmutable());
            $task->setCreator($this->getReference(UserFixtures::ANONYMOUS_USER));
            $manager->persist($task);
        }



        $manager->flush();
    }
}
