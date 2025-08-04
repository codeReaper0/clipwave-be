<?php

namespace Main\Model;

use Exception;
use Main\Utils\DB;
use PDO;

class CommentsModel
{
    public $id;
    public $username;
    public $commentText;
    public $video_id;
    public $email;
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
        $sql = "SELECT id FROM videos";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $videoResult = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT id FROM users ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $userdata = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$this->video_id || !$this->user_id || !$this->commentText) {
            throw new Exception("Missing required fields.");
        }

        $sql = "INSERT INTO comments (video_id, user_id, commentText, created_at) VALUES (:video_id, :user_id, :commentText, :created_at)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':video_id', $this->video_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':commentText', $this->commentText);
        $stmt->bindParam(':created_at', $this->created_at);

        $stmt->execute();

        $sql = "SELECT id, video_id, user_id, created_at FROM " . $this->dbtable . " WHERE video_id=:video_id";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':video_id', $this->video_id);
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        return $comment;

    }
    public function getByVideoId()
    {
        $sql = "SELECT id FROM videos";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $videoResult = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT id,user_id,video_id, commentText, created_at FROM comments";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $videoResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $getComment = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $getComment;
    }
    public function delete()
    {
        // Check if user owns the comment
    {
        $sql = "DELETE FROM comments WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return [];

    }
    }
}
