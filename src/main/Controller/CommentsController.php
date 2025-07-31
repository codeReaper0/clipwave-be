<?php 
namespace Main\Controller;

use Main\Model\CommentsModel;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Throwable;
class commentsController{



    public function addComment(Request $request, Response $response, array $args)
{
    try {
        $videoId = $request->getAttribute('id');
        $userId = $request->getAttribute('user_id'); 
        $body = json_decode($request->getBody()->getContents(), true);
        $commentText = $body['comment'] ?? null;

        $commentModel = new CommentsModel();
        $commentModel->video_id = $videoId;
        $commentModel->user_id = $userId;
        $commentModel->comment_text = $commentText;

        $comments = $commentModel->add();

        $response->getBody()->write(json_encode($comments));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (Throwable $err) {
        $error = [
            "message" => $err->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
}
public function getComments(Request $request, Response $response, array $args)
{
    try {
        $videoId = $args['id'];

        $commentModel = new CommentsModel();
        $comments = $commentModel->getByVideoId();

        $response->getBody()->write(json_encode($comments));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Throwable $err) {
        $error = [
            "message" => $err->getMessage()
        
        ];
        $response->getBody()->write(json_encode($error));
        return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(400);
    }
}
public function deleteComment(Request $request, Response $response, array $args)
{
    try {
        $commentId = $args['comment_id'];
        $userId = $request->getAttribute('user_id'); 

        $commentModel = new CommentsModel();

        $result = $commentModel->delete($commentId);

        $response->getBody()->write(json_encode($result));
        return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
    } catch (Throwable $err) {
        $error = [
            "message" => $err->getMessage()
    ];
        $response->getBody()->write(json_encode($error));
        return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(400);
    }
}
}