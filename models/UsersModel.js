const DB = require("../utils/DB");
const jwt = require("jsonwebtoken");
const Random = require("../utils/Random");
const bcrypt = require("bcryptjs");

class UsersModel {
  constructor() {
    this.db = new DB();
    this.table = "users";
  }

  async signUp() {
    // Check if user exists
    const checkQuery = `
      SELECT id FROM users 
      WHERE username = $1 OR email = $2
    `;
    const checkResult = await this.db.query(checkQuery, [
      this.username,
      this.email,
    ]);

    if (checkResult.rowCount > 0) {
      throw new Error("Username or email already exists");
    }

    // Hash password
    this.password = await bcrypt.hash(this.password, 10);

    // Insert new user
    const insertQuery = `
      INSERT INTO users(username, email, password, role, created_at)
      VALUES($1, $2, $3, $4, NOW()) 
      RETURNING id, username, email, role, created_at
    `;
    const insertResult = await this.db.query(insertQuery, [
      this.username,
      this.email,
      this.password,
      this.role,
    ]);

    return insertResult.rows[0];
  }

  async login() {
    // Get user by username
    const query = `
      SELECT id, username, email, role, password 
      FROM users 
      WHERE username = $1
    `;
    const result = await this.db.query(query, [this.username]);

    if (result.rowCount === 0) {
      throw new Error("Invalid credentials");
    }

    const user = result.rows[0];

    // Verify password
    const validPassword = await bcrypt.compare(this.password, user.password);
    if (!validPassword) {
      throw new Error("Invalid password");
    }

    // Generate token
    const publicKey = Random.generate(12);
    const payload = {
      username: user.username,
      role: user.role,
      publicKey,
      id: user.id,
    };

    const token = jwt.sign(payload, process.env.JWT_SECRET, {
      algorithm: "HS512",
    });

    // Update public key in DB
    const updateQuery = `
      UPDATE users 
      SET public_key = $1 
      WHERE id = $2 
      RETURNING id
    `;
    await this.db.query(updateQuery, [publicKey, user.id]);

    // Return user data without password
    delete user.password;
    user.token = token;

    return user;
  }

  async getProfile() {
    const query = `
      SELECT id, username, role, email 
      FROM users 
      WHERE id = $1
    `;
    const result = await this.db.query(query, [this.id]);

    if (result.rowCount === 0) {
      throw new Error("User not found");
    }

    return result.rows[0];
  }

  async updateProfile() {
    // Get current user data
    const user = await this.getProfile();
    if (!user) {
      throw new Error("User not found");
    }

    if (!this.username && !this.role && !this.email) {
      throw new Error("Required data is missing or incomplete");
    }

    // Build update query dynamically
    let query = "UPDATE users SET ";
    const updates = [];
    const values = [];
    let paramCount = 1;

    if (this.username) {
      updates.push(`username = $${paramCount++}`);
      values.push(this.username);
    }
    if (this.role) {
      updates.push(`role = $${paramCount++}`);
      values.push(this.role);
    }
    if (this.email) {
      updates.push(`email = $${paramCount++}`);
      values.push(this.email);
    }

    query += updates.join(", ") + ` WHERE id = $${paramCount} RETURNING id`;
    values.push(this.id);

    const result = await this.db.query(query, values);

    // Build return data
    const updatedUser = {id: this.id};
    if (this.username) updatedUser.username = this.username;
    if (this.email) updatedUser.email = this.email;
    if (this.role) updatedUser.role = this.role;

    return updatedUser;
  }

  async updatePassword(oldPassword, newPassword) {
    // Get current password
    const query = `
      SELECT password 
      FROM users 
      WHERE id = $1
    `;
    const result = await this.db.query(query, [this.id]);

    if (result.rowCount === 0) {
      throw new Error("User not found");
    }

    const currentPassword = result.rows[0].password;

    // Verify old password
    const validPassword = await bcrypt.compare(oldPassword, currentPassword);
    if (!validPassword) {
      throw new Error("Invalid password");
    }

    // Hash new password
    const encryptedNewPassword = await bcrypt.hash(newPassword, 10);

    // Update password
    const updateQuery = `
      UPDATE users 
      SET password = $1 
      WHERE id = $2
    `;
    await this.db.query(updateQuery, [encryptedNewPassword, this.id]);

    // Logout user
    await this.logout();

    return {id: this.id};
  }

  async getAll() {
    const query = "SELECT * FROM users";
    const result = await this.db.query(query);
    return result.rows;
  }

  async logout() {
    const query = `
      UPDATE users 
      SET public_key = NULL 
      WHERE id = $1
    `;
    await this.db.query(query, [this.id]);
    return true;
  }

  async deleteProfile() {
    const query = `
      DELETE FROM users 
      WHERE id = $1
    `;
    await this.db.query(query, [this.id]);
    return {};
  }

  static async authenticate(userData) {
    const db = new DB();
    const query = `
      SELECT id 
      FROM users 
      WHERE id = $1 AND username = $2 AND public_key = $3
    `;
    const result = await db.query(query, [
      userData.id,
      userData.username,
      userData.publicKey,
    ]);

    if (result.rowCount === 0) {
      throw new Error("Authorization failed. Please login");
    }

    return result.rows[0];
  }
}

module.exports = UsersModel;
