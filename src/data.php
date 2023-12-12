<?php
// Your dynamic route generation logic
$routePath = '/custom-route';
$controllerMethod = 'handleCustomRoute';
$this->info('Custom route added successfully.');

$routeDefinition = "Route::get('$routePath', [ApiController::class, '$controllerMethod']);";

// Append the generated route definition to api.php
$apiFilePath = base_path('routes/api.php');
file_put_contents($apiFilePath, $routeDefinition . PHP_EOL, FILE_APPEND);


// print_r("ppppppppppp");