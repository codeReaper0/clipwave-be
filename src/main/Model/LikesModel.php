<?php
namespace Main\Model;

use Main\Utils\DB;
use PDO;

class LikesModel
{
	public $id;
	public $username;
	public $commentText;
	public $video_id;
	public $email;
	public $user_id;
	public $created_at;

	public $conn;

	public $dbtable = "likes";

	public function __construct()
	{
		$database = new DB();
		$this->conn = $database->conn();
	}

	public function toggleLike()
	{
		// First check if the user has already liked the video
		$checkSql = "SELECT 1 FROM likes WHERE user_id = :user_id AND video_id = :video_id";
		$checkStmt = $this->conn->prepare($checkSql);
		$checkStmt->execute([':user_id' => $this->user_id, ':video_id' => $this->video_id]);

		if ($checkStmt->rowCount() > 0) {
			// Unlike the video
			$deleteSql = "DELETE FROM likes WHERE user_id = :user_id AND video_id = :video_id";
			$deleteStmt = $this->conn->prepare($deleteSql);
			$deleteStmt->execute([':user_id' => $this->user_id, ':video_id' => $this->video_id]);

			return [
				'message' => 'Unliked successfully',
				'is_liked' => false,
				'likes_count' => $this->countLikes()
			];
		} else {
			// Like the video
			$insertSql = "INSERT INTO likes (user_id, video_id, liked_at) VALUES (:user_id, :video_id, NOW())";
			$insertStmt = $this->conn->prepare($insertSql);
			$insertStmt->execute([':user_id' => $this->user_id, ':video_id' => $this->video_id]);

			return [
				'message' => 'Liked successfully',
				'is_liked' => true,
				'likes_count' => $this->countLikes()
			];
		}
	}
	public function countLikes()
	{
		$sql = "SELECT COUNT(*) as total FROM likes WHERE video_id = :video_id";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('video_id', $this->video_id);

		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row['total'] ?? 0;
	}

	public function hasUserLiked()
	{
		$sql = "SELECT 1 FROM likes WHERE user_id = :user_id AND video_id = :video_id LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute([
			':user_id' => $this->user_id,
			':video_id' => $this->video_id
		]);
		return $stmt->rowCount() > 0;
	}
	public function getUsersWhoLiked()
	{
		$sql = "SELECT users.id, users.username
            FROM likes
            JOIN users ON likes.user_id = users.id
            WHERE likes.video_id = :video_id";

		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(':video_id', $this->video_id);
		$stmt->execute();
		$getUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $getUser;
	}

}
