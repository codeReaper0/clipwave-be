<?php 
namespace Main\Controller;

use Main\Model\CommentsModel;
use Main\Utils\tokenUtils;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Throwable;
class commentsController{



    public function addComment(Request $request, Response $response, array $args)
{
    try {
       
        $reqbody = json_decode($request->getBody()->getContents(), true);
        $commentText = $reqbody['commentText'] ?? null;
        $user_id=$reqbody['user_id']?? null;
        $video_id=$reqbody['video_id']?? null;

        $commentModel = new CommentsModel();
        $commentModel->video_id = $video_id;
        $commentModel->user_id = $user_id;
        $commentModel->commentText = $commentText;

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
        $video_id = $request->getAttribute('video_id');

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
        // $id = $args['id']; 
        // $id = $request->getAttribute('id');
        $id = $request->getAttribute('id'); 
        $commentModel = new CommentsModel();
        // $commentModel->user_id=$user_id;
        $commentModel->id=$id;


        $deleted= $commentModel->delete();

        $response->getBody()->write(json_encode($deleted));
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