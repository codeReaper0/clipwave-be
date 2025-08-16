const crypto = require("crypto");
const cloudinary = require("cloudinary").v2;

class SignatureController {
  generateSignature(req, res) {
    try {
      // Configure Cloudinary (move this to constructor if you use it elsewhere)
      cloudinary.config({
        cloud_name: process.env.CLOUDINARY_CLOUD_NAME,
        api_key: process.env.CLOUDINARY_API_KEY,
        api_secret: process.env.CLOUDINARY_API_SECRET,
        secure: true,
      });

      const {
        upload_preset = process.env.CLOUDINARY_UPLOAD_PRESET || "ml_default",
        title = "",
        description = "",
      } = req.body;

      const timestamp = Math.floor(Date.now() / 1000);
      const context = `title=${title}|description=${description}`;

      // Create the signature
      const signature = cloudinary.utils.api_sign_request(
        {
          timestamp,
          upload_preset,
          context,
        },
        process.env.CLOUDINARY_API_SECRET
      );

      res.json({
        signature,
        timestamp,
        api_key: process.env.CLOUDINARY_API_KEY,
        cloud_name: process.env.CLOUDINARY_CLOUD_NAME,
        upload_preset,
      });
    } catch (err) {
      console.error("Signature generation error:", err);
      res.status(500).json({
        error: err.message,
      });
    }
  }
}

module.exports = new SignatureController();
