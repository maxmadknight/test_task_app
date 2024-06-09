<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => TaskStatus::TODO->value,
            'priority' => (int) $this->faker->randomElement(data_get(TaskPriority::cases(), '*.value')),
            'created_at' => now(),
            'updated_at' => now(),
            'parent_id' => null,
            'user_id' => 1,
        ];
    }
}
