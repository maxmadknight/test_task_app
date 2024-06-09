<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'priority' => TaskPriority::MEDIUM->value,
            'status' => TaskStatus::TODO->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'New Task',
                'description' => 'Task description',
                'priority' => TaskPriority::MEDIUM->value,
            ]);

        $this->assertDatabaseHas('tasks', $taskData);
    }

    public function test_cant_create_task_without_full_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status', 'priority']);

        $this->assertDatabaseMissing('tasks', $taskData);
    }

    public function test_can_update_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $task = Task::factory()->create(['user_id' => $user->id]);
        $generalData = Arr::except($task->toArray(), ['created_at', 'updated_at']);
        $updatedData = [
            'title' => 'Updated Task',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson($updatedData);

        $this->assertDatabaseHas('tasks', array_merge($generalData, $updatedData));
    }

    public function test_cant_update_task_of_other_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $task = Task::factory()->create(['user_id' => $user->id]);
        $generalData = Arr::except($task->toArray(), ['created_at', 'updated_at']);
        $updatedData = [
            'title' => 'Updated Task',
            'description' => 'Updated description',
        ];

        $this->actingAs(User::factory()->create(), 'sanctum');
        $response = $this->putJson("/api/tasks/{$task->id}", $updatedData);

        $response->assertStatus(403);

        $this->assertDatabaseHas('tasks', $generalData);
    }

    public function test_can_delete_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $task = Task::factory()->create(['user_id' => $user->id, 'status' => TaskStatus::TODO->value]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_cant_delete_task_of_another_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $task = Task::factory()->create(['user_id' => $user->id, 'status' => TaskStatus::TODO->value]);
        $generalData = Arr::except($task->toArray(), ['created_at', 'updated_at']);

        $this->actingAs(User::factory()->create(), 'sanctum');

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', $generalData);
    }

    public function test_can_mark_task_as_complete()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::TODO->value
        ]);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200)
            ->assertJson(['status' => TaskStatus::DONE->value]);

        $this->assertDatabaseHas(
            'tasks',
            [
                'id' => $task->id,
                'status' => TaskStatus::DONE->value
            ]
        );
    }

    public function test_cant_mark_task_as_complete_of_other_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::TODO->value
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum');

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(403);

        $this->assertDatabaseHas(
            'tasks',
            [
                'id' => $task->id,
                'status' => TaskStatus::TODO->value
            ]
        );
    }

    public function test_cant_mark_task_as_complete_with_incomplete_subtasks()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::TODO->value
        ]);

        Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::TODO->value,
            'parent_id' => $task->id
        ]);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(403);

        $this->assertDatabaseHas(
            'tasks',
            [
                'id' => $task->id,
                'status' => TaskStatus::TODO->value
            ]
        );
    }

    public function test_can_get_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'user_id' => $user->id,
            'priority' => TaskPriority::MEDIUM->value,
            'status' => TaskStatus::TODO->value
        ];

        $task = Task::factory()->create($taskData);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson($taskData);
    }

    public function test_can_get_filtered_sorted_tasks()
    {
        if (!env('scout_driver')) {
            $this->markTestSkipped('ElasticSearch is disabled, to run test, remove SCOUT_DRIVER param from phpunit.xml');
            return;
        }
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::TODO->value,
            'priority' => 1
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::DONE->value,
            'priority' => 2
        ]);

        $response = $this->getJson('/api/tasks?status=todo&sort=priority:asc,created_at:desc');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'status' => TaskStatus::TODO->value,
                'priority' => 1
            ]);
    }
}

