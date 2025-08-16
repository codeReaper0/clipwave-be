<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Initialize Slim App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// CORS middleware
$app->add(function (Request $request, $handler) {
	// Handle OPTIONS requests
	if ($request->getMethod() === 'OPTIONS') {
		$response = new \Slim\Psr7\Response();
		return $response
			->withHeader('Access-Control-Allow-Origin', '*')
			->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
	}

	// Process regular requests
	$response = $handler->handle($request);
	return $response
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Basic route - plain text response
$app->get('/', function (Request $request, Response $response) {
	$response->getBody()->write("Backend is running! | " . date('Y-m-d H:i:s'));
	return $response;
});

// Test route
$app->get('/test', function (Request $request, Response $response) {
	$response->getBody()->write("Test successful");
	return $response;
});

// Debug route - plain text output
$app->get('/debug', function (Request $request, Response $response) use ($app) {
	$output = "Registered Routes:\n";
	$output .= "================\n";

	foreach ($app->getRouteCollector()->getRoutes() as $index => $route) {
		$output .= sprintf(
			"%d. %s %s\n",
			$index + 1,
			implode('|', $route->getMethods()),
			$route->getPattern()
		);
	}

	$response->getBody()->write($output);
	return $response;
});

// Load route files
$routeFiles = [
	__DIR__ . '/src/main/Routes/users.php'
];

foreach ($routeFiles as $routeFile) {
	if (file_exists($routeFile)) {
		require $routeFile;
		error_log("Loaded route file: " . basename($routeFile));
	} else {
		error_log("Route file not found: " . basename($routeFile));
	}
}

// Health check endpoint
$app->get('/health', function (Request $request, Response $response) {
	$response->getBody()->write("OK");
	return $response;
});

// Catch-all route for 404
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response) {
	throw new HttpNotFoundException($request);
});

// Run application
$app->run();