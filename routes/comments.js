const express = require("express");
const router = express.Router();
const CommentsController = require("../controllers/CommentsController");
const UsersMiddleware = require("../middleware/UsersMiddleware");

router.post("/add", UsersMiddleware, CommentsController.addComment);
router.get("/:video_id", UsersMiddleware, CommentsController.getComments);
router.delete("/:id", UsersMiddleware, CommentsController.deleteComment);

module.exports = router;
