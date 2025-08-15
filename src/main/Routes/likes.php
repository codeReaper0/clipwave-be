<?php
namespace Main\routes;

use Main\Controller\LikesController;
use Slim\Routing\RouteCollectorProxy;

isset($protectedUsersGroup) && $protectedUsersGroup->group(
	'/likes',
	function (RouteCollectorProxy $likeGroup) {

		$likeGroup->post(
			"/toggle",
			LikesController::class . ":toggleLike"
		);
		$likeGroup->get(
			"/get/likes/count/{video_id}",
			LikesController::class . ":getLikesCount"
		);

		$likeGroup->get('/has-liked/{user_id}/{video_id}', LikesController::class . ':hasUserLiked');

		$likeGroup->get(
			"/get/user/whoLiked/{video_id}",
			LikesController::class . ":getUserWhoLiked"
		);

	}
);
