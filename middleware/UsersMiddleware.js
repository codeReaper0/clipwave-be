const UsersModel = require("../models/UsersModel");
const TokenUtils = require("../utils/TokenUtils");

module.exports = async (req, res, next) => {
  try {
    const tokenUtils = new TokenUtils();
    const userData = await tokenUtils.extractDataFromToken(req);

    const isAuthenticated = await UsersModel.authenticate(userData);
    if (!isAuthenticated) {
      throw new Error("Authentication failed");
    }

    req.user = userData;
    next();
  } catch (error) {
    res.status(401).json({
      message: error.message,
    });
  }
};
