<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\TaskDTO;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use OpenApi\Annotations as OA;

/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $description
 * @property string|TaskStatus $status
 * @property int $priority
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $completed_at
 * @property int|null $parent_id
 * @property-read Task|null $parent
 * @property-read Collection<int, Task> $subtasks
 * @property-read int|null $subtasks_count
 * @property-read User $user
 * @method static TaskFactory factory($count = null, $state = [])
 * @method static Builder|Task newModelQuery()
 * @method static Builder|Task newQuery()
 * @method static Builder|Task query()
 * @method static Builder|Task whereCompletedAt($value)
 * @method static Builder|Task whereCreatedAt($value)
 * @method static Builder|Task whereDescription($value)
 * @method static Builder|Task whereId($value)
 * @method static Builder|Task whereParentId($value)
 * @method static Builder|Task wherePriority($value)
 * @method static Builder|Task whereStatus($value)
 * @method static Builder|Task whereTitle($value)
 * @method static Builder|Task whereUpdatedAt($value)
 * @method static Builder|Task whereUserId($value)
 * @mixin \Eloquent
 *
 *
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="New Task"),
 *     @OA\Property(property="description", type="string", example="This is a new task"),
 *     @OA\Property(property="priority", type="integer", example=3),
 *     @OA\Property(property="status", type="string", example="todo"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Task extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'completed_at',
        'parent_id',
        'user_id'
    ];

    public static function createFromDTO(TaskDTO $taskDTO, int $userId): self
    {
        return static::create(array_merge($taskDTO->toArray(), ['user_id' => $userId]));
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'completed_at' => $this->completed_at,
            'user_id' => $this->user_id,
            // Add any other fields you want to include in the index
        ];
    }

    public function searchableAs()
    {
        return 'tasks_index';
    }

}

