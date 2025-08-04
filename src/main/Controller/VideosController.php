<?php
namespace Main\Controller;

use Main\Model\VideosModel;
use Main\Utils\tokenUtils;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

class VideosController
{
    public function uploadVideo(Request $request, Response $response)
    {
        try {
            $formData = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            $tokenUtils = new tokenUtils();
            $userData = $tokenUtils->extractDataFromToken($request);
            $user_id = $userData->id;

            $videoModel = new VideosModel();

            $uploadResult = $videoModel->uploadVideo(
                $formData,
                $uploadedFiles,
                $user_id
            );

            $response->getBody()->write(json_encode($uploadResult));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (Throwable $e) {
            $response->getBody()->write(json_encode([
                "message" => $e->getMessage(),
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    public function getAllVideos(Request $request, Response $response)
    {
        try {
            $videoModel = new VideosModel();
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
    public function searchVideo(Request $request, Response $response)
    {
        try {
            $params = $request->getQueryParams();

            $videoModel = new VideosModel();

            $videos = $videoModel->searchVideo($params);

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
            $id = $request->getAttribute('id');

            $videoModel = new VideosModel();
            $videoModel->id = $id;

            $getVideo = $videoModel->getVideo();

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
            $videoId = $request->getAttribute('video_id');
    
            $videoModel = new VideosModel();
            $videoModel->video_id = $videoId;
    
            $deleteResult = $videoModel->deleteVideo();
    
            $response->getBody()->write(json_encode($deleteResult));
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
