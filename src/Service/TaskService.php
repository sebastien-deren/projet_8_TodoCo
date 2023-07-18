<?php
declare(strict_types=1);
namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Service\UserService;
use App\Repository\TaskRepository;

class TaskService
{

    public function __construct(
        private TaskRepository $taskRepository,
        private UserService $userService
    ){}
    public function toggle(Task $task)
    {
        $task->toggle(!$task->isDone());
        $this->saveTask($task);
    }
    public function createTask(Task $task,User|null $currentUser){
        if(\is_null($currentUser)){
            $currentUser = $this->taskRepository?->findOneByUsername('anonymous') ?? $this->UserService->createAnon();
        }
        $task->setCreator($currentUser);
        $this->saveTask($task);

    }
    public function saveTask(Task $task)
    {
        $this->taskRepository->save($task,true);
    }
    public function removeTask(Task $task)
    {
        $this->taskRepository->remove($task,true);
    }
}
