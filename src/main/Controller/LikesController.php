<?php
namespace Main\Controller;

use Main\Model\LikesModel;
use PDOException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Throwable;

class LikesController
{

    public function toggleLike(Request $request, Response $response)
    {
        try {
            $userId = $request->getAttribute('user_id');
            $reqdata = (array) json_decode($request->getBody()->getContents(), true);
            $videoId = $reqdata['video_id'] ?? null;

            $likeModel = new LikesModel();
            $likeModel->user_id = $userId;
            $likeModel->video_id = $videoId;

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
            $likeModel = new LikesModel();
            $likeModel->video_id = $args['video_id'];

            $count = $likeModel->countLikes();

            $response->getBody()->write(json_encode(['video_id' => $likeModel->video_id, 'likes' => $count]
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
            $userId = $request->getAttribute('user_id');

            $likeModel = new LikesModel();
            $likeModel->user_id = $userId;
            $likeModel->video_id = $args['video_id'];

            $liked = $likeModel->hasUserLiked();

            $response->getBody()->write(json_encode(['video_id' => $likeModel->video_id, 'liked' => $liked]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException | Throwable $err) {
            $error = [
                'message' => $err->getMessage(),
            ];
            $response->getBody()
                ->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }
    }

    public function getUserLikedVideos(Request $request, Response $response): Response
    {
        try {
            $userId = $request->getAttribute('user_id');
            $likeModel = new Like();
            $likeModel->user_id = $userId;

            $videos = $likeModel->getUserLikedVideos();

            $response->getBody()->write(json_encode(['liked_videos' => $videos]
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
