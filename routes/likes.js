const express = require("express");
const router = express.Router();
const LikesController = require("../controllers/LikesController");
const UsersMiddleware = require("../middleware/UsersMiddleware");

router.post("/toggle", UsersMiddleware, LikesController.toggleLike);
router.get(
  "/get/likes/count/:video_id",
  UsersMiddleware,
  LikesController.getLikesCount
);
router.get(
  "/has-liked/:user_id/:video_id",
  UsersMiddleware,
  LikesController.hasUserLiked
);
router.get(
  "/get/user/whoLiked/:video_id",
  UsersMiddleware,
  LikesController.getUserWhoLiked
);

module.exports = router;
