<?php
declare(strict_types=1);

namespace App\Services;

use App\Data\TaskDTO;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TaskService
{
    public function getAllTasks(array $filters, ?string $sort, int $perPage): LengthAwarePaginator
    {
        $query = Task::query();

        /** Get only tol level tasks */
        $query->whereParentId(null);

        if (isset($filters['status'])) {
            $query->whereStatus($filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->wherePriority($filters['priority']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($sort) {
            $sortFields = explode(',', $sort);
            foreach ($sortFields as $sortField) {
                $direction = 'asc';
                if (str_starts_with($sortField, '-')) {
                    $direction = 'desc';
                    $sortField = substr($sortField, 1);
                }
                $query->orderBy($sortField, $direction);
            }
        }

        return $query->paginate($perPage);
    }

    public function searchTasks(string $query, int $perPage, ?array $filter, ?string $sort): LengthAwarePaginator
    {
        $search = Task::search($query);
        $columns = Schema::getColumnListing((new Task)->getTable());
        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                if (in_array($key, $columns)) {
                    $search->where($key, $value);
                }
            }
        }

        if (!empty($sort)) {
            if (Str::contains($sort, ',')) {
                $sort = explode(',', $sort);
                foreach ($sort as $field) {
                    if (!empty($field)) {
                        $direction = 'asc';
                        $field = trim($field);
                        if (Str::contains($field, ':')) {
                            $field = explode(':', $field);
                            $direction = $field[1];
                            $field = $field[0];
                        }
                        $search->orderBy($field, $direction);
                    }
                }
            } else {
                $direction = 'asc';
                if (Str::contains($sort, ':')) {
                    $sort = explode(':', $sort);
                    $direction = $sort[1];
                    $sort = $sort[0];
                }
                $search->orderBy($sort, $direction);
            }
        }

        return $search->paginate($perPage);
    }

    public function createTask(TaskDTO $taskDTO): Task
    {
        return Task::createFromDTO($taskDTO, Auth::id());
    }

    public function updateTask(Task $task, TaskDTO $taskDTO): Task
    {
        $task->update($taskDTO->toArray());
        return $task;
    }

    public function deleteTask(Task $task): void
    {
        if ($task->status == 'done') {
            throw new \Exception('Cannot delete a completed task');
        }

        $task->delete();
    }

    public function completeTask(Task $task): Task
    {
        if ($task->subtasks()->where('status', '!=', 'done')->exists()) {
            throw new \Exception('Cannot complete a task with incomplete subtasks');
        }

        $task->update(['status' => 'done', 'completed_at' => now()]);
        return $task;
    }
}
