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

// 1️⃣ Add CORS middleware FIRST so it applies to everything
$app->add(new CORSMiddleware());

// 2️⃣ Handle all OPTIONS requests BEFORE routing
$app->options('/{routes:.+}', function (ServerRequestInterface $request, ResponseInterface $response) {
	return $response;
});

// 3️⃣ Add Slim's routing
$app->addRoutingMiddleware();

// 4️⃣ Add Slim's error handler
$app->addErrorMiddleware(true, true, true);

// 5️⃣ Register your routes
$app->post('/cloudinary/signature', \Main\Controller\SignatureController::class . ':generateSignature');
require __DIR__ . '/src/main/Routes/users.php';

// 6️⃣ Fallback for all undefined routes (still returns CORS headers)
$app->map(
	['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
	'/{routes:.+}',
	function (ServerRequestInterface $request, ResponseInterface $response) {
		$payload = json_encode(["error" => "Endpoint not found"], JSON_UNESCAPED_UNICODE);
		$response->getBody()->write($payload);
		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus(404);
	}
);

// 7️⃣ Run the app
$app->run();
