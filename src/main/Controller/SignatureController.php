<?php
namespace Main\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;

class SignatureController
{
	public function generateSignature(Request $request, Response $response): Response
	{
		try {
			$data = $request->getParsedBody();

			$uploadPreset = $data['upload_preset'] ?? 'ml_default';
			$title = $data['title'] ?? '';
			$description = $data['description'] ?? '';
			$timestamp = time();

			// context format: "title=<value>|description=<value>"
			$contextValue = "title={$title}|description={$description}";

			// Params to sign
			$params = [
				'context' => $contextValue,
				'timestamp' => $timestamp,
				'upload_preset' => $uploadPreset
			];

			// Sort keys alphabetically
			ksort($params);

			// Build string without URL encoding
			$paramString = urldecode(http_build_query($params));

			// Append API secret
			$signature = sha1($paramString . $_ENV['CLOUDINARY_API_SECRET']);

			$responseData = [
				'signature' => $signature,
				'timestamp' => $timestamp,
				'api_key' => $_ENV['CLOUDINARY_API_KEY']
			];

			$response->getBody()->write(json_encode($responseData));
			return $response->withHeader('Content-Type', 'application/json');

		} catch (\Exception $e) {
			$response->getBody()->write(json_encode([
				'error' => $e->getMessage()
			]));
			return $response
				->withHeader('Content-Type', 'application/json')
				->withStatus(500);
		}
	}

}