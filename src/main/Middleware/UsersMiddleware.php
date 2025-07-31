<?php

namespace Main\Middleware;

use Error;
use Main\Model\UsersModel;
use Main\Utils\TokenUtils;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class UsersMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $tokenUtils = new TokenUtils();
            $userData = $tokenUtils->extractDataFromToken($request);

            $isAuthenticated = UsersModel::authenticate($userData);

            if (!$isAuthenticated) {
                throw new Error("Authentication failed 3");
            }

            $response = $handler->handle($request);
            return $response;
        } catch (\Throwable  | PDOException $th) {
            $error = [
                "message" => $th->getMessage(),
            ];

            $resp = new Response();
            $resp->getBody()->write(json_encode($error));

            return $resp
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }
    }

}
