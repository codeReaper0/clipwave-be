const DB = require("../utils/DB");

class CommentsModel {
  constructor() {
    this.db = new DB();
    this.table = "comments";
  }

  async add() {
    const query = `
      INSERT INTO comments (video_id, user_id, comment_text, created_at) 
      VALUES ($1, $2, $3, NOW())
      RETURNING id, video_id, user_id, comment_text, created_at
    `;
    const values = [this.video_id, this.user_id, this.commentText];
    const result = await this.db.query(query, values);
    return result.rows[0];
  }

  async getByVideoId(video_id) {
    const query = `
      SELECT c.*, u.username 
      FROM comments c
      JOIN users u ON c.user_id = u.id
      WHERE c.video_id = $1
      ORDER BY c.created_at DESC
    `;
    const result = await this.db.query(query, [video_id]);
    return result.rows;
  }

  async delete() {
    const query = `
      DELETE FROM comments 
      WHERE id = $1 
      RETURNING video_id
    `;
    const result = await this.db.query(query, [this.id]);
    return result.rows[0];
  }

  async belongsToUser(user_id) {
    const query = `
      SELECT 1 FROM comments 
      WHERE id = $1 AND user_id = $2
    `;
    const result = await this.db.query(query, [this.id, user_id]);
    return result.rowCount > 0;
  }

  async getVideoId() {
    const query = `
      SELECT video_id FROM comments 
      WHERE id = $1
    `;
    const result = await this.db.query(query, [this.id]);
    return result.rows[0]?.video_id;
  }

  async getCommentCount(video_id) {
    const query = `
      SELECT COUNT(*) as count 
      FROM comments 
      WHERE video_id = $1
    `;
    const result = await this.db.query(query, [video_id]);
    return parseInt(result.rows[0].count);
  }
}

module.exports = CommentsModel;
