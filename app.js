require("dotenv").config();
const path = require("path");
const express = require("express");
const cors = require("cors");
const routes = require("./routes");
const CORSMiddleware = require("./middleware/CORSMiddleware");
const SignatureController = require("./controllers/SignatureController");

const app = express();

// Middleware
app.use(express.json());
app.use(cors());
app.use(CORSMiddleware);

// Basic routes
app.get("/", (req, res) => {
  res.send(`Backend is running! | ${new Date().toISOString()}`);
});

app.get("/test", (req, res) => {
  res.send("Test successful");
});

app.get("/debug", (req, res) => {
  const routesList = app._router.stack
    .filter((layer) => layer.route)
    .map((layer) => {
      return {
        path: layer.route.path,
        methods: Object.keys(layer.route.methods),
      };
    });

  res.json(routesList);
});

app.post("/cloudinary/signature", SignatureController.generateSignature);

// Load routes
app.use("/users", routes.users);

// Health check
app.get("/health", (req, res) => {
  res.send("OK");
});

// 404 handler
app.use((req, res) => {
  res.status(404).json({error: "Not Found"});
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
