import axios from "axios";
import { authService } from './auth';
import router from '../router';

const API_URL = "http://localhost:8000/api"
// Create an instance of axios with a base URL
const api = axios.create({
  baseURL: API_URL,
  headers: {
    "Content-Type": "application/json",
    "Accept": "application/json"
  }
});

// Add token to requests
api.interceptors.request.use(
  config => {
    const token = authService.getToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Handle token expiration
api.interceptors.response.use(
  response => response,
  async (error) => {
    if (error.response) {
      if (error.response.status === 401) {
        // Token expired or invalid
        authService.clearToken();
        console.log(router.currentRoute);
        // Push login if not in login
        if (router.currentRoute.path != "/login") {
          router.push('/login');
        }
      }

      if (error.response.status === 403) {
        if (router.currentRoute.path != "/") {
          authService.clearToken();
          router.push('/');
        }
      }
    }
    return Promise.reject(error);
  }
);

export default api;
export {
  api,
  API_URL
}; 