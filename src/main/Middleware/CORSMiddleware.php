<?php
namespace Main\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CORSMiddleware implements MiddlewareInterface
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		// Handle preflight OPTIONS request
		if ($request->getMethod() === 'OPTIONS') {
			$response = new \Slim\Psr7\Response();

			// Add CORS headers to OPTIONS response
			$response = $this->addCorsHeaders($response);
			return $response;
		}

		// Handle regular request
		$response = $handler->handle($request);

		// Add CORS headers to regular response
		return $this->addCorsHeaders($response);
	}

	private function addCorsHeaders(ResponseInterface $response): ResponseInterface
	{
		$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

		return $response
			->withHeader('Access-Control-Allow-Origin', $origin)
			->withHeader('Vary', 'Origin')
			->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
			->withHeader('Access-Control-Allow-Credentials', 'true')
			->withHeader('Access-Control-Max-Age', '86400');
	}
}