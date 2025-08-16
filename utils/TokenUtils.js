const jwt = require("jsonwebtoken");

class TokenUtils {
  constructor() {
    this.secretKey = process.env.JWT_SECRET;
    this.algorithm = "HS512";
  }

  generateToken(payload) {
    const issuedAt = Math.floor(Date.now() / 1000);
    const expirationTime = issuedAt + 3600; // 1 hour

    const tokenPayload = {
      ...payload,
      iat: issuedAt,
      exp: expirationTime,
    };

    return jwt.sign(tokenPayload, this.secretKey, {algorithm: this.algorithm});
  }

  extractDataFromToken(req) {
    const authHeader = req.headers.authorization;
    if (!authHeader) {
      throw new Error("Authorization header missing");
    }

    const tokenMatch = authHeader.match(/Bearer\s(\S+)/);
    if (!tokenMatch) {
      throw new Error("Token not found in header");
    }

    const token = tokenMatch[1];
    try {
      return jwt.verify(token, this.secretKey, {algorithms: [this.algorithm]});
    } catch (err) {
      throw new Error("Invalid token: " + err.message);
    }
  }

  validateToken(token) {
    try {
      jwt.verify(token, this.secretKey, {algorithms: [this.algorithm]});
      return true;
    } catch (err) {
      return false;
    }
  }
}

module.exports = TokenUtils;
