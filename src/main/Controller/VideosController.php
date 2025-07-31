<?php
namespace Main\Controller;
use Main\Utils\tokenUtils;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;
use VideosModel;

class VideosController
{
    public function uploadVideo(Request $request, Response $response)
    {
        try {

            $userReqdata = (array) json_decode($request->getBody()->getContents());

            $title = $userReqdata['title'] ?? null;
            $genre = $userReqdata['genre'] ?? null;
            $age_rating = $userRdata['age_rating'] ?? null;
            $publisher = $userReqdata['publisher'] ?? null;
            $producer = $userReqdata['producer'] ?? null;
            $thumbnail_url = $userReqdata['thumbnail_url'] ?? null;
            $video_url = $userReqdata['video_url'] ?? null;

            // Token extraction for user ID
            $tokenUtils = new tokenUtils();
            $userData = $tokenUtils->extractDataFromToken($request);
            $user_id = $userData->id;

            $videoModel = new VideoModel();
            $videoModel->title = $title;
            $videoModel->genre = $genre;
            $videoModel->age_rating = $age_rating;
            $videoModel->publisher = $publisher;
            $videoModel->producer = $producer;
            $videoModel->thumbnail_url = $thumbnail_url;
            $videoModel->video_url = $video_url;
            $videoModel->user_id = $user_id;

            $uploadResponse = $videoModel->upload();

            $response->getBody()->write(json_encode($uploadResponse));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException $err) {

            $error = [
                "message" => $err->getMessage(),
            ];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        } catch (Throwable $err) {
            $error = [
                "message" => $err->getMessage(),
            ];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

    }
    public function getAllVideos(Request $request, Response $response)
    {
        try {
            $videoModel = new VideoModel();
            $videos = $videoModel->getAllVideos();

            $response->getBody()->write(json_encode($videos));
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
    public function searchVideos(Request $request, Response $response)
    {
        try {
            $params = $request->getQueryParams(); 

            $videoModel = new VideoModel();
    
            $videos = $videoModel->searchVideos($params);
    
            $response->getBody()->write(json_encode($videos));
            return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $err) {

        $error = [
            "message" => $err->getMessage(),
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    } catch (Throwable $err) {
        $error = [
            "message" => $err->getMessage(),
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    
        }
    
    
    }

    public function getVideo(Request $request, Response $response)
    {
        try {
            $userId = $request->getAttribute('user_id'); 

            $videoModel = new VideoModel();
            $videoModel->user_id = $userId;
    
            $getVideo = $videoModel->getVideosByUser();
    

            $response->getBody()->write(json_encode($getVideo));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException $err) {
            $error = [
                "message" => $err->getMessage(),
            ];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        } catch (Throwable $err) {
            $error = [
                "message" => $err->getMessage(),
            ];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }
    }

    public function deleteVideo(Request $request, Response $response)
    {
        try {
            $tokenUtils = new tokenUtils();
            $userData = $tokenUtils->extractDataFromToken($request);
            $id = $userData->id;

            $user = new VideosModel();
            $user->id = $id;

            $userData = $user->deleteVideo();

            $response->getBody()->write(json_encode($userData));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (Throwable $err) {
            $error = [
                "message" => $err->getMessage(),
            ];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }
    }
    public function likeVideo(Request $request, Response $response, array $args)
{
    try {
        $userId = $request->getAttribute('user_id');
        $videoId = $args['id'];

        $videoModel = new VideosModel();
        $result = $videoModel->toggleLike($userId, $videoId);

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Throwable $e) {
        $response->getBody()->write(json_encode([
            "message" => $e->getMessage()
        ]));
        return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(400);
    }
}
}
