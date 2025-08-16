const express = require("express");
const router = express.Router();
const UsersController = require("../controllers/UsersController");
const UsersMiddleware = require("../middleware/UsersMiddleware");
const videosRoutes = require("./videos");
const commentsRoutes = require("./comments");
const likesRoutes = require("./likes");

// Open routes (no authentication required)
router.post("/signup", UsersController.signUp);
router.post("/login", UsersController.login);

// Protected routes (require authentication)
router.use(UsersMiddleware);

router.use("/videos", videosRoutes);
router.use("/comments", commentsRoutes);
router.use("/likes", likesRoutes);

router.get("/profile", UsersController.getProfile);
router.get("/get/all", UsersController.getAll);
router.patch("/update/profile", UsersController.updateProfile);
router.patch("/update/password", UsersController.updatePassword);
router.delete("/delete/profile", UsersController.deleteProfile);
router.post("/logout", UsersController.logout);

module.exports = router;
