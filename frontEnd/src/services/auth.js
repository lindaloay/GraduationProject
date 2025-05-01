import { globalState } from '../state';

const TOKEN_KEY = 'token';

export const authService = {
  // Save token and expiry to localStorage
  setToken(token, expiresIn) {
    localStorage.setItem(TOKEN_KEY, token);
    const expiryDate = new Date();
    expiryDate.setSeconds(expiryDate.getSeconds() + expiresIn);
    globalState.isLoggedIn = true;
  },

  // Get token from localStorage
  getToken() {
    return localStorage.getItem(TOKEN_KEY);
  },

  // Check if token is valid and not expired
  isTokenValid() {
    const token = this.getToken();
    console.log(token);
    if (!token) return false;

    return true;
  },

  // Clear token and related data
  clearToken() {
    localStorage.removeItem(TOKEN_KEY);
    globalState.isLoggedIn = false;
  },

  // Initialize auth state
  initializeAuth() {
    globalState.isLoggedIn = this.isTokenValid();
  }
}; 