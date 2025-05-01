<template>
  <v-container fluid class="login-page">
    <v-row class="fill-height">
      <v-col cols="12" md="6" class="form-section">
        <v-container>
          <v-row justify="center">
            <v-col cols="12" md="8" class="text-center">
              <div v-if="errorMessage" class="error-alert">
                {{ errorMessage }}
              </div>
              <h1 class="title mb-3">تسجيل الدخول</h1>
              <p class="first-p">
                ليس لديك حساب بعد؟
                <router-link to="/Signup" class="highlighted-link">
                  سجل الآن!
                </router-link>
              </p>
              <p class="second-p mb-5">من خلال ملء النموذج أدناه</p>
            </v-col>
          </v-row>

          <v-row justify="center">
            <v-col cols="12" md="8">
              <v-form ref="form" v-model="valid">
                <p class="third-p">
                  إيميل
                  <v-icon color="red">*</v-icon>
                </p>
                <v-text-field
                  v-model="email"
                  label="إيميل"
                  placeholder="أكتب بريدك الإلكتروني"
                  :solo="true"
                  :rules="[rules.required, rules.email]"
                />
                <p class="forth-p">
                  كلمة السر
                  <v-icon color="red">*</v-icon>
                </p>
                <v-text-field
                  v-model="password"
                  label="كلمة السر"
                  placeholder="أكتب كلمة السر"
                  solo
                  :rules="[rules.required]"
                  :type="showPassword ? 'text' : 'password'"
                  append-icon="mdi-eye"
                  @click:append="togglePasswordVisibility"
                />
                <a href="/ForgetPass" class="forgot-password">
                  هل نسيت كلمة السر؟
                </a>
                <v-btn
                  color="orange"
                  block
                  large
                  outlined
                  class="mt-4 button"
                  @click="login"
                  :loading="loading"
                >
                  تسجيل دخول
                </v-btn>
                
                <div class="admin-login-link mt-4 text-center">
                  <router-link to="/admin/login" class="admin-link">
                    تسجيل دخول المسؤول (Admin)
                  </router-link>
                </div>
              </v-form>
            </v-col>
          </v-row>
        </v-container>
      </v-col>
      <v-col cols="12" md="6" class="image-section d-none d-md-flex">
        <v-img
          src="/photo/login/logingirl.jpg"
          class="image fill-height"
          contain
        ></v-img>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { globalState } from "../state";
import api from "@/services/api";

export default {
  data() {
    return {
      valid: false,
      email: "",
      password: "",
      showPassword: false,
      loading: false,
      errorMessage: null,
      rules: {
        required: (value) => !!value || "هذا الحقل مطلوب",
        counter: (value) => value.length <= 20 || "20 حرف على الأكثر",
        email: (value) => {
          const pattern =
            /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
          return pattern.test(value) || "إيميل غير صحيح";
        },
      },
    };
  },
  methods: {
    async login() {
      if (this.$refs.form.validate()) {
        this.loading = true;
        this.errorMessage = null;

        try {
          const response = await api.post("login", {
            email: this.email,
            password: this.password,
          });

          if (response.data && response.data.token) {
            // Save the token in localStorage
            localStorage.setItem("token", response.data.token);

            // Update global state
            if (localStorage.getItem("token")) {
              await globalState.fetchUserProfile();
            }

            // Redirect to home page
            this.$router.push("/");
          }
        } catch (error) {
          console.error("Login error:", error);
          
          if (error.response && error.response.status === 403) {
            if (error.response.data && error.response.data.redirectTo === '/admin/login') {
              // This is an admin user trying to log in through the regular login
              this.errorMessage = error.response.data.message || "الرجاء استخدام صفحة تسجيل دخول الأدمن";
              
              // Provide a direct link option
              if (confirm("هل تريد الانتقال إلى صفحة تسجيل دخول الأدمن؟")) {
                this.$router.push('/admin/login');
              }
            } else if (error.response.data && error.response.data.status === 'inactive_account') {
              // This is a deactivated account
              this.errorMessage = error.response.data.message || "تم تعطيل حسابك. يرجى التواصل مع الدعم الفني للمساعدة.";
            } else {
              this.errorMessage = error.response.data.message || "حدث خطأ في عملية تسجيل الدخول.";
            }
          } else if (error.response && error.response.data && error.response.data.message) {
            this.errorMessage = error.response.data.message;
          } else {
            this.errorMessage =
              "حدث خطأ أثناء تسجيل الدخول. يرجى المحاولة مرة أخرى.";
          }
        } finally {
          this.loading = false;
        }
      } else {
        this.errorMessage = "الرجاء ملء جميع الحقول المطلوبة.";
      }
    },
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },
  },
};
</script>

<style>
.image {
  max-width: 100%;
  height: fit-content !important;
}

.login-page {
  min-height: 100vh;
  padding: 0 !important;
  background-color: #fafafa !important;
}

.image-section {
  padding: 0;
}

.form-section {
  display: flex;
  flex-direction: column;
  justify-content: center;
  text-align: center;
  background-color: #fafafa;
  padding-top: 70px !important;
}

.title {
  font-weight: bold !important;
  color: #34495e !important;
  font-size: xx-large !important;
}

.first-p {
  font-size: x-large;
  color: #34495e;
}

.highlighted-link {
  color: #ffa726 !important;
  font-weight: bold;
  text-decoration: none;
}

.second-p {
  font-size: larger;
  font-weight: bold;
  color: #ffa726;
}

.third-p,
.forth-p {
  justify-self: right;
  font-size: larger;
  color: #34495e;
  font-family: "ExpoLight" !important;
}

.v-text-field.v-text-field--solo:not(.v-text-field--solo-flat)
  > .v-input__control
  > .v-input__slot {
  box-shadow: none !important;
  border: 1px solid #ccc;
}

.forgot-password {
  display: block;
  /* margin-top: 8px; */
  font-size: larger;
  font-weight: bold;
  color: #ffa726 !important;
  text-decoration: none;
}

.fill-height {
  height: fit-content;
}

.button {
  font-size: larger !important;
  font-weight: bold !important;
  border: 2px solid !important;
}

.admin-login-link {
  margin-top: 30px;
}

.admin-link {
  color: #1976d2 !important;
  text-decoration: none;
  font-size: 14px;
  opacity: 0.8;
  transition: opacity 0.3s;
}

.admin-link:hover {
  opacity: 1;
  text-decoration: underline;
}

.error-alert {
  background-color: #ff5252;
  color: white;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 15px;
  text-align: center;
}
</style>
