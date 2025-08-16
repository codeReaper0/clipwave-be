const DB = require("../utils/DB");

class LikesModel {
  constructor() {
    this.db = new DB();
    this.table = "likes";
  }

  async toggleLike() {
    // Check if like exists
    const checkQuery = `
      SELECT 1 FROM likes 
      WHERE user_id = $1 AND video_id = $2
    `;
    const checkResult = await this.db.query(checkQuery, [
      this.user_id,
      this.video_id,
    ]);

    if (checkResult.rowCount > 0) {
      // Unlike
      const deleteQuery = `
        DELETE FROM likes 
        WHERE user_id = $1 AND video_id = $2
      `;
      await this.db.query(deleteQuery, [this.user_id, this.video_id]);

      const count = await this.countLikes();
      return {
        message: "Unliked successfully",
        is_liked: false,
        likes_count: count,
      };
    } else {
      // Like
      const insertQuery = `
        INSERT INTO likes (user_id, video_id, liked_at) 
        VALUES ($1, $2, NOW())
      `;
      await this.db.query(insertQuery, [this.user_id, this.video_id]);

      const count = await this.countLikes();
      return {
        message: "Liked successfully",
        is_liked: true,
        likes_count: count,
      };
    }
  }

  async countLikes() {
    const query = `
      SELECT COUNT(*) as total 
      FROM likes 
      WHERE video_id = $1
    `;
    const result = await this.db.query(query, [this.video_id]);
    return parseInt(result.rows[0].total);
  }

  async hasUserLiked() {
    const query = `
      SELECT 1 FROM likes 
      WHERE user_id = $1 AND video_id = $2 
      LIMIT 1
    `;
    const result = await this.db.query(query, [this.user_id, this.video_id]);
    return result.rowCount > 0;
  }

  async getUsersWhoLiked() {
    const query = `
      SELECT users.id, users.username
      FROM likes
      JOIN users ON likes.user_id = users.id
      WHERE likes.video_id = $1
    `;
    const result = await this.db.query(query, [this.video_id]);
    return result.rows;
  }
}

module.exports = LikesModel;
