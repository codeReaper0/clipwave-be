<?php
namespace Main\Model;

use Exception;
use Main\Utils\DB;
use PDO;

class CommentsModel
{
	public $id;
	public $commentText;
	public $video_id;
	public $user_id;
	public $created_at;

	public $conn;
	public $dbtable = "comments";

	public function __construct()
	{
		$database = new DB();
		$this->conn = $database->conn();
	}

	public function add()
	{
		$sql = "INSERT INTO comments (video_id, user_id, comment_text, created_at) 
                VALUES (:video_id, :user_id, :commentText, NOW())
                RETURNING id, video_id, user_id, comment_text, created_at";

		$stmt = $this->conn->prepare($sql);
		$stmt->execute([
			':video_id' => $this->video_id,
			':user_id' => $this->user_id,
			':commentText' => $this->commentText
		]);

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getByVideoId($video_id)
	{
		$sql = "SELECT c.*, u.username 
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.video_id = :video_id
                ORDER BY c.created_at DESC";

		$stmt = $this->conn->prepare($sql);
		$stmt->execute([':video_id' => $video_id]);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function delete()
	{
		$sql = "DELETE FROM comments WHERE id = :id RETURNING video_id";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute([':id' => $this->id]);

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function belongsToUser($user_id)
	{
		$sql = "SELECT 1 FROM comments WHERE id = :id AND user_id = :user_id";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute([
			':id' => $this->id,
			':user_id' => $user_id
		]);

		return $stmt->rowCount() > 0;
	}

	public function getVideoId()
	{
		$sql = "SELECT video_id FROM comments WHERE id = :id";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute([':id' => $this->id]);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result['video_id'] ?? null;
	}

	public function getCommentCount($video_id)
	{
		$sql = "SELECT COUNT(*) as count FROM comments WHERE video_id = :video_id";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute([':video_id' => $video_id]);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result['count'] ?? 0;
	}
}