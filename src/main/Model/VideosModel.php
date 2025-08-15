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
	public $cloudinary_id;
	public $description;
	public $duration;
	public $format;
	public $hls_url;

	public function __construct()
	{
		$db = new DB();
		$this->conn = $db->conn();
	}

	public function uploadVideo(array $videoData)
	{
		$query = "INSERT INTO videos (
        user_id, 
        cloudinary_id, 
        title, 
        description, 
        genre, 
        age_rating, 
        publisher, 
        producer, 
        duration, 
        format, 
        video_url, 
        hls_url, 
        thumbnail_url
    ) VALUES (
        :user_id, 
        :cloudinary_id, 
        :title, 
        :description, 
        :genre, 
        :age_rating, 
        :publisher, 
        :producer, 
        :duration, 
        :format, 
        :video_url, 
        :hls_url, 
        :thumbnail_url
    ) RETURNING id, user_id, title, description, genre, age_rating, 
               publisher, producer, duration, format, video_url, 
               hls_url, thumbnail_url, cloudinary_id, uploaded_at";

		$stmt = $this->conn->prepare($query);
		$stmt->execute([
			':user_id' => $videoData['user_id'],
			':cloudinary_id' => $videoData['cloudinary_id'],
			':title' => $videoData['title'],
			':description' => $videoData['description'] ?? null,
			':genre' => $videoData['genre'] ?? null,
			':age_rating' => $videoData['age_rating'] ?? null,
			':publisher' => $videoData['publisher'] ?? null,
			':producer' => $videoData['producer'] ?? null,
			':duration' => $videoData['duration'],
			':format' => $videoData['format'],
			':video_url' => $videoData['video_url'],
			':hls_url' => $videoData['hls_url'],
			':thumbnail_url' => $videoData['thumbnail_url'] ?? null
		]);

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	private function getVideoById($id)
	{
		$sql = "SELECT 
                v.id, 
                v.user_id,
                u.username,
                v.title, 
                v.description,
                v.genre, 
                v.age_rating, 
                v.publisher,
                v.producer,
                v.video_url, 
                v.hls_url,
                v.thumbnail_url,
                v.cloudinary_id,
                v.duration,
                v.format,
                v.uploaded_at
            FROM videos v
            JOIN users u ON v.user_id = u.id
            WHERE v.id = :id
            LIMIT 1";

		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getAllVideos(int $limit = 20, int $offset = 0)
	{
		$sql = "SELECT 
                v.id, 
                v.user_id,
                u.username,
                v.title, 
                v.description,
                v.genre, 
                v.age_rating, 
                v.publisher,
                v.producer,
                v.video_url, 
                v.hls_url,
                v.thumbnail_url,
                v.cloudinary_id,
                v.duration,
                v.format,
                v.uploaded_at,
                -- Like count
                (SELECT COUNT(*) FROM likes l WHERE l.video_id = v.id) AS like_count,
                -- Comment count
                (SELECT COUNT(*) FROM comments c WHERE c.video_id = v.id) AS comment_count
            FROM videos v
            JOIN users u ON v.user_id = u.id
            ORDER BY v.uploaded_at DESC
            LIMIT :limit OFFSET :offset";

		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
		$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}


	public function searchVideo($filters, $limit = 10, $offset = 0)
	{
		$sql = "SELECT 
                id,
                user_id,
                title,
                description,
                genre,
                age_rating,
                publisher,
                producer,
                video_url,
                hls_url,
                thumbnail_url,
                cloudinary_id,
                duration,
                format,
                uploaded_at
            FROM videos WHERE 1=1";

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

		if (!empty($filters['user_id'])) {
			$sql .= " AND user_id = :user_id";
			$params[':user_id'] = $filters['user_id'];
		}

		$sql .= " ORDER BY uploaded_at DESC LIMIT :limit OFFSET :offset";

		$stmt = $this->conn->prepare($sql);

		foreach ($params as $key => $val) {
			$stmt->bindValue($key, $val);
		}

		$stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
		$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getVideo()
	{
		$sql = "SELECT 
                id,
                user_id,
                title,
                description,
                genre,
                age_rating,
                publisher,
                producer,
                video_url,
                hls_url,
                thumbnail_url,
                cloudinary_id,
                duration,
                format,
                uploaded_at
            FROM videos WHERE id = :id";

		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
		$stmt->execute();

		$video = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$video) {
			throw new ErrorException("Video not found");
		}

		return $video;
	}

	public function deleteVideo()
	{
		// First get video info before deleting
		$video = $this->getVideo();

		$sql = "DELETE FROM videos WHERE id = :video_id";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(':video_id', $this->video_id, PDO::PARAM_INT);
		$stmt->execute();

		return [
			'deleted' => true,
			'video_id' => $this->video_id,
			'cloudinary_id' => $video['cloudinary_id']
		];
	}
}