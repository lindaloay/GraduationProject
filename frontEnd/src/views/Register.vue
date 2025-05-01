<script>
import { globalState } from "../state";
import api from "@/services/api";
import { authService } from "@/services/auth";

export default {
  data() {
    return {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      accountType: "personal",
      loading: false,
      errorMessage: null,
    };
  },
  methods: {
    async register() {
      this.loading = true;
      this.errorMessage = null;

      try {
        const response = await api.post("register", {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
          account_type: this.accountType,
        });

        if (response.data && response.data.success) {
          // Store the token
          authService.setToken(response.data.token, response.data.expires_in);

          // Update global state
          if (localStorage.getItem("token")) {
      await globalState.fetchUserProfile();
        }

          // Redirect based on account type
          if (this.accountType === "business") {
            this.$router.push("/Business1");
          } else {
            this.$router.push("/");
          }
        }
      } catch (error) {
        console.error("Registration error:", error);
        if (error.response && error.response.data) {
          if (error.response.data.errors) {
            const errors = error.response.data.errors;
            const errorMessages = Object.keys(errors).map((key) =>
              errors[key].join(", ")
            );
            this.errorMessage = errorMessages.join(". ");
          } else if (error.response.data.message) {
            this.errorMessage = error.response.data.message;
          }
        } else {
          this.errorMessage = "حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى.";
        }
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
