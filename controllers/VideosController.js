const VideosModel = require("../models/VideosModel");
const cloudinary = require("cloudinary").v2;

class VideosController {
  constructor() {
    this.cloudinary = require("cloudinary").v2;
    this.cloudinary.config({
      cloud_name: process.env.CLOUDINARY_CLOUD_NAME,
      api_key: process.env.CLOUDINARY_API_KEY,
      api_secret: process.env.CLOUDINARY_API_SECRET,
      secure: true,
    });

    this.uploadVideo = this.uploadVideo.bind(this);
    this.getAllVideos = this.getAllVideos.bind(this);
    this.searchVideo = this.searchVideo.bind(this);
    this.getVideo = this.getVideo.bind(this);
    this.deleteVideo = this.deleteVideo.bind(this);
  }

  // Make these methods instance methods
  generateHlsUrl(publicId) {
    return `https://res.cloudinary.com/${process.env.CLOUDINARY_CLOUD_NAME}/video/upload/sp_hd/${publicId}.m3u8`;
  }

  generateThumbnailUrl(publicId) {
    return `https://res.cloudinary.com/${process.env.CLOUDINARY_CLOUD_NAME}/video/upload/w_500,h_500,c_fill/${publicId}.jpg`;
  }

  async uploadVideo(req, res) {
    try {
      const user_id = req.user.id;
      const {
        cloudinaryId,
        title = "Untitled Video",
        description = "",
        genre = null,
        age_rating = null,
        publisher = null,
        producer = null,
        duration = 0,
        format = "mp4",
        url,
      } = req.body;

      if (!cloudinaryId) {
        throw new Error("Cloudinary ID is required");
      }

      const videoModel = new VideosModel();
      const dbResult = await videoModel.uploadVideo({
        user_id,
        cloudinary_id: cloudinaryId,
        title,
        description,
        genre,
        age_rating,
        publisher,
        producer,
        duration,
        format,
        video_url: url,
        hls_url: this.generateHlsUrl(cloudinaryId),
        thumbnail_url: this.generateThumbnailUrl(cloudinaryId),
      });

      return res.status(201).json({
        success: true,
        video: dbResult,
      });
    } catch (err) {
      console.error("Upload error:", err);
      return res.status(400).json({
        success: false,
        message: err.message,
      });
    }
  }

  async getAllVideos(req, res) {
    try {
      const limit = parseInt(req.query.limit) || 20;
      const offset = parseInt(req.query.offset) || 0;

      const videoModel = new VideosModel();
      const videos = await videoModel.getAllVideos(limit, offset);

      return res.status(200).json({
        success: true,
        data: videos,
        count: videos.length,
      });
    } catch (err) {
      return res.status(500).json({
        message: err.message,
      });
    }
  }

  async searchVideo(req, res) {
    try {
      const limit = parseInt(req.query.limit) || 10;
      const offset = parseInt(req.query.offset) || 0;

      const videoModel = new VideosModel();
      const videos = await videoModel.searchVideo(req.query, limit, offset);

      return res.status(200).json({
        success: true,
        data: videos,
        count: videos.length,
      });
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async getVideo(req, res) {
    try {
      const {id} = req.params;

      const videoModel = new VideosModel();
      videoModel.id = id;

      const video = await videoModel.getVideo();

      return res.status(200).json({
        success: true,
        data: video,
      });
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async deleteVideo(req, res) {
    try {
      const {video_id} = req.params;

      const videoModel = new VideosModel();
      videoModel.video_id = video_id;

      // First get video info from database
      const video = await videoModel.getVideo();
      if (!video) {
        throw new Error("Video not found");
      }

      // Delete from Cloudinary
      await cloudinary.uploader.destroy(video.cloudinary_id, {
        resource_type: "video",
        invalidate: true,
      });

      // Delete from database
      const deleteResult = await videoModel.deleteVideo();

      return res.status(200).json({
        success: true,
        message: "Video deleted successfully",
        data: deleteResult,
      });
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }
}

module.exports = new VideosController();
