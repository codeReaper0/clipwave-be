<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Initialize Slim App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Single CORS middleware that handles both preflight and regular requests
$app->add(function (Request $request, $handler) {
	// Handle preflight OPTIONS requests
	if ($request->getMethod() === 'OPTIONS') {
		$response = new \Slim\Psr7\Response();
		return $response
			->withHeader('Access-Control-Allow-Origin', '*')
			->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
	}

	// Handle regular requests
	$response = $handler->handle($request);
	return $response
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Remove this duplicate OPTIONS route - it's now handled by the middleware above
// $app->options('/{routes:.+}', function (Request $request, Response $response) {
//     return $response;
// });

// Basic route
$app->get('/', function (Request $request, Response $response) {
	$response->getBody()->write("Backend is running!");
	return $response;
});

// Debug route
$app->get('/debug', function (Request $request, Response $response) use ($app) {
	$routes = [];
	foreach ($app->getRouteCollector()->getRoutes() as $route) {
		$routes[] = $route->getPattern();
	}

	$data = [
		'status' => 'success',
		'routes' => array_unique($routes),
		'message' => 'Make sure to use POST for /users/login'
	];

	$response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
	return $response->withHeader('Content-Type', 'application/json');
});

// Load route files
$routeFiles = [
	__DIR__ . '/src/main/Routes/users.php'
];

foreach ($routeFiles as $routeFile) {
	if (!file_exists($routeFile)) {
		error_log("Route file not found: $routeFile");
		continue;
	}
	require $routeFile;
}

// Catch-all route for 404 (excluding OPTIONS which is handled by middleware)
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response) {
	throw new HttpNotFoundException($request);
});

$app->run();