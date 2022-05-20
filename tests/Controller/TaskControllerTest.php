<?php

namespace App\Tests\Conttroller;

use App\Entity\Task;
use App\Entity\User;
use App\Tests\ControllerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    use ControllerTrait;

    /**
     * Test authenticated user can access tasks list page
     * @dataProvider userProvider
     */
    public function testAuthenticatedUserCanAccessTaskListPage(string $user): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $user]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test simple user see only his owned tasks
     */
    public function testSimpleUserSeeOnlyOwnedTasksList(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        $tasksCount = $user->getTasks()->count();

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, '/tasks');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount($tasksCount, $crawler->filter('div.thumbnail'));
    }

    /**
     * Test admin see all users tasks
     */
    public function testAdminSeeAllTasksList(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        $tasksCount = count($entityManager->getRepository(Task::class)->findAll());

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, '/tasks');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount($tasksCount, $crawler->filter('div.thumbnail'));
    }

    /**
     * Test buttons on listed tasks thumbnails
     * @dataProvider userProvider
     */
    public function testListedTaskThumbnailLinks(string $username): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $username]);

        $client->loginUser($user);

        $crawler = $client->request(Request::METHOD_GET, '/tasks');

        $this->assertSelectorTextContains('button.btn.btn-danger', 'Supprimer');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Marquer');
        $this->assertEquals(1, $crawler->filter('button:contains("Modifier")')->count());
    }

    /**
     * Test authenticated user can access task creation page
     * @dataProvider userProvider
     */
    public function testAuthenticatedUserCanAccessTaskCreationPage(string $user): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $user]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test create task form exist
     * @dataProvider userProvider
     */
    public function testCreateTaskFormExist(string $username): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $username]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/create');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
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

        $this->assertResponseRedirects('/tasks');

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
     * Test authenticated user can access task edition page
     * @dataProvider userProvider
     */
    public function testAuthenticatedUserCanAccessTaskEditionPage(string $user): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $user]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test user can't edit other user task
     */
    public function testUserCanNotEditOtherUserTask(): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/1/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    /**
     * Test admin can edit other user task
     */
    public function testAdminCanEditOtherUserTask(): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/1/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test edit task form exist
     * @dataProvider userProvider
     */
    public function testEditTaskFormExist(string $user): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $user]);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/1/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input#task_title');
        $this->assertSelectorExists('textarea#task_content');
        $this->assertSelectorExists('input#task__token');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Modifier');
    }

    /**
     * Test edit task form submit with valid datas
     * @dataProvider userProvider
     */
    public function testEditTaskFormWithValidDatas(string $user): void
    {
        $client  = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $user]);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/tasks/1/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]'   => 'new title',
            'task[content]' => 'new content'
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/tasks');

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
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => $user]);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/tasks/1/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]'   => '',
            'task[content]' => ''
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input#task_title');
        $this->assertSelectorExists('textarea#task_content');
        $this->assertSelectorExists('input#task__token');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Modifier');
        $this->assertSelectorTextContains('form', 'Vous devez saisir un titre');
        $this->assertSelectorTextContains('form', 'Vous devez saisir du contenu');
    }

    /**
     * Test authenticated simple user can toogle own task status
     * @dataProvider isDoneProvider
     */
    public function testAuthenticatedSimpleUserCanToggleTaskStatus(bool $isDone): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->find(1);

        $task->setIsDone($isDone);

        $taskState = $task->isDone();

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/1/toggle');

        /** @var Task $toggledTask */
        $toggledTask = $entityManager->getRepository(Task::class)->find(1);

        $this->assertEquals(!$taskState, $toggledTask->isDone());
        $this->assertResponseRedirects('/tasks');

        $client->followRedirect();

        $flashMessage = !$taskState ? 'marquée comme faite.' : 'marquée comme non terminée';

        $this->assertSelectorTextContains('div.alert.alert-success', $flashMessage);
    }

    /**
     * Test authenticated simple user can't toogle other user task
     */
    public function testAuthenticatedSimpleUserCanNotToggleOtherUserTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/1/toggle');

        $this->assertResponseRedirects('/tasks');
        $this->assertSelectorTextContains('div.alert', 'Vous n\'êtes pas authorisé à effectuer cette action');
    }

    /**
     * Test admin can toogle other user task
     */
    public function testAdminCanToggleOtherUserTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        /** @var Task $task */
        $task = $entityManager->getRepository(Task::class)->find(1);

        $taskState = $task->isDone();

        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/tasks/1/toggle');

        /** @var Task $toggledTask */
        $toggledTask = $entityManager->getRepository(Task::class)->find(1);

        $this->assertEquals(!$taskState, $toggledTask->isDone());
        $this->assertResponseRedirects('/tasks');

        $client->followRedirect();

        $flashMessage = !$taskState ? 'marquée comme faite.' : 'marquée comme non terminée';

        $this->assertSelectorTextContains('div.alert.alert-success', $flashMessage);
    }

    /**
     * Test simple user can delete own task
     */
    public function testSimpleUserCanDeleteOwnTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/1/delete');

        $this->assertResponseRedirects('/tasks');

        $client->followRedirects();

        $deletedTask = $entityManager->getRepository(Task::class)->find(1);

        $this->assertEquals(null, $deletedTask);
        $this->assertSelectorTextContains('div.alert', 'La tâche a bien été supprimée');
    }

    /**
     * Test simple user can't delete other user task
     */
    public function testSimpleUserCanNotDeleteOtherUserTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/tasks/1/delete');

        $this->assertResponseRedirects('/tasks');
        $this->assertSelectorTextContains('div.alert', 'Vous n\'êtes pas authorisé à effectuer cette action');
    }

    /**
     * Test admin can delete other user task
     */
    public function testAdminCanDeleteOtherUserTask(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/tasks/1/delete');

        $this->assertResponseRedirects('/tasks');

        $client->followRedirects();

        $deletedTask = $entityManager->getRepository(Task::class)->find(1);

        $this->assertEquals(null, $deletedTask);
        $this->assertSelectorTextContains('div.alert.alert-success', 'La tâche a bien été supprimée');
    }
}
