<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\App;
use App\Route\TaskRoute;

$app = new App();

// Setup task routes
TaskRoute::setupRoutes($app);

$app->run();
