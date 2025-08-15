<?php

namespace Main\Routes;

use Main\Controller\UsersController;
use Main\Middleware\UsersMiddleware;
use Slim\Routing\RouteCollectorProxy;

isset($app) && $app->group('/users', function (RouteCollectorProxy $usersGroup) {
	$usersGroup->group("", function (RouteCollectorProxy $OpenUsersGroup) {

		$OpenUsersGroup->post(
			'/signup',
			UsersController::class . ':signup'
		);

		$OpenUsersGroup->post(
			'/login',
			UsersController::class . ':login'
		);
	});

	$usersGroup->group("", function (RouteCollectorProxy $protectedUsersGroup) {

		require 'src/main/Routes/videos.php';

		require 'src/main/Routes/comments.php';

		require 'src/main/Routes/likes.php';



		$protectedUsersGroup->get(
			"/profile",
			UsersController::class . ":getProfile"
		);
		$protectedUsersGroup->get(
			"/get/all",
			UsersController::class . ":getAll"
		);
		$protectedUsersGroup->patch(
			"/update/profile",
			UsersController::class . ":updateProfile"
		);
		$protectedUsersGroup->patch(
			"/update/password",
			UsersController::class . ":updatePassword"
		);
		$protectedUsersGroup->delete(
			"/delete/profile",
			UsersController::class . ":deleteProfile"
		);
		$protectedUsersGroup->post(
			"/logout",
			UsersController::class . ":logout"
		);
	})
		->addMiddleware(new UsersMiddleware())
	;
});
