<template>
  <v-app>
    <template v-if="!isAdminRoute">
      <!-- Regular user layout -->
      <HeaderCompo />
      <router-view />
      <FooterCompo />
    </template>
    
    <template v-else>
      <!-- Admin layout - no header/footer -->
      <router-view />
    </template>
    
    <!-- Global Snackbar -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
      top
      shaped
    >
      <div class="d-flex align-center">
        <v-icon v-if="snackbar.color === 'success'" left>mdi-check-circle</v-icon>
        <v-icon v-else-if="snackbar.color === 'error'" left>mdi-alert-circle</v-icon>
        {{ snackbar.text }}
      </div>
      <template v-slot:action="{ attrs }">
        <v-btn
          text
          v-bind="attrs"
          @click="snackbar.show = false"
        >
          إغلاق
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script>
import HeaderCompo from "@/components/HeaderCompo.vue";
import FooterCompo from "@/components/FooterCompo.vue";
import { globalState } from "./state";
import { authService } from "./services/auth";
import api from "./services/api";

export default {
  components: {
    HeaderCompo,
    FooterCompo,
  },
  data() {
    return {
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      }
    };
  },
  computed: {
    isAdminRoute() {
      return this.$route.path.startsWith('/admin');
    }
  },
  watch: {
    '$route'() {
      // Update when route changes
      // This is needed because the computed property won't
      // automatically update during navigation
      console.log('Route changed to:', this.$route.path);
      console.log('Is admin route:', this.isAdminRoute);
    }
  },
  async created() {
    // Initialize auth state from localStorage
    authService.initializeAuth();

    // If user is logged in, fetch their data
    if (globalState.isLoggedIn) {
      try {
        const response = await api.get("user/profile");
        
        if (response.data && response.data.user) {
          globalState.user = response.data.user;
          console.log("User loaded:", globalState.user);
        } else {
          console.error("User data not found in response");
          authService.clearToken();
        }
      } catch (error) {
        console.error("Error fetching user profile:", error);
        // If there's an error (e.g., token expired), clear the auth state
        authService.clearToken();
      }
    }
    
    // Listen for snackbar events
    this.$root.$on('show-snackbar', this.showSnackbar);
  },
  beforeDestroy() {
    // Clean up event listener
    this.$root.$off('show-snackbar', this.showSnackbar);
  },
  methods: {
    showSnackbar({ text, color }) {
      this.snackbar.text = text;
      this.snackbar.color = color;
      this.snackbar.show = true;
    }
  }
};
</script>

<style>
body {
  font-family: "Cairo", sans-serif;
  direction: rtl;
}
</style>
