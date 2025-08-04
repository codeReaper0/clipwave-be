<?php

namespace Main\routes;

use Main\Controller\commentsController;
use Slim\Routing\RouteCollectorProxy;

isset($protectedUsersGroup) && $protectedUsersGroup->group(
    '/comments',
    function (RouteCollectorProxy $commentGroup) {

        $commentGroup->post(
            "/add/comment",
            CommentsController::class . ":addComment"
        );
        $commentGroup->get(
            "/get/comments/{video_id}",
            CommentsController::class . ":getComments"
        );
        $commentGroup->delete(
            "/delete/Comment/{id}",
            CommentsController::class . ":deleteComment"
        );

    });
