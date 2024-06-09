<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="TaskUpdateRequest",
 *     type="object",
 *     required={"title", "priority", "status"},
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         example="New Task"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="This is a new task"
 *     ),
 *     @OA\Property(
 *         property="priority",
 *         type="integer",
 *         example=3
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="todo"
 *     ),
 *     @OA\Property(
 *         property="parent_id",
 *         type="integer",
 *         nullable=true,
 *         example=null
 *     )
 * )
 */
class UpdateTaskRequest extends FormRequest
{
    public string $title;
    public ?string $description;
    public string $status;
    public int $priority;
    public ?int $parent_id;

    public function authorize()
    {
        return $this->user()->can('update', $this->task);
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|required|integer|in:'.implode(',', data_get(TaskPriority::cases(), '*.value')),
            'status' => 'sometimes|required|string|in:'.implode(',', data_get(TaskStatus::cases(), '*.value')),
            'parent_id' => 'nullable|exists:tasks,id',
        ];
    }
}
