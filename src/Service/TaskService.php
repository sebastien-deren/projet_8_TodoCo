<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Service\UserService;
use App\Repository\TaskRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository,
        private UserService $userService,
        private TagAwareCacheInterface $cache
    ) {
    }

    public function findTodo(): array
    {
        $todoTasks = $this->cache->get('todoTasks', function (ItemInterface $item): array {
            $item->expiresAfter(3600);
            return  $this->taskRepository->tasksTodo();
        });
        return $todoTasks;
    }
    public function findDone(): array
    {
        $doneTasks = $this->cache->get('doneTasks', function (ItemInterface $item): array {
            $item->expiresAfter(3600);
            return  $this->taskRepository->tasksDone();
        });
        return $doneTasks;
    }
    public function toggle(Task $task): void
    {
        $this->deleteCacheIsDone($task->isDone());
        $task->toggle(!$task->isDone());
        $this->saveTask($task);
    }
    public function createTask(Task $task, User|null $currentUser): void
    {
        if (\is_null($currentUser)) {
            $currentUser = $this->taskRepository?->findOneByUsername('anonymous') ?? $this->UserService->createAnon();
        }
        $task->setCreator($currentUser);
        $this->saveTask($task);
    }
    public function saveTask(Task $task): void
    {
        $this->taskRepository->save($task, true);
        $this->deleteCacheIsDone($task->isDone());
    }
    public function removeTask(Task $task): void
    {
        $this->taskRepository->remove($task, true);
        $this->deleteCacheIsDone($task->isDone());
    }
    private function deleteCacheIsDone(bool $isDone): void
    {
        $isDone ? $this->cache->delete('donetasks') : $this->cache->delete('todoTasks');
    }
}
