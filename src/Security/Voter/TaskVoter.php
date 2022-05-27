<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TaskVoter extends Voter
{
    public const EDIT   = 'TASK_EDIT';
    public const TOGGLE = 'TASK_TOGGLE';
    public const DELETE = 'TASK_DELETE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @see Voter
     */
    protected function supports(string $attribute, $task): bool
    {
        return in_array($attribute, [self::EDIT, self::TOGGLE, self::DELETE])
            && $task instanceof \App\Entity\Task;
    }

    /**
     * @see Voter
     */
    protected function voteOnAttribute(string $attribute, $task, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($task, $user);
                break;
            case self::TOGGLE:
                return $this->canToggle($task, $user);
                break;
            case self::DELETE:
                return $this->canDelete($task, $user);
                break;
        }

        return false;
    }

    /**
     * Check if task can be edited
     *
     * @param Task $task
     * @param UserInterface $user
     *
     * @return boolean
     */
    private function canEdit(Task $task, UserInterface $user): bool
    {
        return $user === $task->getUser();
    }

    /**
     * Check if task can be toggled
     *
     * @param Task $task
     * @param UserInterface $user
     *
     * @return boolean
     */
    private function canToggle(Task $task, UserInterface $user): bool
    {
        return
            $user === $task->getUser()
            ||
            $this->security->isGranted('ROLE_ADMIN') && $task->getUser() === null;
    }

    /**
     * Checks if task can be deleted
     *
     * @param Task $task
     * @param UserInterface $user
     *
     * @return boolean
     */
    private function canDelete(Task $task, UserInterface $user): bool
    {
        return
            $user === $task->getUser()
            ||
            $this->security->isGranted('ROLE_ADMIN') && $task->getUser() === null;
    }
}
