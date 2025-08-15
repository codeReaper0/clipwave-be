<?php

namespace Main\Controller;

use Main\Model\UsersModel;
use Main\Utils\tokenUtils;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

class UsersController
{

	public function signUp(Request $request, Response $response)
	{
		try {

			$userReqData = (array) json_decode($request->getBody()->getContents());

			$username = $userReqData['username'];
			$email = $userReqData['email'];
			$password = $userReqData['password'];
			$role = $userReqData['role'];

			$usersModel = new UsersModel();

			$usersModel->username = $username;
			$usersModel->email = $email;
			$usersModel->password = $password;
			$usersModel->role = $role;

			$userVendor = $usersModel->signup();

			//process the response
			$response->getBody()->write(json_encode($userVendor));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (PDOException $err) {

			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		} catch (Throwable $err) {
			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}

	public function login(Request $request, Response $response)
	{
		try {
			$userReqData = (array) json_decode($request->getBody()->getContents());

			$username = $userReqData['username'];
			$password = $userReqData['password'];

			$userModel = new UsersModel();

			$userModel->username = $username;
			$userModel->password = $password;

			$userResponseData = $userModel->login();

			$response->getBody()->write(json_encode($userResponseData));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (PDOException $err) {
			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		} catch (Throwable $err) {
			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(500);
		}
	}

	public function getProfile(Request $request, Response $response)
	{
		try {
			$tokenUtils = new tokenUtils();
			$userData = $tokenUtils->extractDataFromToken($request);
			$id = $userData->id;

			$user = new UsersModel();
			$user->id = $id;
			$userData = $user->getProfile();

			$response->getBody()->write(json_encode($userData));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (PDOException $err) {
			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		} catch (Throwable $err) {
			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}
	public function updateProfile(Request $request, Response $response)
	{
		try {
			$tokenUtils = new tokenUtils();
			$userData = $tokenUtils->extractDataFromToken($request);
			$id = $userData->id;

			$userReqData = (array) json_decode($request->getBody()->getContents());

			$username = $userReqData['username'] ?? null;
			$email = $userReqData['email'] ?? null;
			$role = $userReqData['role'] ?? null;

			$user = new UsersModel();

			$user->id = $id;
			$user->username = $username;
			$user->email = $email;
			$user->role = $role;

			$updateUser = $user->UpdateProfile();

			$response->getBody()->write(json_encode($updateUser));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (PDOException $err) {
			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}
	public function updatePassword(Request $request, Response $response)
	{
		try {

			$userReqData = (array) json_decode($request->getBody()->getContents());
			$oldPassword = $userReqData['oldPassword'];
			$newPassword = $userReqData['newPassword'];

			$tokenUtils = new TokenUtils();
			$userData = $tokenUtils->extractDataFromToken($request);
			$id = $userData->id;

			$user = new UsersModel();
			$user->id = $id;
			$userVendor = $user->updatePassword($oldPassword, $newPassword);

			$response->getBody()->write(json_encode($userVendor));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (PDOException $err) {
			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		} catch (Throwable $err) {
			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}
	public function getAll(Request $request, Response $response)
	{

		try {
			$tokenUtils = new TokenUtils();
			$adminUserData = $tokenUtils->extractDataFromToken($request);

			$getAllUsers = new UsersModel();

			$getAllUsersData = $getAllUsers->getAll();

			$response->getBody()->write(json_encode($getAllUsersData));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (PDOException $err) {
			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}
	public function logout(Request $request, Response $response)
	{
		try {

			$tokenUtils = new TokenUtils();
			$userData = $tokenUtils->extractDataFromToken($request);
			$id = $userData->id;

			$user = new UsersModel();
			$user->id = $id;

			$user->logout();

			$response->getBody()->write(json_encode(["message" => "logout successful"]));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (PDOException $err) {
			$err = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($err));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}
	public function deleteProfile(Request $request, Response $response)
	{
		try {
			$tokenUtils = new tokenUtils();
			$userData = $tokenUtils->extractDataFromToken($request);
			$id = $userData->id;

			$user = new UsersModel();
			$user->id = $id;

			$userData = $user->deleteProfile();

			$response->getBody()->write(json_encode($userData));

			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(200);
		} catch (Throwable $err) {
			$error = [
				"message" => $err->getMessage(),
			];
			$response->getBody()->write(json_encode($error));
			return $response
				->withHeader('content-type', 'application/json')
				->withStatus(400);
		}
	}

}
