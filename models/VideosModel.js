const DB = require("../utils/DB");

class VideosModel {
  constructor() {
    this.db = new DB();
    this.table = "videos";
  }

  async uploadVideo(videoData) {
    const query = `
      INSERT INTO videos (
        user_id, cloudinary_id, title, description, genre, 
        age_rating, publisher, producer, duration, format, 
        video_url, hls_url, thumbnail_url
      ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13
      ) RETURNING 
        id, user_id, title, description, genre, age_rating, 
        publisher, producer, duration, format, video_url, 
        hls_url, thumbnail_url, cloudinary_id, uploaded_at
    `;

    const values = [
      videoData.user_id,
      videoData.cloudinary_id,
      videoData.title,
      videoData.description || null,
      videoData.genre || null,
      videoData.age_rating || null,
      videoData.publisher || null,
      videoData.producer || null,
      videoData.duration,
      videoData.format,
      videoData.video_url,
      videoData.hls_url,
      videoData.thumbnail_url || null,
    ];

    const result = await this.db.query(query, values);
    return result.rows[0];
  }

  async getAllVideos(limit = 20, offset = 0) {
    const query = `
      SELECT 
        v.id, v.user_id, u.username, v.title, v.description,
        v.genre, v.age_rating, v.publisher, v.producer,
        v.video_url, v.hls_url, v.thumbnail_url, v.cloudinary_id,
        v.duration, v.format, v.uploaded_at,
        (SELECT COUNT(*) FROM likes l WHERE l.video_id = v.id) AS like_count,
        (SELECT COUNT(*) FROM comments c WHERE c.video_id = v.id) AS comment_count
      FROM videos v
      JOIN users u ON v.user_id = u.id
      ORDER BY v.uploaded_at DESC
      LIMIT $1 OFFSET $2
    `;

    const result = await this.db.query(query, [limit, offset]);
    return result.rows;
  }

  async searchVideo(filters, limit = 10, offset = 0) {
    let query = `
      SELECT 
        id, user_id, title, description, genre, age_rating,
        publisher, producer, video_url, hls_url, thumbnail_url,
        cloudinary_id, duration, format, uploaded_at
      FROM videos 
      WHERE 1=1
    `;

    const values = [];
    let paramCount = 1;

    if (filters.title) {
      query += ` AND title LIKE $${paramCount++}`;
      values.push(`%${filters.title}%`);
    }

    if (filters.genre) {
      query += ` AND genre = $${paramCount++}`;
      values.push(filters.genre);
    }

    if (filters.age_rating) {
      query += ` AND age_rating = $${paramCount++}`;
      values.push(filters.age_rating);
    }

    if (filters.user_id) {
      query += ` AND user_id = $${paramCount++}`;
      values.push(filters.user_id);
    }

    query += ` ORDER BY uploaded_at DESC LIMIT $${paramCount++} OFFSET $${paramCount++}`;
    values.push(limit, offset);

    const result = await this.db.query(query, values);
    return result.rows;
  }

  async getVideo() {
    const query = `
      SELECT 
        v.id, v.user_id, u.username, v.title, v.description,
        v.genre, v.age_rating, v.publisher, v.producer,
        v.video_url, v.hls_url, v.thumbnail_url, v.cloudinary_id,
        v.duration, v.format, v.uploaded_at
      FROM videos v
      JOIN users u ON v.user_id = u.id
      WHERE v.id = $1
      LIMIT 1
    `;

    const result = await this.db.query(query, [this.id]);

    if (result.rowCount === 0) {
      throw new Error("Video not found");
    }

    return result.rows[0];
  }

  async deleteVideo() {
    // First get video info
    const video = await this.getVideo();
    if (!video) {
      throw new Error("Video not found");
    }

    // Delete from database
    const query = `
      DELETE FROM videos 
      WHERE id = $1
    `;
    await this.db.query(query, [this.video_id]);

    return {
      deleted: true,
      video_id: this.video_id,
      cloudinary_id: video.cloudinary_id,
    };
  }
}

module.exports = VideosModel;
