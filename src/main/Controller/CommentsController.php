<?php
namespace Main\Controller;

use Main\Model\CommentsModel;
use Main\Utils\tokenUtils;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Throwable;

class CommentsController
{
	private function jsonResponse(Response $response, array $data, int $status = 200): Response
	{
		$response->getBody()->write(json_encode($data));
		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus($status);
	}

	public function addComment(Request $request, Response $response)
	{
		try {
			$reqbody = json_decode($request->getBody()->getContents(), true);
			$content = $reqbody['content'] ?? null;
			$user_id = $reqbody['user_id'] ?? null;
			$video_id = $reqbody['video_id'] ?? null;

			if (!$content || !$user_id || !$video_id) {
				throw new \Exception("All fields are required");
			}

			$commentModel = new CommentsModel();
			$commentModel->video_id = $video_id;
			$commentModel->user_id = $user_id;
			$commentModel->user_id = $user_id;
			$commentModel->commentText = $content;

			$comment = $commentModel->add();

			return $this->jsonResponse($response, [
				'success' => true,
				'comment' => $comment,
				'comment_count' => $commentModel->getCommentCount($video_id)
			]);

		} catch (Throwable $err) {
			return $this->jsonResponse($response, [
				"success" => false,
				"message" => $err->getMessage()
			], 400);
		}
	}

	public function getComments(Request $request, Response $response, array $args)
	{
		try {
			$video_id = $args['video_id'] ?? null;

			if (!$video_id) {
				throw new \Exception("Video ID is required");
			}

			$commentModel = new CommentsModel();
			$comments = $commentModel->getByVideoId($video_id);

			return $this->jsonResponse($response, [
				'success' => true,
				'comments' => $comments
			]);

		} catch (Throwable $err) {
			return $this->jsonResponse($response, [
				"success" => false,
				"message" => $err->getMessage()
			], 400);
		}
	}

	public function deleteComment(Request $request, Response $response, array $args)
	{
		try {
			$id = $args['id'] ?? null;
			$user_id = $request->getAttribute('user_id');

			if (!$id) {
				throw new \Exception("Comment ID is required");
			}

			$commentModel = new CommentsModel();
			$commentModel->id = $id;

			// Verify comment belongs to user
			if (!$commentModel->belongsToUser($user_id)) {
				throw new \Exception("Unauthorized to delete this comment");
			}

			$video_id = $commentModel->getVideoId();
			$deleted = $commentModel->delete();

			return $this->jsonResponse($response, [
				'success' => true,
				'deleted' => $deleted,
				'comment_count' => $commentModel->getCommentCount($video_id)
			]);

		} catch (Throwable $err) {
			return $this->jsonResponse($response, [
				"success" => false,
				"message" => $err->getMessage()
			], 400);
		}
	}
}