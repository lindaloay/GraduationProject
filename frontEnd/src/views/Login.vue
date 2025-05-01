<script>
import { globalState } from "../state";
import api from "@/services/api";
import { authService } from "@/services/auth";

export default {
  data() {
    return {
      email: "",
      password: "",
      loading: false,
      errorMessage: null,
    };
  },
  methods: {
    async login() {
      this.loading = true;
      this.errorMessage = null;

      try {
        const response = await api.post("login", {
          email: this.email,
          password: this.password,
        });

        if (response.data && response.data.success) {
          // Store the token
          authService.setToken(response.data.token, response.data.expires_in);

          // Update global state
          if (localStorage.getItem("token")) {
      await globalState.fetchUserProfile();
        }

          // Redirect based on user type
          if (response.data.user.role === "business") {
            this.$router.push("/business/dashboard");
          } else {
            this.$router.push("/");
          }
        }
      } catch (error) {
        console.error("Login error:", error);
        if (error.response && error.response.data) {
          this.errorMessage =
            error.response.data.message || "حدث خطأ أثناء تسجيل الدخول";
        } else {
          this.errorMessage =
            "حدث خطأ أثناء تسجيل الدخول. يرجى المحاولة مرة أخرى.";
        }
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
