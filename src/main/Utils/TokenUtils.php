<?php

namespace Main\Utils;

use Error;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;
use UnexpectedValueException;

class tokenUtils
{
    public function extractDataFromToken($request)
    {
        $token = $request->getHeader("Authorization")[0] ?? null;
        if (!$token) {
            throw new Error("Authentication failed 0");
        }

        $splittedToken = explode(" ", $token);
        
        $tokenType = $splittedToken[0] ?? null;

        $accessToken = $splittedToken[1] ?? null;

        if (!$tokenType || !$accessToken) {
            throw new Error("Authentication failed 1");
        }

         $payload = null;

        if ($tokenType === "Bearer") {
            //verify token
            $key = $_ENV["AUTH_KEY"];

            try {
                $payload = JWT::decode($accessToken, new Key($key, "HS512"));
            } catch (UnexpectedValueException $error) {
                throw new Error("Authentication failed 4");
            } catch (ExpiredException $error) {
                throw new Error("Expired token");
            } catch (Throwable $error) {
            
            }

            if (!$payload) {
                throw new Error("Authentication failed 2");
            }
        } else {
            throw new Error("Authentication failed 3");
        }

        return $payload;
    }
}