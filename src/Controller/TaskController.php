<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * TaskController class
 * @package App\Controller
 */
class TaskController extends AbstractController
{
    /**
     * @var TaskRepository
     */
    private TaskRepository $taskRepository;

    /**
     * TaskController constructor
     *
     * @param TaskRepository $taskRepository
     */
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * @Route("/tasks/todo", name="task_list_todo")
     * @Route("/tasks/done", name="task_list_done")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function todoListAction(Request $request): Response
    {
        $isDone = $request->attributes->get('_route') === 'task_list_done';

        return $this->render('task/list.html.twig', [
            'tasks' => $this->taskRepository->findBy(['isDone' => $isDone])
        ]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $this->taskRepository->create($task, $user);

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list_todo');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     *
     * @param Task    $task
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Task $task, Request $request): Response
    {
        $this->denyAccessUnlessGranted('TASK_EDIT', $task);

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskRepository->update($task);

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list_todo');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     *
     * @param Request $request
     * @param Task    $task
     *
     * @return RedirectResponse
     */
    public function toggleTaskAction(request $request, Task $task): RedirectResponse
    {
        $this->denyAccessUnlessGranted('TASK_TOGGLE', $task);

        $task->toggle(!$task->isDone());
        $this->taskRepository->toggleTask($task);

        $message = $task->isDone()
            ? 'La tâche %s a bien été marquée comme faite.'
            : 'La tâche %s a bien été marquée comme non terminée.';

        $this->addFlash('success', sprintf($message, $task->getTitle()));

        return str_contains($request->server->get('HTTP_REFERER'), '/tasks/done')
            ? $this->redirectToRoute('task_list_done')
            : $this->redirectToRoute('task_list_todo');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     *
     * @param Request $request
     * @param Task    $task
     *
     * @return RedirectResponse
     */
    public function deleteTaskAction(Request $request, Task $task): RedirectResponse
    {
        $this->denyAccessUnlessGranted('TASK_DELETE', $task);

        $this->taskRepository->remove($task);

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return str_contains($request->server->get('HTTP_REFERER'), '/tasks/done')
            ? $this->redirectToRoute('task_list_done')
            : $this->redirectToRoute('task_list_todo');
    }
}
