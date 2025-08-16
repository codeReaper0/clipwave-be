const {Pool} = require("pg");

class DB {
  constructor() {
    this.pool = new Pool({
      host: process.env.DB_HOST,
      port: process.env.DB_PORT || 5432,
      user: process.env.DB_USER,
      password: process.env.DB_PASS,
      database: process.env.DB_NAME,
      ssl: {
        rejectUnauthorized: false,
      },
    });
  }

  async query(text, params) {
    try {
      const result = await this.pool.query(text, params);
      return result;
    } catch (err) {
      console.error("Database error:", err);
      throw err;
    }
  }
}

module.exports = DB;
