<?php
namespace Main\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class CORSMiddleware implements MiddlewareInterface
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$origin = $request->getHeaderLine('Origin') ?: '*';

		// Handle preflight OPTIONS request
		if (strtoupper($request->getMethod()) === 'OPTIONS') {
			$response = new Response();
			return $this->addCorsHeaders($response, $origin)->withStatus(204);
		}

		// Handle normal requests
		$response = $handler->handle($request);
		return $this->addCorsHeaders($response, $origin);
	}

	private function addCorsHeaders(ResponseInterface $response, string $origin): ResponseInterface
	{
		return $response
			->withHeader('Access-Control-Allow-Origin', $origin)
			->withHeader('Vary', 'Origin')
			->withHeader(
				'Access-Control-Allow-Headers',
				'X-Requested-With, Content-Type, Accept, Origin, Authorization'
			)
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
			->withHeader('Access-Control-Allow-Credentials', 'true')
			->withHeader('Access-Control-Max-Age', '86400');
	}
}
