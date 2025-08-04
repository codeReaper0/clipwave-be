<?php

namespace Main\Model;

use ErrorException;
use Exception;
use Main\Utils\DB;
use PDO;

class VideosModel
{
    public $conn;
    public $id;
    public $user_id;
    public $video_id;
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

    public function uploadVideo(array $formData, array $uploadedFiles, int $user_id)
    {
        // Required fields
        $requiredFields = ['title', 'genre', 'age_rating', 'publisher', 'producer'];

        foreach ($requiredFields as $field) {
            if (empty($formData[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        // Uploaded files
        if (empty($uploadedFiles['videoFile']) || empty($uploadedFiles['thumbnailFile'])) {
            throw new Exception("Video and thumbnail files are required.");
        }

        $videoFile = $uploadedFiles['videoFile'];
        $thumbnailFile = $uploadedFiles['thumbnailFile'];

        if ($videoFile->getError() !== UPLOAD_ERR_OK || $thumbnailFile->getError() !== UPLOAD_ERR_OK) {
            // throw new Exception("Error in file upload.");
            throw new Exception("Video upload error code: " . $videoFile->getError() .
                ", Thumbnail upload error code: " . $thumbnailFile->getError() .
                ", video size: " . $videoFile->getSize() . " bytes");
        }

        // Upload directory
        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Save video
        $videoFilename = uniqid() . '_' . $videoFile->getClientFilename();
        $videoPath = $uploadDir . $videoFilename;
        $videoFile->moveTo($videoPath);
        $videoUrl = '/uploads/' . $videoFilename;

        // Save thumbnail
        $thumbFilename = uniqid() . '_' . $thumbnailFile->getClientFilename();
        $thumbPath = $uploadDir . $thumbFilename;
        $thumbnailFile->moveTo($thumbPath);
        $thumbnailUrl = '/uploads/' . $thumbFilename;

        // Save to database
        $sql = "INSERT INTO videos (
            title, publisher, producer, genre, age_rating,
            video_url, thumbnail_url, uploaded_at, user_id
        ) VALUES (
            :title, :publisher, :producer, :genre, :age_rating,
            :video_url, :thumbnail_url, NOW(), :user_id
        )";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':title', $formData['title']);
        $stmt->bindParam(':publisher', $formData['publisher']);
        $stmt->bindParam(':producer', $formData['producer']);
        $stmt->bindParam(':genre', $formData['genre']);
        $stmt->bindParam(':age_rating', $formData['age_rating']);
        $stmt->bindParam(':video_url', $videoUrl);
        $stmt->bindParam(':thumbnail_url', $thumbnailUrl);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // "message" => "Video uploaded successfully.",
        $videos = [
            "title" => $formData['title'],
            "genre" => $formData['genre'],
            "publisher" => $formData['publisher'],
            "producer" => $formData['producer'],
            "age_rating" => $formData['age_rating'],
            "video_url" => $videoUrl,
            "thumbnail_url" => $thumbnailUrl,

        ];
        return $videos;
    }
    public function getAllVideos()
    {
        $sql = "SELECT id, title, publisher, genre, age_rating, video_url, uploaded_at
            FROM videos
            ORDER BY uploaded_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $videos;
    }

    public function searchVideo($filters, $limit = 10, $offset = 0)
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

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        $searchData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $searchData;
    }
    public function getVideo()
    {
        $sql = "SELECT * FROM videos WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $video = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$video) {
            throw new ErrorException("Video not found");
        }

        $getVideo = [
            "id" => $video["id"],
            "username" => $video["title"],

        ];

        return $getVideo;
    }

    public function deleteVideo()
    {
        $sql = "DELETE FROM videos WHERE id=:video_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':video_id', $video_id);
        $stmt->execute();

        return [];

    }

}
