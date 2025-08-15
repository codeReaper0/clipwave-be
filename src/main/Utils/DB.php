<?php

namespace Main\Utils;

use PDO;
use PDOException;

class DB
{
	private $host;
	private $port;
	private $user;
	private $pass;
	private $dbname;
	public $conn;

	public function __construct()
	{
		$this->host = $_ENV["DB_HOST"];
		$this->port = $_ENV["DB_PORT"] ?? '5432';
		$this->user = $_ENV["DB_USER"];
		$this->pass = $_ENV["DB_PASS"];
		$this->dbname = $_ENV["DB_NAME"];
	}

	public function conn(): PDO
	{
		try {
			$conn_str = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
			$conn = new PDO($conn_str, $this->user, $this->pass);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			return $conn;
		} catch (PDOException $e) {
			throw new PDOException("Connection failed: " . $e->getMessage());
		}
	}
}