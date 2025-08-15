<?php
namespace Main\routes;

use Main\Controller\CommentsController;
use Slim\Routing\RouteCollectorProxy;

isset($protectedUsersGroup) && $protectedUsersGroup->group(
	'/comments',
	function (RouteCollectorProxy $commentGroup) {
		$commentGroup->post(
			"/add",
			CommentsController::class . ":addComment"
		);

		$commentGroup->get(
			"/{video_id}",
			CommentsController::class . ":getComments"
		);

		$commentGroup->delete(
			"/{id}",
			CommentsController::class . ":deleteComment"
		);
	}
);