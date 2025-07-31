<?php



namespace Main\routes;

use Main\Controller\VideosController;
use Slim\Routing\RouteCollectorProxy;

isset($protectedUsersGroup) && $protectedUsersGroup->group(
    '/videos', 
    function (RouteCollectorProxy $videoGroup) {

    $videoGroup->post(
        "/upload",
        VideosController::class . ":upload"
    );
    $videoGroup->get(
        "/get/all/videos",
        VideosController::class . ":getAllVideos"
    );
    $videoGroup->get(
        "/search/videos",
        VideosController::class . ":searchVideos"
    );
    $videoGroup->get(
        "/get/video",
        VideosController::class . ":getVideo"
    );
    $videoGroup->post(
        "/like/video",
        VideosController::class . ":likeVideo"
    );



 
});


?>