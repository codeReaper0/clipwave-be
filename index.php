<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

// Initialize Slim App with Azure base path
$app = AppFactory::create();

// Azure may run in a subdirectory - detect base path
$basePath = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
$app->setBasePath($basePath);

// Error middleware with Azure-specific handling
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// CORS Middleware
$app->add(function (Request $request, $handler) {
	$response = $handler->handle($request);
	return $response
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', '*')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Debug route with Azure environment info
$app->get('/debug', function (Request $request, Response $response) use ($app) {
	$data = [
		'status' => 'success',
		'base_path' => $app->getBasePath(),
		'server' => $_SERVER,
		'env' => getenv(),
		'php_version' => phpversion()
	];

	$response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
	return $response->withHeader('Content-Type', 'application/json');
});

// Include your routes
require __DIR__ . '/src/main/Routes/users.php';

// Run the app
$app->run();