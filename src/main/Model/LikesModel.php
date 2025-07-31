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
        $sql = "SELECT * FROM likes WHERE user_id = :user_id AND video_id = :video_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $this->user_id, ':video_id' => $this->video_id]);

        if ($stmt->rowCount() > 0) {
            $deleteSql = "DELETE FROM likes WHERE user_id = :user_id AND video_id = :video_id";
            $delStmt = $this->conn->prepare($deleteSql);
            $delStmt->execute([':user_id' => $this->user_id, ':video_id' => $this->video_id]);
            return ['message' => 'Unliked successfully'];
        } else {
            $insertSql = "INSERT INTO likes (user_id, video_id, liked_at) VALUES (:user_id, :video_id, NOW())";
            $insStmt = $this->conn->prepare($insertSql);
            $insStmt->execute([':user_id' => $this->user_id, ':video_id' => $this->video_id]);
            return ['message' => 'Liked successfully'];
        }
    }

    public function countLikes()
    {
        $sql = "SELECT COUNT(*) as total FROM likes WHERE video_id = :video_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':video_id' => $this->video_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function hasUserLiked()
    {
        $sql = "SELECT 1 FROM likes WHERE user_id = :user_id AND video_id = :video_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $this->user_id, ':video_id' => $this->video_id]);
        return $stmt->rowCount() > 0;
    }

    public function getUserLikedVideos($userId): array
    {
        $sql = "SELECT v.* FROM likes l
            JOIN videos v ON l.video_id = v.id
            WHERE l.user_id = :user_id
            ORDER BY l.liked_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
   

}
