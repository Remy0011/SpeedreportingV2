const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    baseUrl: "http://127.0.0.1:8080",
    viewportWidth: 1280,
    viewportHeight: 800,
    video: false,
    screenshotOnRunFailure: true,
    defaultCommandTimeout: 8000,
    retries: {
      runMode: 1,
      openMode: 0,
    },
    env: {
      // Comptes de test (cf. dev/data.sql / dev/test_data.sql)
      adminEmail: "admin_test@example.com",
      adminPassword: "password123",
      userEmail: "user@example.com",
      userPassword: "password123",
    },
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});