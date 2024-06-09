<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="TaskCreateRequest",
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
class CreateTaskRequest extends FormRequest
{
    public string $title;
    public ?string $description;
    public string $status;
    public int $priority;
    public ?int $parent_id;

    public function authorize()
    {
        return true; // Adjust authorization logic as needed
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|in:' . implode(',', data_get(TaskStatus::cases(), '*.value')),
            'priority' => 'required|integer|in:' . implode(',', data_get(TaskPriority::cases(), '*.value')),
            'parent_id' => 'nullable|exists:tasks,id',
        ];
    }
}
