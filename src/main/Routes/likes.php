<?php


namespace Main\routes;
use Main\Controller\LikesController;
use Slim\Routing\RouteCollectorProxy;

isset($protectedLikesGroup) && $protectedLikesGroup->group(
    '/videos', 
    function (RouteCollectorProxy $likeGroup) {

    $likeGroup->post(
        "/toggle/likes",
        LikesController::class . ":toggleLikes"
    );
    $likeGroup->get(
        "/get/likes/count",
        LikesController::class . ":getLikesCount"
    );
    $likeGroup->delete(
        "/has/UserLiked",
        LikesController::class . ":hasUserLiked"
    );

    $likeGroup->delete(
        "/get/UserLiked/video",
        LikesController::class . ":getUserLikedVideo"
    );

 
});
