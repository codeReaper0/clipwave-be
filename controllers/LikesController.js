const LikesModel = require("../models/LikesModel");

class LikesController {
  async toggleLike(req, res) {
    try {
      const {video_id, user_id} = req.body;

      const likeModel = new LikesModel();
      likeModel.user_id = user_id;
      likeModel.video_id = video_id;

      const result = await likeModel.toggleLike();

      return res.status(200).json(result);
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async getLikesCount(req, res) {
    try {
      const {video_id} = req.params;

      const likeModel = new LikesModel();
      likeModel.video_id = video_id;

      const likesCount = await likeModel.countLikes();

      return res.status(200).json({
        message: "likes count retrieved successfully",
        data: {
          video_id,
          likes_count: likesCount,
        },
      });
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async hasUserLiked(req, res) {
    try {
      const {user_id, video_id} = req.params;

      if (!video_id) {
        throw new Error("Video ID is required");
      }

      const likeModel = new LikesModel();
      likeModel.user_id = user_id;
      likeModel.video_id = video_id;

      const liked = await likeModel.hasUserLiked();

      return res.status(200).json({
        success: true,
        video_id,
        liked,
      });
    } catch (err) {
      return res.status(400).json({
        success: false,
        message: err.message,
      });
    }
  }

  async getUserWhoLiked(req, res) {
    try {
      const {video_id} = req.params;

      const likeModel = new LikesModel();
      likeModel.video_id = video_id;

      const users = await likeModel.getUsersWhoLiked();

      return res.status(200).json(users);
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }
}

module.exports = new LikesController();
