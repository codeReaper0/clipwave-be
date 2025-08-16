class Random {
  static generate(length = 8) {
    const characters =
      "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return Array.from({length}, () =>
      characters.charAt(Math.floor(Math.random() * characters.length))
    ).join("");
  }

  static generateApiKey(length = 48) {
    const characters =
      "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return Array.from({length}, () =>
      characters.charAt(Math.floor(Math.random() * characters.length))
    ).join("");
  }
}

module.exports = Random;
