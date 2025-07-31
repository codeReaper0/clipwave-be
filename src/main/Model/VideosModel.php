<?php

use Main\Utils\DB;

class VideosModel
{
    public $conn;
    public $user_id;
    public $title;
    public $genre;
    public $age_rating;
    public $filename;
    public $video_url;
    public $videoFile;
    public $thumbnail_url;
    public $publisher;
    public $producer;
    public $uploaded_at;


    public function __construct()
    {
        $db = new DB();
        $this->conn = $db->conn();
    }

    public function upload()
    {
        if (!$this->title || !$this->genre || !$this->age_rating || !$this->publisher || !$this->producer) {
            throw new Exception("Missing required fields.");
        }
    
        // Upload directory
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    
        // Save video file and build URL
        $filename = uniqid() . '_' . $this->videoFile->getClientFilename();
        $this->videoFile->moveTo($uploadDir . $filename);
    
        $this->video_url = '/uploads/' . $filename; // relative path or replace with full URL if needed
    
        $sql = "INSERT INTO videos (title, publisher, producer, genre, age_rating, video_url, thumbnail_url, uploaded_at)
        VALUES (:title, :publisher, :producer, :genre, :age_rating, :video_url, :thumbnail_url, NOW())";

        $stmt = $this->conn->prepare($sql);

       
    $stmt->bindParam(':title', $this->title);
    $stmt->bindParam(':publisher', $this->publisher);
    $stmt->bindParam(':producer', $this->producer);
    $stmt->bindParam(':genre', $this->genre);
    $stmt->bindParam(':age_rating', $this->age_rating);
    $stmt->bindParam(':video_url', $this->video_url);
    $stmt->bindParam(':thumbnail_url', $this->thumbnail_url); 

    $stmt->execute();

    return [
        "message" => "Video uploaded successfully",
        "video" => [
            "title" => $this->title,
            "genre" => $this->genre,
            "publisher" => $this->publisher,
            "producer" => $this->producer,
            "age_rating" => $this->age_rating,
            "video_url" => $this->video_url,
            "thumbnail_url" => $this->thumbnail_url
        ]
        ];
    }
    public function getAllVideos()
{
    $sql = "SELECT id, title, publisher, genre, age_rating, video_url, created_at 
            FROM videos 
            ORDER BY created_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $videos;
}

public function searchVideos($filters, $limit = 10, $offset = 0)
{
    $sql = "SELECT * FROM videos WHERE 1=1";
    $params = [];

    if (!empty($filters['title'])) {
        $sql .= " AND title LIKE :title";
        $params[':title'] = "%" . $filters['title'] . "%";
    }

    if (!empty($filters['genre'])) {
        $sql .= " AND genre = :genre";
        $params[':genre'] = $filters['genre'];
    }

    if (!empty($filters['age_rating'])) {
        $sql .= " AND age_rating = :age_rating";
        $params[':age_rating'] = $filters['age_rating'];
    }

    $sql .= " ORDER BY uploaded_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $this->conn->prepare($sql);

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();
    $searchData=$stmt->fetchAll(PDO::FETCH_ASSOC);
    return $searchData;
}
public function getVideosByUser()
{
    $sql = "SELECT * FROM videos WHERE user_id = :user_id ORDER BY uploaded_at DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':user_id', $this->user_id);
    $stmt->execute();

    $getVideo=$stmt->fetchAll(PDO::FETCH_ASSOC);
    return $getVideo;
}

public function deleteVideo()
{
    $sql = "DELETE FROM video WHERE id=:id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $d);
    $stmt->execute();

    return [];

}



}
