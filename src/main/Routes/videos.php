<?php

namespace Main\routes;

use Main\Controller\VideosController;
use Slim\Routing\RouteCollectorProxy;

isset($protectedUsersGroup) && $protectedUsersGroup->group(
	'/videos',
	function (RouteCollectorProxy $videoGroup) {

		$videoGroup->post(
			"/upload",
			VideosController::class . ":uploadVideo"
		);
		$videoGroup->get(
			"/all",
			VideosController::class . ":getAllVideos"
		);
		$videoGroup->get(
			"/search",
			VideosController::class . ":searchVideo"
		);
		$videoGroup->get(
			"/{id}",
			VideosController::class . ":getVideo"
		);
		$videoGroup->delete(
			"/{video_id}",
			VideosController::class . ":deleteVideo"
		);
	}
);


?>