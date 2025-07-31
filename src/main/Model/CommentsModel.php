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
    public $videoId;
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
        if (!$this->videoId || !$this->user_id || !$this->commentText) {
            throw new Exception("Missing required fields.");
        }

        $sql = "INSERT INTO comments (video_id, user_id, comment_text) VALUES (:videoId, :user_id, :commentText)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':video_id', $this->videoId);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':comment_text', $this->commentText);
        $stmt->execute();

        return [
            "message" => "Comment added successfully",
        ];
    }
    public function getByVideoId()
{
    $sql = "SELECT c.id, c.commentText, c.created_at, u.username
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.videoId = :videoId
            ORDER BY c.created_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':video_id', $videoId);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function delete($commentId)
{
    // Check if user owns the comment
    $sql = "DELETE FROM comments WHERE id = :id AND user_id = :user_id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $commentId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        throw new Exception("Comment not found or you're not authorized to delete it.");
    }

    return [];
}
}
