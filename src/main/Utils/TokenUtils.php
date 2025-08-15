<?php
namespace Main\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class TokenUtils
{
	private $secretKey;
	private $algorithm = 'HS512';

	public function __construct()
	{
		$this->secretKey = $_ENV['JWT_SECRET'];
	}

	public function generateToken(array $payload): string
	{
		$issuedAt = time();
		$expirationTime = $issuedAt + 3600; // 1 hour expiration

		$payload = array_merge($payload, [
			'iat' => $issuedAt,
			'exp' => $expirationTime
		]);

		return JWT::encode($payload, $this->secretKey, $this->algorithm);
	}

	public function extractDataFromToken($request): object
	{
		$authHeader = $request->getHeaderLine('Authorization');

		if (empty($authHeader)) {
			throw new Exception("Authorization header missing");
		}

		if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
			throw new Exception("Token not found in header");
		}

		$token = $matches[1];

		try {
			$decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
			return $decoded;
		} catch (Exception $e) {
			throw new Exception("Invalid token: " . $e->getMessage());
		}
	}

	public function validateToken(string $token): bool
	{
		try {
			JWT::decode($token, new Key($this->secretKey, $this->algorithm));
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}