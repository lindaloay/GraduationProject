import { reactive } from "vue";
import api from "./services/api";

export const globalState = reactive({
  user: null,
  isLoggedIn: false,
  isLoading: false,
  error: null,

  // Method to fetch and update user profile
  async fetchUserProfile() {
    try {
      this.isLoading = true;
      this.error = null;
      
      const response = await api.get('user/profile', {
        headers: {
          'Authorization': `${localStorage.getItem('token')}`
        }
      });

      if (response.data.success) {
        this.user = response.data.user;
        this.isLoggedIn = true;
      }
    } catch (error) {
      this.error = error.response?.data?.message || 'Error fetching user profile';
      console.error('Profile fetch error:', error);
    } finally {
      this.isLoading = false;
    }
  },

  // Method to update user data
  updateUser(userData) {
    this.user = userData;
    this.isLoggedIn = !!userData;
  },

  // Method to clear user data
  clearUser() {
    this.user = null;
    this.isLoggedIn = false;
  },

  // Method to handle login
  async login(token) {
    localStorage.setItem('token', token);
    await this.fetchUserProfile();
  },

  // Method to handle logout
  logout() {
    localStorage.removeItem('token');
    this.clearUser();
  }
});
