<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    /**
     * TaskRepository constructor
     * call parent constructor to be autowired in other classes or services
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Create a task
     *
     * @param Task $task
     *
     * @return void
     */
    public function create(Task $task, User $user = null): void
    {
        $task->setUser($user);

        $this->store($task);
    }

    /**
     * Update a task
     *
     * @param Task $task
     *
     * @return void
     */
    public function update(Task $task): void
    {
        $this->store($task);
    }

    /**
     * Toggle a task state
     *
     * @return void
     */
    public function toggleTask(Task $task): void
    {
        $this->store($task);
    }

    /**
     * Remove a task
     *
     * @param Task $task
     *
     * @return void
     */
    public function remove(Task $task): void
    {
        $this->_em->remove($task);
        $this->_em->flush();
    }

    /**
     * Persist and flush a task
     *
     * @param Task $task
     *
     * @return void
     */
    private function store(Task $task): void
    {
        $this->_em->persist($task);
        $this->_em->flush();
    }
}
