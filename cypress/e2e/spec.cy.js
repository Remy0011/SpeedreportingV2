// app_test.cy.js
describe('Speed Reporting App E2E Tests', () => {

  // User credentials (using user@example.com from your test data)
  const userEmail = 'user@example.com';
  const userPassword = 'password123';
  const protectedPages = ['/dashboard', '/mes-heures'];

  it('Visits the homepage and displays login form', () => {
    cy.visit('/');
    cy.contains('Se connecter').should('be.visible');
    cy.get('input[name="email"]').should('be.visible');
    cy.get('input[name="password"]').should('be.visible');
    cy.get('button[type="submit"]').should('contain', 'Se connecter');
  });

  it('Logs in successfully and redirects to dashboard', () => {
    cy.visit('/connexion');
    cy.get('input[name="email"]').type(userEmail);
    cy.get('input[name="password"]').type(userPassword);
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/dashboard');
    cy.contains('Dashboard').should('be.visible');
  });

  it('Visits protected pages after login', () => {
    cy.login(userEmail, userPassword);
    cy.wrap(protectedPages).each((page) => {
      cy.visit(page);
      cy.url().should('include', page);
    });
  });

  it('Handles invalid login', () => {
    cy.visit('/connexion');
    cy.get('input[name="email"]').type('invalid@example.com');
    cy.get('input[name="password"]').type('wrongpassword');
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/connexion');
    cy.contains('Se connecter').should('be.visible');
    // Check for error message if your app displays one, e.g., cy.contains('Invalid credentials');
  });

});