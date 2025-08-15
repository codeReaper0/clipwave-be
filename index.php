<?php
require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Main\Middleware\CORSMiddleware;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Create Slim app
$app = AppFactory::create();

// Handle all OPTIONS requests before routing (preflight)
$app->options('/{routes:.+}', function (ServerRequestInterface $request, ResponseInterface $response) {
	// Just return empty response — CORSMiddleware will add the headers
	return $response;
});

// Add CORS middleware FIRST
$app->add(new CORSMiddleware());

// Add routing middleware
$app->addRoutingMiddleware();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Example route
$app->post('/cloudinary/signature', \Main\Controller\SignatureController::class . ':generateSignature');

// Register other routes
require __DIR__ . '/src/main/Routes/users.php';

// Catch-all route for undefined endpoints (no OPTIONS here)
$app->map(
	['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
	'/{routes:.+}',
	function (ServerRequestInterface $request, ResponseInterface $response) {
		$payload = json_encode(["error" => "Endpoint not found"], JSON_UNESCAPED_UNICODE);
		$response->getBody()->write($payload);
		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus(404);
	}
);

// Run app
$app->run();
