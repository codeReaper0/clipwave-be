<?php

namespace Main\routes;

use Main\Controller\VideosController;
use Slim\Routing\RouteCollectorProxy;

isset($protectedUsersGroup) && $protectedUsersGroup->group(
    '/videos', 
    function (RouteCollectorProxy $videoGroup) {

    $videoGroup->post(
        "/upload/video",
        VideosController::class . ":uploadVideo"
    );
    $videoGroup->get(
        "/get/all/videos",
        VideosController::class . ":getAllVideos"
    );
    $videoGroup->get(
        "/search/video",
        VideosController::class . ":searchVideo"
    );
    $videoGroup->get(
        "/get/video/{id}",
        VideosController::class . ":getVideo"
    );
    $videoGroup->delete(
        "/delete/video/{video_id}",
        VideosController::class . ":deleteVideo"
    );



 
});


?>