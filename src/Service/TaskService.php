<?php
declare(strict_types=1);
namespace App\Service;

use App\Entity\Task;
use App\Repository\TaskRepository;

class TaskService
{

    public function __construct(
        private TaskRepository $taskRepository
    ){}
    public function toggle(Task $task)
    {
        $task->toggle(!$task->isDone());
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
