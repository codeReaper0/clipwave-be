<?php



namespace Main\routes;

use Main\Controller\commentsController;
use Main\Controller\VideosController;
use Slim\Routing\RouteCollectorProxy;

isset($protectedCommentsGroup) && $protectedCommentsGroup->group(
    '/videos', 
    function (RouteCollectorProxy $commentGroup) {

    $commentGroup->post(
        "/add/comment",
        CommentsController::class . ":addComment"
    );
    $commentGroup->get(
        "/get/comments",
        CommentsController::class . ":getComments"
    );
    $commentGroup->delete(
        "/delete/Comment",
        CommentsController::class . ":deleteComment"
    );



 
});
