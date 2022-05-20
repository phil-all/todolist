<?php

namespace App\Tests\Entity;

use DateTime;
use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests on Task entity
 */
class TaskTest extends TestCase
{
    private Task $task;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = new Task();
        $this->user = new User();
    }

    public function testTaskGettersAndSetters(): void
    {
        $title      = 'title';
        $content    = 'content';
        $createdAt  = new DateTime();
        $isDone     = true;
        $toogleFlag = false;

        $this->task
            ->setTitle($title)
            ->setContent($content)
            ->setCreatedAt($createdAt)
            ->setIsDone($isDone)
            ->setUser($this->user);

        $this->assertEquals($title, $this->task->getTitle());
        $this->assertEquals($content, $this->task->getContent());
        $this->assertEquals($createdAt, $this->task->getCreatedAt());
        $this->assertEquals($isDone, $this->task->isDone());
        $this->assertEquals($this->user, $this->task->getUser());
        $this->assertNull($this->task->getId());

        $this->task->toggle($toogleFlag);

        $this->assertEquals($toogleFlag, $this->task->isDone());
    }
}
