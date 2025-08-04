<?php
namespace Main\routes;

use Main\Controller\LikesController;
use Slim\Routing\RouteCollectorProxy;

isset($protectedUsersGroup) && $protectedUsersGroup->group(
    '/likes',
    function (RouteCollectorProxy $likeGroup) {

        $likeGroup->post(
            "/toggle/like",
            LikesController::class . ":toggleLike"
        );
        $likeGroup->get(
            "/get/likes/count/{video_id}",
            LikesController::class . ":getLikesCount"
        );
        $likeGroup->get(
            "/has/UserLiked",
            LikesController::class . ":hasUserLiked"
        );

        $likeGroup->get(
            "/get/user/whoLiked/{video_id}",
            LikesController::class . ":getUserWhoLiked"
        );

    });
