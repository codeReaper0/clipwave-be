<?php
namespace Main\Controller;

use Main\Model\VideosModel;
use Main\Utils\tokenUtils;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class VideosController
{
	private $cloudinary;

	public function __construct()
	{
		// Initialize Cloudinary
		$this->cloudinary = new Cloudinary([
			'cloud' => [
				'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
				'api_key' => $_ENV['CLOUDINARY_API_KEY'],
				'api_secret' => $_ENV['CLOUDINARY_API_SECRET']
			],
			'url' => [
				'secure' => true
			]
		]);
	}

	public function uploadVideo(Request $request, Response $response)
	{
		try {
			$tokenUtils = new TokenUtils();
			$userData = $tokenUtils->extractDataFromToken($request);
			$user_id = $userData->id;

			// Get and validate JSON input
			$json = $request->getBody()->getContents();
			$formData = json_decode($json, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new \Exception("Invalid JSON input");
			}

			// Validate required fields
			if (empty($formData['cloudinaryId'])) {
				throw new \Exception("Cloudinary ID is required");
			}

			// Set default title if empty
			$title = !empty($formData['title']) ? $formData['title'] : 'Untitled Video';

			// Save to database
			$videoModel = new VideosModel();
			$dbResult = $videoModel->uploadVideo([
				'user_id' => $user_id,
				'cloudinary_id' => $formData['cloudinaryId'],
				'title' => $title,
				'description' => $formData['description'] ?? '',
				'genre' => $formData['genre'] ?? null,
				'age_rating' => $formData['age_rating'] ?? null,
				'publisher' => $formData['publisher'] ?? null,
				'producer' => $formData['producer'] ?? null,
				'duration' => $formData['duration'] ?? 0,
				'format' => $formData['format'] ?? 'mp4',
				'video_url' => $formData['url'],
				'hls_url' => $this->generateHlsUrl($formData['cloudinaryId']),
				'thumbnail_url' => $this->generateThumbnailUrl($formData['cloudinaryId'])
			]);

			$response->getBody()->write(json_encode([
				'success' => true,
				'video' => $dbResult
			]));
			return $response
				->withHeader('Content-Type', 'application/json')
				->withStatus(201);

		} catch (Throwable $e) {
			// Error response
			$response->getBody()->write(json_encode([
				'success' => false,
				'message' => $e->getMessage()
			]));
			return $response
				->withHeader('Content-Type', 'application/json')
				->withStatus(400);
		}
	}
	private function generateHlsUrl(string $publicId): string
	{
		return "https://res.cloudinary.com/" . $_ENV['CLOUDINARY_CLOUD_NAME'] .
			"/video/upload/sp_hd/{$publicId}.m3u8";
	}

	private function generateThumbnailUrl(string $publicId): string
	{
		return "https://res.cloudinary.com/" . $_ENV['CLOUDINARY_CLOUD_NAME'] .
			"/video/upload/w_500,h_500,c_fill/{$publicId}.jpg";
	}
	public function getAllVideos(Request $request, Response $response)
	{
		try {
			$queryParams = $request->getQueryParams();
			$limit = $queryParams['limit'] ?? 20;
			$offset = $queryParams['offset'] ?? 0;

			$videoModel = new VideosModel();
			$videos = $videoModel->getAllVideos($limit, $offset);

			$response->getBody()->write(json_encode([
				'success' => true,
				'data' => $videos,
				'count' => count($videos)
			]));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);

		} catch (PDOException $err) {
			$error = ["message" => $err->getMessage()];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(500);

		} catch (Throwable $err) {
			$error = ["message" => $err->getMessage()];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(500);
		}
	}

	public function searchVideo(Request $request, Response $response)
	{
		try {
			$params = $request->getQueryParams();
			$limit = $params['limit'] ?? 10;
			$offset = $params['offset'] ?? 0;

			$videoModel = new VideosModel();
			$videos = $videoModel->searchVideo($params, $limit, $offset);

			$response->getBody()->write(json_encode([
				'success' => true,
				'data' => $videos,
				'count' => count($videos)
			]));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);

		} catch (PDOException $err) {
			$error = ["message" => $err->getMessage()];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		} catch (Throwable $err) {
			$error = ["message" => $err->getMessage()];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}

	public function getVideo(Request $request, Response $response)
	{
		try {
			$id = $request->getAttribute('id');

			$videoModel = new VideosModel();
			$videoModel->id = $id;

			$video = $videoModel->getVideo();

			$response->getBody()->write(json_encode([
				'success' => true,
				'data' => $video
			]));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);

		} catch (PDOException $err) {
			$error = ["message" => $err->getMessage()];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		} catch (Throwable $err) {
			$error = ["message" => $err->getMessage()];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}

	public function deleteVideo(Request $request, Response $response)
	{
		try {
			$videoId = $request->getAttribute('video_id');

			$videoModel = new VideosModel();
			$videoModel->video_id = $videoId;

			// First get video info from database
			$video = $videoModel->getVideo();

			if (empty($video)) {
				throw new \Exception("Video not found");
			}

			// Delete from Cloudinary
			$this->cloudinary->uploadApi()->destroy($video['cloudinary_id'], [
				'resource_type' => 'video',
				'invalidate' => true
			]);

			// Delete from database
			$deleteResult = $videoModel->deleteVideo();

			$response->getBody()->write(json_encode([
				'success' => true,
				'message' => 'Video deleted successfully',
				'data' => $deleteResult
			]));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (Throwable $err) {
			$error = ["message" => $err->getMessage()];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}
}