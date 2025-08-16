const UsersModel = require("../models/UsersModel");
const TokenUtils = require("../utils/TokenUtils");

class UsersController {
  async signUp(req, res) {
    try {
      const {username, email, password, role} = req.body;

      const usersModel = new UsersModel();
      usersModel.username = username;
      usersModel.email = email;
      usersModel.password = password;
      usersModel.role = role;

      const user = await usersModel.signUp();

      return res.status(200).json(user);
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async login(req, res) {
    try {
      const {username, password} = req.body;

      const userModel = new UsersModel();
      userModel.username = username;
      userModel.password = password;

      const user = await userModel.login();

      return res.status(200).json(user);
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async getProfile(req, res) {
    try {
      const userModel = new UsersModel();
      userModel.id = req.user.id;

      const profile = await userModel.getProfile();

      return res.status(200).json(profile);
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async updateProfile(req, res) {
    try {
      const {username, email, role} = req.body;

      const userModel = new UsersModel();
      userModel.id = req.user.id;
      userModel.username = username;
      userModel.email = email;
      userModel.role = role;

      const updatedUser = await userModel.updateProfile();

      return res.status(200).json(updatedUser);
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async updatePassword(req, res) {
    try {
      const {oldPassword, newPassword} = req.body;

      const userModel = new UsersModel();
      userModel.id = req.user.id;

      const result = await userModel.updatePassword(oldPassword, newPassword);

      return res.status(200).json(result);
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async getAll(req, res) {
    try {
      const userModel = new UsersModel();
      const users = await userModel.getAll();

      return res.status(200).json(users);
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async logout(req, res) {
    try {
      const userModel = new UsersModel();
      userModel.id = req.user.id;

      await userModel.logout();

      return res.status(200).json({message: "logout successful"});
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }

  async deleteProfile(req, res) {
    try {
      const userModel = new UsersModel();
      userModel.id = req.user.id;

      const result = await userModel.deleteProfile();

      return res.status(200).json(result);
    } catch (err) {
      return res.status(400).json({
        message: err.message,
      });
    }
  }
}

module.exports = new UsersController();
