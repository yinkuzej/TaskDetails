<?php
namespace App\Database;

use App\Models\Task;

class TaskDatabase {

    // Create a new task in the database
    public function createTask(array $data): Task {
        return Task::create($data);
    }

    // Update an existing task in the database
    public function updateTask(int $taskId, array $data): ?Task {
        $task = Task::find($taskId);

        if (!$task) {
            return null;
        }

        $task->update($data);
        return $task;
    }

    // Delete a task from the database
    public function deleteTask(int $taskId): bool {
        $task = Task::find($taskId);

        if (!$task) {
            return false;
        }

        return $task->delete();
    }

    // Get a task by ID from the database
    public function getTaskById(int $taskId): ?Task {
        try {
            return Task::findOrFail($taskId);
        } catch (\Exception $e) {
            return null;
        }
    }

    // Get all tasks from the database
    public function getAllTasks(): array {
        return Task::all()->toArray();
    }
}
