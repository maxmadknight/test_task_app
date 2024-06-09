<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Data\TaskDTO;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\TaskIndexRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Task API",
 *     version="1.0.0"
 * )
 */
class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Get list of tasks",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in title and description",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort by field (e.g., 'title' or '-created_at' for descending)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=15
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function index(TaskIndexRequest $request): JsonResponse
    {
        $filters = $request->only(['status', 'priority']);
        $sort = $request->input('sort');
        $perPage = (int)$request->input('per_page', 15);

        if ($request->has('search')) {
            $tasks = $this->taskService->searchTasks(
                $request->get('search'),
                $perPage,
                $filters,
                $sort
            );
        } else {
            $tasks = $this->taskService->getAllTasks(
                $filters,
                $sort,
                $perPage
            );
        }

        return response()->json($tasks);
    }

    /**
     * @param Task $task
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/tasks/{task}",
     *     summary="Get task details",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function show(Task $task)
    {
        $task->load('subtasks');
        return response()->json($task);
    }

    /**
     * @param CreateTaskRequest $request
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a new task",
     *     tags={"Tasks"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TaskCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function store(CreateTaskRequest $request)
    {
        $taskDTO = TaskDTO::from($request->validated());

        $task = $this->taskService->createTask($taskDTO);

        return response()->json($task, 201);
    }

    /**
     * @param Task $task
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/tasks/{task}",
     *     summary="Delete a task",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Task deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        try {
            $this->taskService->deleteTask($task);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    /**
     * @param Task $task
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Patch(
     *     path="/api/tasks/{task}/complete",
     *     summary="Complete a task",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task completed successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function complete(Task $task)
    {
        $this->authorize('update', $task);

        if ($task->subtasks()
            ->where('status', '!=', TaskStatus::DONE->value)
            ->exists()
        ) {
            return response()->json(
                ['message' => 'Cannot complete a task with incomplete subtasks'],
                403
            );
        }

        try {
            $completedTask = $this->taskService->completeTask($task);
            return response()->json($completedTask);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    /**
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Put(
     *     path="/api/tasks/{task}",
     *     summary="Update a task",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TaskUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $taskDTO = TaskDTO::from($request->validated());

        $updatedTask = $this->taskService->updateTask($task, $taskDTO);

        return response()->json($updatedTask);
    }
}
