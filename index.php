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

// Catch-all route for undefined endpoints
$app->map(
	['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
	'/{routes:.+}',
	function (ServerRequestInterface $request, ResponseInterface $response) {
		$response->getBody()->write(json_encode(["error" => "Endpoint not found"]));
		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus(404);
	}
);

// Run app
$app->run();
