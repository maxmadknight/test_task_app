<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Allow to update only own task
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    /**
     * Check does user can delete task
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id &&
            TaskStatus::tryFrom($task->status) !== TaskStatus::DONE;
    }

    /**
     * Check does user can complete task
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function complete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id &&
            !$task->subtasks()->where('status', TaskStatus::TODO->value)->exists();
    }
}
