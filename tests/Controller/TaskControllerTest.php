<?php

namespace App\Tests\Conttroller;

use App\Entity\Task;
use App\Entity\User;
use App\Tests\ControllerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    use ControllerTrait;

    /**
     * @dataProvider userProvider
     */
    public function testTodoTasksListPageContentAndAuthenticatedUsersAccess(string $userName): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, $userName);

        $tasksCount          = count($this->getRepository($client, Task::class)->findBy(['isDone' => false]));
        $anonymousTasksCount = count($this->getRepository($client, Task::class)->findBy(['user' => null]));

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, '/tasks/todo');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('a.btn.btn-danger.log', 'Se déconnecter');
        $this->assertSelectorTextContains('h1', 'Liste des tâches non terminées');
        $this->assertSelectorExists('img.slide-image');
        $this->assertCount($tasksCount, $crawler->filter('div.thumbnail'));
        $this->assertSelectorTextContains('a.btn.btn-success', 'Tâches terminées');
        $this->assertSelectorTextContains('a.btn.btn-info', 'Nouvelle tâche');
        $this->assertSelectorTextContains('a.btn.btn-danger.btn-sm', 'Supprimer');
        $this->assertSelectorTextContains('a.btn.btn-success.btn-sm', 'Marquer comme terminée');
        $this->assertSelectorTextNotContains('a.btn.btn-success.btn-sm', 'Marquer non terminée');
        $this->assertNotEquals($tasksCount, $anonymousTasksCount);
    }

    /**
     * @dataProvider userProvider
     */
    public function testDoneTasksListPageContentAndAuthenticatedUsersAccess(string $userName): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, $userName);

        $tasksCount          = count($this->getRepository($client, Task::class)->findBy(['isDone' => true]));
        $anonymousTasksCount = count($this->getRepository($client, Task::class)->findBy(['user' => null]));

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, '/tasks/done');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('a.btn.btn-danger.log', 'Se déconnecter');
        $this->assertSelectorTextContains('h1', 'Liste des tâches faites');
        $this->assertSelectorExists('img.slide-image');
        $this->assertCount($tasksCount, $crawler->filter('div.thumbnail'));
        $this->assertSelectorTextContains('a.btn.btn-warning', 'Tâches non terminées');
        $this->assertSelectorTextContains('a.btn.btn-info', 'Nouvelle tâche');
        $this->assertSelectorTextContains('a.btn.btn-danger.btn-sm', 'Supprimer');
        $this->assertSelectorTextNotContains('a.btn.btn-success.btn-sm', 'Marquer comme terminée');
        $this->assertSelectorTextContains('a.btn.btn-success.btn-sm', 'Marquer comme non terminée');
        $this->assertNotEquals($tasksCount, $anonymousTasksCount);
    }

    /**
     * @dataProvider userProvider
     */
    public function testCreateTaskPageContent(string $userName): void
    {
        $client  = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, $userName);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/create');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('a.btn.btn-danger', 'Se déconnecter');
        $this->assertSelectorTextContains('h1', 'Créer une nouvelle tâche');
        $this->assertSelectorExists('img.slide-image');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input#task_title');
        $this->assertSelectorExists('textarea#task_content');
        $this->assertSelectorExists('input#task__token');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Ajouter');
    }

    /**
     * @dataProvider userProvider
     */
    public function testCreateTaskFormSubmitWithValidDatas(string $userName): void
    {
        $client  = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, $userName);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/tasks/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]'   => 'test title',
            'task[content]' => 'test content'
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/tasks/todo');

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'La tâche a été bien été ajoutée.');
    }

    /**
     * @dataProvider userProvider
     */
    public function testCreateTaskFormSubmitWithBlankDatas(string $userName): void
    {
        $client  = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, $userName);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/tasks/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]'   => '',
            'task[content]' => ''
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input#task_title');
        $this->assertSelectorExists('textarea#task_content');
        $this->assertSelectorExists('input#task__token');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Ajouter');
        $this->assertSelectorTextContains('form', 'Vous devez saisir un titre');
        $this->assertSelectorTextContains('form', 'Vous devez saisir du contenu');
    }

    /**
     * @dataProvider userProvider
     */
    public function testUserSettedOnTaskCreation(string $userName): void
    {
        $client  = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, $userName);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/tasks/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]'   => 'last_task',
            'task[content]' => 'content'
        ]);

        $client->submit($form);

        /** @var Task $lastTask */
        $lastTask = $this->getRepository($client, Task::class)->findOneBy(['title' => 'last_task']);

        $this->assertEquals($user->getId(), $lastTask->getUser()->getId());
    }

    public function testUsercanEditOwnTask(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_2');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => $user]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * @dataProvider userProvider
     */
    public function testUserCanNotEditOtherUserTask(string $userName): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, $userName);

        /** @var User $otherUser */
        $otherUser = $this->getUser($client, 'user_3');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => $otherUser]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUserCanNotEditAnonymousTask(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_2');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => null]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @dataProvider userProvider
     */
    public function testEditTaskPageContentAndAuthenticatedOwnerAccess(string $user): void
    {
        $client  = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_2');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => $user]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('a.btn.btn-danger', 'Se déconnecter');
        $this->assertSelectorTextContains('h1', 'Modifier ' . $task->getTitle());
        $this->assertSelectorExists('img.slide-image');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input#task_title');
        $this->assertSelectorExists('textarea#task_content');
        $this->assertSelectorExists('input#task__token');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Modifier');
    }

    public function testEditTaskFormWithValidDatas(): void
    {
        $client  = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_3');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => $user]);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]'   => 'new title',
            'task[content]' => 'new content'
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/tasks/todo');

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'La tâche a bien été modifiée.');
    }

    /**
     * @dataProvider userProvider
     */
    public function testEditTaskFormWithBlankDatas(string $user): void
    {
        $client  = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_2');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => $user]);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]'   => '',
            'task[content]' => ''
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains('form', 'Vous devez saisir un titre');
        $this->assertSelectorTextContains('form', 'Vous devez saisir du contenu');
    }

    /**
     * @dataProvider isDoneProvider
     */
    public function testUserCanToggleOwnTask(bool $isDone): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_2');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['isDone' => $isDone, 'user' => $user]);

        $taskStateBeforeToggle = $task->isDone();

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/toggle');

        /** @var Task $toggledTask */
        $toggledTask = $this->getRepository($client, Task::class)->find($task->getId());

        $taskStateAftertoggle = $toggledTask->isdone();

        $this->assertNotEquals($taskStateBeforeToggle, $taskStateAftertoggle);
        $this->assertResponseRedirects();

        $client->followRedirect();

        $this->assertSelectorExists('div.alert.alert-success');
    }

    public function testUserCanNotToggleOtherUserTask(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_2');

        /** @var User $otherUser */
        $otherUser = $this->getUser($client, 'user_5');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => $otherUser]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, ('/tasks/' . $task->getId() . '/toggle'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUserCanDeleteOwnTask(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_2');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => $user]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/delete');

        $this->assertNull($task->getId());
        $this->assertResponseRedirects('/tasks/todo');

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'La tâche a bien été supprimée.');
    }

    public function testUserCanNotDeleteOtherUserTask(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_2');

        /** @var User $otherUser */
        $otherUser = $this->getUser($client, 'user_3');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => $otherUser]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, ('/tasks/' . $task->getId() . '/delete'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testNonAdminUserCanNotDeleteAnonymousTask(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_2');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => null]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, ('/tasks/' . $task->getId() . '/delete'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminCanDeleteAnonymousTask(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'admin_1');

        /** @var Task $task */
        $task = $this->getRepository($client, Task::class)->findOneby(['user' => null]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, ('/tasks/' . $task->getId() . '/delete'));

        $this->assertResponseRedirects();
        $this->assertNull($task->getId());
    }
}
