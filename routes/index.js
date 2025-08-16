const express = require("express");
const router = express.Router();
const usersRoutes = require("./users");

router.use("/users", usersRoutes);

module.exports = {
  users: usersRoutes,
};
