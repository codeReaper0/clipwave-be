const CommentsModel = require("../models/CommentsModel");

class CommentsController {
  constructor() {
    this.addComment = this.addComment.bind(this);
    this.getComments = this.getComments.bind(this);
    this.deleteComment = this.deleteComment.bind(this);
  }

  jsonResponse(res, data, status = 200) {
    return res.status(status).json(data);
  }

  async addComment(req, res) {
    try {
      const {content, user_id, video_id} = req.body;

      if (!content || !user_id || !video_id) {
        throw new Error("All fields are required");
      }

      const commentModel = new CommentsModel();
      commentModel.video_id = video_id;
      commentModel.user_id = user_id;
      commentModel.commentText = content;

      const comment = await commentModel.add();
      const commentCount = await commentModel.getCommentCount(video_id);

      return this.jsonResponse(res, {
        success: true,
        comment,
        comment_count: commentCount,
      });
    } catch (err) {
      return this.jsonResponse(
        res,
        {
          success: false,
          message: err.message,
        },
        400
      );
    }
  }

  async getComments(req, res) {
    try {
      const {video_id} = req.params;

      if (!video_id) {
        throw new Error("Video ID is required");
      }

      const commentModel = new CommentsModel();
      const comments = await commentModel.getByVideoId(video_id);

      return this.jsonResponse(res, {
        success: true,
        comments,
      });
    } catch (err) {
      return this.jsonResponse(
        res,
        {
          success: false,
          message: err.message,
        },
        400
      );
    }
  }

  async deleteComment(req, res) {
    try {
      const {id} = req.params;
      const user_id = req.user.id;

      if (!id) {
        throw new Error("Comment ID is required");
      }

      const commentModel = new CommentsModel();
      commentModel.id = id;

      // Verify comment belongs to user
      const belongs = await commentModel.belongsToUser(user_id);
      if (!belongs) {
        throw new Error("Unauthorized to delete this comment");
      }

      const video_id = await commentModel.getVideoId();
      const deleted = await commentModel.delete();
      const commentCount = await commentModel.getCommentCount(video_id);

      return this.jsonResponse(res, {
        success: true,
        deleted,
        comment_count: commentCount,
      });
    } catch (err) {
      return this.jsonResponse(
        res,
        {
          success: false,
          message: err.message,
        },
        400
      );
    }
  }
}

module.exports = new CommentsController();
