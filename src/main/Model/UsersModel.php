<?php

namespace Main\Model;

use Error;
use Firebase\JWT\JWT;
use Main\Utils\DB;
use Main\Utils\Random;
use PDO;

class UsersModel
{
	public $id;
	public $username;
	public $password;
	public $role;
	public $created_at;
	public $email;
	public $publicKey;
	public $conn;

	public $dbtable = "users";

	public function __construct()
	{
		$database = new DB();
		$this->conn = $database->conn();
	}


	#region Open

	public function signUp()
	{
		$this->password = password_hash($this->password, PASSWORD_DEFAULT);

		$sql = "SELECT id FROM users WHERE username=:username OR email=:email";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(':username', $this->username);
		$stmt->bindParam(':email', $this->email);
		$stmt->execute();

		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($user) {
			throw new Error("Username or email already exists");
		}

		// Modified query - removed :created_at parameter
		$sql = "INSERT INTO users(username, email, password, role, created_at)
            VALUES(:username, :email, :password, :role, NOW()) 
            RETURNING id, username, email, role, created_at";

		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(':username', $this->username);
		$stmt->bindParam(':password', $this->password);
		$stmt->bindParam(':email', $this->email);
		$stmt->bindParam(':role', $this->role);

		if (!$stmt->execute()) {
			throw new Error("An error occurred while creating your account");
		}

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function login()
	{
		// Step 1: Select user by username
		$sql = "SELECT id, username, email, role, password 
            FROM users 
            WHERE username = :username";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(':username', $this->username);
		$stmt->execute();

		$userVendor = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$userVendor) {
			throw new Error("Invalid credentials");
		}

		// Step 2: Verify password
		if (!password_verify($this->password, $userVendor['password'])) {
			throw new Error("Invalid password");
		}

		// Step 3: Generate public key and JWT
		$publicKey = Random::generate(12);
		$id = $userVendor['id'];

		$payload = [
			"username" => $userVendor['username'],
			"role" => $userVendor['role'],
			"publicKey" => $publicKey,
			"id" => $id
		];

		$key = $_ENV["JWT_SECRET"];
		$token = JWT::encode($payload, $key, "HS512");

		// Step 4: Update public key in DB
		$sql = "UPDATE users SET public_key = :publicKey WHERE id = :id RETURNING id";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(':publicKey', $publicKey);
		$stmt->bindParam(':id', $id);
		$stmt->execute();

		// Step 5: Return token + full user data (excluding password)
		unset($userVendor['password']); // don't return password hash

		$userVendor['token'] = $token;

		return $userVendor;
	}

	#endregion Open

	public function getProfile()
	{
		$sql = "SELECT id, username, role, email FROM users WHERE id=:id";
		$stmt = $this->conn->prepare($sql);

		$stmt->bindParam(':id', $this->id);
		$stmt->execute();

		$vendor = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$vendor) {
			throw new Error("Vendor not found");
		}

		$userVendor = [
			"id" => $vendor["id"],
			"username" => $vendor["username"],
			"role" => $vendor["role"],
			"email" => $vendor["email"],
		];

		return $userVendor;
	}

	public function updateProfile()
	{

		$sql = "SELECT id, username, role, email FROM users WHERE id=:id";
		$stmt = $this->conn->prepare($sql);

		$stmt->bindParam(':id', $this->id);

		$stmt->execute();

		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$user) {
			throw new Error("user not found");
		}

		if (!$this->username && !$this->role && !$this->email) {
			throw new Error("Required data is missing or incomplete.Kindly check your input!");
		}

		$sql = "update users SET ";

		if ($this->username) {
			$sql = $sql . "username=:username";
		}
		if ($this->role) {
			if ($this->username) {
				$sql = $sql . ", ";
			}

			$sql = $sql . "role=:role";
		}
		if ($this->email) {
			if ($this->username || $this->role) {
				$sql = $sql . ", ";
			}

			$sql = $sql . "email=:email";

		}


		$sql = $sql . " where id=:id";

		$stmt = $this->conn->prepare($sql);

		if ($this->username) {
			$stmt->bindParam(':username', $this->username);
		}
		if ($this->role) {
			$stmt->bindParam(':role', $this->role);
		}
		if ($this->email) {
			$stmt->bindParam(':email', $this->email);
		}

		$stmt->bindParam(':id', $this->id);

		$stmt->execute();
		#endregion Update user

		#region Build return data
		$user = [
			"id" => $this->id
		];

		if ($this->username) {
			$user["username"] = $this->username;
		}
		if ($this->email) {
			$user["email"] = $this->email;
		}
		if ($this->role) {
			$user["role"] = $this->role;
		}

		#endregion Build return data

		return $user;
	}
	public function updatePassword($oldPassword, $newPassword)
	{
		$sql = "SELECT password FROM users WHERE id=:id";
		$stmt = $this->conn->prepare($sql);

		$stmt->bindParam(':id', $this->id);
		$stmt->execute();

		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		$encrypted_password = $user["password"];

		if (!password_verify($oldPassword, $encrypted_password)) {
			throw new Error("invalid Password");

		}

		$encrypted_newPassword = password_hash($newPassword, null);

		$sql = "UPDATE users SET password=:password WHERE id=:id";
		$stmt = $this->conn->prepare($sql);

		$stmt->bindParam(':password', $encrypted_newPassword);
		// var_dump($this->password); die();
		$stmt->bindParam(':id', $this->id);
		$stmt->execute();

		$this->logout();

		return [
			"id" => $this->id,
		];
	}
	public function getAll()
	{
		$sql = "SELECT * FROM users";
		$stmt = $this->conn->query($sql);
		$getAllUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
		// var_dump($getAllVendor); die();

		if (!$getAllUser) {
			throw new Error("Invalid data");
		}

		return $getAllUser;
		//Note: if it is a single object, you set it but if it is an array of object you return it straight.
	}
	public function logout()
	{
		$sql = "UPDATE users SET publicKey=null WHERE id=:id ";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(':id', $this->id);
		$stmt->execute();

		return true;
	}
	public function deleteProfile()
	{
		$sql = "DELETE FROM users WHERE id=:id";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(':id', $this->id);
		$stmt->execute();

		return [];

	}

	public static function authenticate($userData)
	{

		$id = $userData->id;
		$username = $userData->username;
		$publicKey = $userData->publicKey;
		// $accessEnabled = self::VENDOR_ENABLED;

		$db = new DB;
		$conn = $db->conn();
		$sql = "SELECT id FROM users WHERE id=:id AND username=:username AND public_key=:publicKey";
		$stmt = $conn->prepare($sql);

		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':username', $username);
		$stmt->bindParam(':publicKey', $publicKey);
		// $stmt->bindParam(':accessStatus', $accessEnabled);

		$stmt->execute();
		$userDataFromDB = $stmt->fetch(PDO::FETCH_ASSOC);



		if (!$userDataFromDB) {
			throw new Error("Authorization failed. Login");
		}

		return $userDataFromDB;
	}


}