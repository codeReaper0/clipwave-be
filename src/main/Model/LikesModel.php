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
            $Sql = "DELETE FROM likes WHERE user_id = :user_id AND video_id = :video_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user_id' => $this->user_id, ':video_id' => $this->video_id]);
            return [
                'message' => 'Unliked successfully',
            ];
        } else {
            $sql = "INSERT INTO likes (user_id, video_id, liked_at) VALUES (:user_id, :video_id, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user_id' => $this->user_id, ':video_id' => $this->video_id]);
            return [
                'message' => 'Liked successfully',
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
