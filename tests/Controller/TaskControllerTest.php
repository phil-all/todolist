<?php

namespace App\Tests\Conttroller;

use App\Entity\Task;
use App\Entity\User;
use App\Tests\ControllerTrait;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class TaskControllerTest extends WebTestCase
{
    use ControllerTrait;

    /**
     * Test todo tasks list page content and authenticated user access
     * @dataProvider userProvider
     */
    public function testTodoTasksListPageContentAndAuthenticatedUsersAccess(string $userName): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $userName]);

        $tasksCount          = count($entityManager->getRepository(Task::class)->findBy(['isDone' => false]));
        $anonymousTasksCount = count($entityManager->getRepository(Task::class)->findBy(['user' => null]));

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, '/tasks/todo');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('a.btn.btn-danger', 'Se déconnecter');
        $this->assertSelectorTextContains('h1', 'Liste des tâches non terminées');
        $this->assertSelectorExists('img.slide-image');
        $this->assertCount($tasksCount, $crawler->filter('div.thumbnail'));
        $this->assertSelectorTextContains('a.btn.btn-success', 'Consulter la liste des taches faites');
        $this->assertSelectorTextContains('a.btn.btn-info', 'Créer une tâche');
        $this->assertSelectorTextContains('button.btn.btn-danger', 'Supprimer');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Marquer comme faite');
        $this->assertSelectorTextNotContains('button.btn.btn-success', 'Marquer non terminée');
        $this->assertNotEquals($tasksCount, $anonymousTasksCount);
    }

    /**
     * Test done tasks list page content and authenticated user access
     * @dataProvider userProvider
     */
    public function testDoneTasksListPageContentAndAuthenticatedUsersAccess(string $userName): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $userName]);

        $tasksCount          = count($entityManager->getRepository(Task::class)->findBy(['isDone' => true]));
        $anonymousTasksCount = count($entityManager->getRepository(Task::class)->findBy(['user' => null]));

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, '/tasks/done');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('a.btn.btn-danger', 'Se déconnecter');
        $this->assertSelectorTextContains('h1', 'Liste des tâches faites');
        $this->assertSelectorExists('img.slide-image');
        $this->assertCount($tasksCount, $crawler->filter('div.thumbnail'));
        $this->assertSelectorTextContains('a.btn.btn-warning', 'Consulter la liste des taches non terminées');
        $this->assertSelectorTextContains('a.btn.btn-info', 'Créer une tâche');
        $this->assertSelectorTextContains('button.btn.btn-danger', 'Supprimer');
        $this->assertSelectorTextNotContains('button.btn.btn-success', 'Marquer comme faite');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Marquer non terminée');
        $this->assertNotEquals($tasksCount, $anonymousTasksCount);
    }

    /**
     * Test create task page content and authenticated user access
     * @dataProvider userProvider
     */
    public function testCreateTaskPageContent(string $username): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $username]);

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
     * Test create task form submit with valid datas
     * @dataProvider userProvider
     */
    public function testCreateTaskFormSubmitWithValidDatas(string $username): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $username]);

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
     * Test create task form submit with blank datas
     * @dataProvider userProvider
     */
    public function testCreateTaskFormSubmitWithBlankDatas(string $username): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $username]);

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
     * Test user setted on task creation
     * @dataProvider userProvider
     */
    public function testUserSettedOnTaskCreation(string $username): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $username]);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/tasks/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]'   => 'last_task',
            'task[content]' => 'content'
        ]);

        $client->submit($form);

        /** @var Task $lastTask */
        $lastTask = $entityManager->getRepository(Task::class)->findOneBy(['title' => 'last_task']);

        $this->assertEquals($user->getId(), $lastTask->getUser()->getId());
    }

    /**
     * Test user can edit own task
     */
    public function testUsercanEditOwnTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => $user]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test user can't edit other user task
     * @dataProvider userProvider
     */
    public function testUserCanNotEditOtherUserTask(string $userName): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $userName]);

        /** @var User $otherUser */
        $otherUser = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_3']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => $otherUser]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test user can't edit anonymous task
     */
    public function testUserCanNotEditAnonymousTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => null]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test edit task page content and authenticated owner access
     * @dataProvider userProvider
     */
    public function testEditTaskPageContentAndAuthenticatedOwnerAccess(string $user): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => $user]);

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

    /**
     * Test edit task form submit with valid datas
     */
    public function testEditTaskFormWithValidDatas(): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_3']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => $user]);

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
     * Test edit task form submit with blank datas
     * @dataProvider userProvider
     */
    public function testEditTaskFormWithBlankDatas(string $user): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => $user]);

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
     * Test user can toogle own task status
     * @dataProvider isDoneProvider
     */
    public function testUserCanToggleOwnTask(bool $isDone): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['isDone' => $isDone, 'user' => $user]);

        $taskStateBeforeToggle = $task->isDone();

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/toggle');

        /** @var Task $toggledTask */
        $toggledTask = $entityManager->getRepository(Task::class)->find($task->getId());

        $taskStateAftertoggle = $toggledTask->isdone();

        $this->assertNotEquals($taskStateBeforeToggle, $taskStateAftertoggle);
        $this->assertResponseRedirects();

        $client->followRedirect();

        $this->assertSelectorExists('div.alert.alert-success');
    }

    /**
     * Test user can't toogle other user task
     */
    public function testUserCanNotToggleOtherUserTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        /** @var User $otherUser */
        $otherUser = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_5']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => $otherUser]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, ('/tasks/' . $task->getId() . '/toggle'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test user can delete own task
     */
    public function testUserCanDeleteOwnTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => $user]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/' . $task->getId() . '/delete');

        $this->assertNull($task->getId());
        $this->assertResponseRedirects('/tasks/todo');

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'La tâche a bien été supprimée.');
    }

    /**
     * Test user can't delete other user task
     */
    public function testUserCanNotDeleteOtherUserTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        /** @var User $otherUser */
        $otherUser = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_3']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => $otherUser]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, ('/tasks/' . $task->getId() . '/delete'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test non admin user can't delete anonymous task
     */
    public function testNonAdminUserCanNotDeleteAnonymousTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => null]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, ('/tasks/' . $task->getId() . '/delete'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test admin can delete anonymous task
     */
    public function testAdminCanDeleteAnonymousTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->findOneby(['user' => null]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, ('/tasks/' . $task->getId() . '/delete'));

        $this->assertResponseRedirects();
        $this->assertNull($task->getId());
    }
}
