<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

// Middleware to handle CORS
$app->add(function (Request $request, $handler) {
	$response = $handler->handle($request);
	return $response
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
});

// Only ONE wildcard OPTIONS route (prevents duplicate registration)
$app->options('/{routes:.+}', function (Request $request, Response $response) {
	return $response;
});

// Example route
$app->get('/', function (Request $request, Response $response) {
	$response->getBody()->write("Backend is running!");
	return $response;
});

// Include your API routes file
require __DIR__ . '/routes.php';

// Run the Slim app
$app->run();
