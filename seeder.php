<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\Task;

$capsule = new Capsule();
$capsule->addConnection(require __DIR__ . '/src/Config/DatabaseConfig.php');
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create tasks table
Capsule::schema()->create('tasks', function ($table) {
    $table->increments('id');
    $table->string('title');
    $table->boolean('done')->default(false);
    $table->timestamps();
});

// Seed tasks table
$tasksData = [
    ['title' => 'Task 1'],
    ['title' => 'Task 2'],
    ['title' => 'Task 3']
];

foreach ($tasksData as $taskData) {
    Task::create($taskData);
}

echo "Database seeded successfully.\n";
