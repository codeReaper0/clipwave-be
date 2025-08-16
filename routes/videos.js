const express = require("express");
const router = express.Router();
const VideosController = require("../controllers/VideosController");
const UsersMiddleware = require("../middleware/UsersMiddleware");

router.post("/upload", UsersMiddleware, VideosController.uploadVideo);
router.get("/all", UsersMiddleware, VideosController.getAllVideos);
router.get("/search", UsersMiddleware, VideosController.searchVideo);
router.get("/:id", UsersMiddleware, VideosController.getVideo);
router.delete("/:video_id", UsersMiddleware, VideosController.deleteVideo);

module.exports = router;
