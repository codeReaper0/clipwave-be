<?php
namespace Main\Controller;

use Main\Model\LikesModel;
use Main\Utils\tokenUtils;
use PDOException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Throwable;

class LikesController
{

	public function toggleLike(Request $request, Response $response)
	{
		try {
			// $userId = $request->getAttribute('user_id');
			$reqdata = (array) json_decode($request->getBody()->getContents(), true);
			$video_id = $reqdata['video_id'] ?? null;
			$user_id = $reqdata['user_id'] ?? null;

			$likeModel = new LikesModel();
			$likeModel->user_id = $user_id;
			$likeModel->video_id = $video_id;

			$result = $likeModel->toggleLike();

			$response->getBody()->write(json_encode($result));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (PDOException | Throwable $err) {
			$error = [
				'message' => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}

	public function getLikesCount(Request $request, Response $response, array $args): Response
	{
		try {
			$video_id = $request->getAttribute('video_id');

			$likeModel = new LikesModel();
			$likeModel->video_id = $video_id;

			$likesCount = $likeModel->countLikes();

			$response->getBody()->write(json_encode(
				[
					"message" => 'likes count retrieved successfully',
					'data' => [
						'video_id' => $video_id,
						'likes_count' => $likesCount
					],
				]
			));
			return $response->withHeader('content-type', 'application/json')->withStatus(200);
		} catch (PDOException | Throwable $err) {
			$error = ['message' => $err->getMessage()];
			$response->getBody()
				->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}
	public function hasUserLiked(Request $request, Response $response, array $args): Response
	{
		try {

			// Get user ID from token (which uses 'id')
			$userId = $args['user_id'];
			$videoId = $args['video_id'] ?? null;

			if (!$videoId) {
				throw new \Exception("Video ID is required");
			}

			$likeModel = new LikesModel();
			// Map token 'id' to model 'user_id'
			$likeModel->user_id = $userId;
			$likeModel->video_id = $videoId;

			$liked = $likeModel->hasUserLiked();

			$response->getBody()->write(json_encode([
				'success' => true,
				'video_id' => $videoId,
				'liked' => $liked
			]));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (PDOException | Throwable $err) {
			$error = [
				'success' => false,
				'message' => $err->getMessage(),
			];
			$response->getBody()
				->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}
	public function getUserWhoLiked(Request $request, Response $response): Response
	{
		try {
			$video_id = $request->getAttribute('video_id');

			$tokenUtils = new tokenUtils();
			$userData = $tokenUtils->extractDataFromToken($request);
			$user_id = $userData->id;

			$likeModel = new LikesModel();
			$likeModel->user_id = $user_id;
			$likeModel->video_id = $video_id;

			$getUsers = $likeModel->getUsersWhoLiked();

			$response->getBody()->write(json_encode(
				[$getUsers]
			));
			return $response->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (PDOException | Throwable $err) {
			$error = [
				'message' => $err->getMessage(),
			];
			$response->getBody()
				->write(json_encode($error));
			return $response->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}
}
