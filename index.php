<?php

// set_time_limit(0);

require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;


// $app =AppFactory::create();

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();


$app = AppFactory::create();
$app->addRoutingMiddleware();


// Define other routes and handlers as needed...
require __DIR__ . '/src/main/Routes/users.php';



$app->map(["POST"], "/{route:.+}", function (ServerRequestInterface $request, ResponseInterface $response) {
    $response->getBody()->write(json_encode(["error" => "Endpoint not found"]));
    return $response->withHeader('content-type', 'application/json');
});

// Run the Slim app
$app->run();
