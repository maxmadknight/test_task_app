<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        foreach ($users as $user) {
            Task::factory()->count(5)->create([
                'user_id' => $user->id,
            ]);

            // Optionally create subtasks
            foreach ($user->tasks as $task) {
                Task::factory()->count(2)->create([
                    'parent_id' => $task->id,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
