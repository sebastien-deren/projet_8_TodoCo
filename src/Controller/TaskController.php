<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    #[Route(path: '/tasks', name: 'task_list')]
    public function listAction(ManagerRegistry $managerRegistry): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('task/list.html.twig', ['tasks' => $managerRegistry->getRepository(Task::class)->findAll()]);
    }

    #[Route(path: '/tasks/create', name: 'task_create')]
    public function createAction(Request $request,ManagerRegistry $managerRegistry): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //JUST CALL A SERVICE
            $em = $managerRegistry->getManager();

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/tasks/{id}/edit', name: 'task_edit')]
    public function editAction(Task $task, Request $request,ManagerRegistry $managerRegistry): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isValid()) {
            //NOT IN CONTROLLER
            $managerRegistry->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route(path: '/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTaskAction(Task $task,ManagerRegistry $managerRegistry): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        //ALL THAT AS NO PLACE IN CONTROLLER
        $task->toggle(!$task->isDone());
        $managerRegistry->getManager()->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    #[Route(path: '/tasks/{id}/delete', name: 'task_delete')]
    public function deleteTaskAction(Task $task, ManagerRegistry $managerRegistry): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        //NOT IN CONTROLLER
        $em = $managerRegistry->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
