const swaggerJsdoc = require("swagger-jsdoc");
const swaggerUi = require("swagger-ui-express");
const YAML = require("yamljs");
const path = require("path");

// Load the YAML file
const swaggerDocument = YAML.load(path.join(__dirname, "swagger.yaml"));

module.exports = (app) => {
  // Serve Swagger UI at /
  app.use("/", swaggerUi.serve, swaggerUi.setup(swaggerDocument));

  // JSON endpoint for Swagger spec
  app.get("/swagger.json", (req, res) => {
    res.setHeader("Content-Type", "application/json");
    res.send(swaggerDocument);
  });
};
