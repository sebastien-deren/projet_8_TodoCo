<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\TaskService;
use App\Service\UserService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    #[Route(path: '/tasks/todo', name: 'task_list_todo', methods: ['GET'])]
    public function todoList(TaskService $taskService): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('task/list.html.twig', ['tasks' => $taskService->findTodo()]);
    }
    #[Route(path: '/tasks/done', name: 'task_list_done', methods: ['GET'])]
    public function doneList(TaskService $taskService): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('task/list.html.twig', ['tasks' => $taskService->findDone()]);
    }

    #[Route(path: '/tasks/create', name: 'task_create', methods: ['GET', 'POST'])]
    public function createAction(Request $request, TaskService $taskService): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $taskService->createTask($task, $this->getUser());

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/tasks/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function editAction(Task $task, Request $request, TaskService $taskService): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taskService->saveTask($task);

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route(path: '/tasks/{id}/toggle', name: 'task_toggle', methods: ['GET'])]
    public function toggleTaskAction(Task $task, TaskService $taskService): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $taskService->toggle($task);
        $stringDone = $task->isDone() ? '' : 'non';
        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme  %s faite.', $task->getTitle(), $stringDone));

        return $task->isDone() ? $this->redirectToRoute('task_list_done') : $this->redirectToRoute('task_list_todo');
    }

    #[Route(path: '/tasks/{id}/delete', name: 'task_delete', methods: ['GET'])]
    public function deleteTaskAction(Task $task, TaskService $taskService, UserService $userService)
    {
        $isDeletable = $userService->canDeleteTask($task->getCreator(), $this->getUser(), $this->isGranted('ROLE_ADMIN'));
        if (!$isDeletable) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette tâche');
            return $this->redirectToRoute('task_list');
        }

        $taskService->removeTask($task);

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
