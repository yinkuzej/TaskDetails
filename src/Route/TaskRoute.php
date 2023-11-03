<?php
namespace App\Route;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Producer\TaskProducer;
use App\Database\TaskDatabase;

class TaskRoute {
    public static function setupRoutes(App $app): void {
        $taskDatabase = new TaskDatabase();
        $taskProducer = new TaskProducer();

        // Endpoint to get all tasks
        $app->get('/tasks', function (Request $request, Response $response, array $args) use ($taskDatabase) {
            try {
                $tasks = $taskDatabase->getAllTasks();
                return $response->withJson(['tasks' => $tasks]);
            } catch (\Exception $e) {
                return self::createErrorResponse($response, 500, 'Internal Server Error');
            }
        });

        // Endpoint to get a specific task by ID
        $app->get('/tasks/{id}', function (Request $request, Response $response, array $args) use ($taskDatabase) {
            $taskId = $args['id'];

            try {
                $task = $taskDatabase->getTaskById($taskId);

                if (!$task) {
                    return self::createErrorResponse($response, 404, 'Task not found');
                }

                return $response->withJson(['task' => $task]);
            } catch (\Exception $e) {
                return self::createErrorResponse($response, 500, 'Internal Server Error');
            }
        });

        // Endpoint to create a new task
        $app->post('/tasks', function (Request $request, Response $response, array $args) use ($taskDatabase, $taskProducer) {
            $data = $request->getParsedBody();

            if (empty($data['title'])) {
                return self::createErrorResponse($response, 400, 'Task title is required');
            }

            $newTask = [
                'title' => $data['title'],
                'done' => false,
            ];

            try {
                $task = $taskDatabase->createTask($newTask);
                $taskProducer->publishTaskEvent('task_created', $task->toArray());
                return $response->withJson(['task' => $task]);
            } catch (\Exception $e) {
                return self::createErrorResponse($response, 500, 'Internal Server Error');
            }
        });

        // Endpoint to update an existing task by ID
        $app->put('/tasks/{id}', function (Request $request, Response $response, array $args) use ($taskDatabase, $taskProducer) {
            $taskId = $args['id'];
            $data = $request->getParsedBody();

            if (empty($data['title'])) {
                return self::createErrorResponse($response, 400, 'Task title is required');
            }

            $existingTask = $taskDatabase->getTaskById($taskId);

            if (!$existingTask) {
                return self::createErrorResponse($response, 404, 'Task not found');
            }

            $updatedTaskData = [
                'title' => $data['title'],
                'done' => !empty($data['done']),
            ];

            try {
                $updatedTask = $taskDatabase->updateTask($taskId, $updatedTaskData);
                $taskProducer->publishTaskEvent('task_updated', $updatedTask->toArray());
                return $response->withJson(['task' => $updatedTask]);
            } catch (\Exception $e) {
                return self::createErrorResponse($response, 500, 'Internal Server Error');
            }
        });

        // Endpoint to delete a task by ID
        $app->delete('/tasks/{id}', function (Request $request, Response $response, array $args) use ($taskDatabase, $taskProducer) {
            $taskId = $args['id'];
            $existingTask = $taskDatabase->getTaskById($taskId);

            if (!$existingTask) {
                return self::createErrorResponse($response, 404, 'Task not found');
            }

            try {
                $isDeleted = $taskDatabase->deleteTask($taskId);

                if ($isDeleted) {
                    $taskProducer->publishTaskEvent('task_deleted', ['id' => $taskId]);
                    return $response->withJson(['message' => 'Task deleted successfully']);
                } else {
                    return self::createErrorResponse($response, 500, 'Failed to delete task');
                }
            } catch (\Exception $e) {
                return self::createErrorResponse($response, 500, 'Internal Server Error');
            }
        });
    }

    // Helper function to create error responses
    private static function createErrorResponse(Response $response, int $statusCode, string $message): Response {
        $errorResponse = [
            'error' => [
                'code' => $statusCode,
                'message' => $message
            ]
        ];
        return $response->withJson($errorResponse, $statusCode);
    }
}
