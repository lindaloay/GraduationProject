<template>
  <v-container fluid class="login-page">
    <v-row class="fill-height">
      <v-col cols="12" md="6" class="form-section">
        <v-container>
          <v-row justify="center">
            <v-col cols="12" md="8" class="text-center">
              <h1 class="title mb-3" style="font-size: xx-large !important">
                التسجيل
              </h1>
              <p class="first-p">
                لديك حساب بالفعل؟
                <a href="/Login" class="highlighted-link">تسجيل الدخول</a>
              </p>
              <v-row>
                <v-divider class="mt-3 ml-3"></v-divider>
                <p class="second-p">أو</p>
                <v-divider class="mt-3 mr-3"></v-divider>
              </v-row>
            </v-col>
          </v-row>

          <v-row justify="center">
            <v-col cols="12" md="8">
              <v-alert v-if="errorMessage" type="error" dismissible>
                {{ errorMessage }}
              </v-alert>

              <!-- Enhanced Account type selection -->
              <div class="account-selection-container mb-5">
                <h3 class="account-selection-title mb-3">اختر نوع الحساب</h3>

                <v-row>
                  <v-col cols="12" sm="6">
                    <v-card
                      class="account-card pa-4"
                      :class="{ 'active-card': accountType === 'normal' }"
                      @click="accountType = 'normal'"
                      hover
                      elevation="2"
                    >
                      <v-card-title class="account-card-title justify-center">
                        <v-icon large color="orange" class="mr-2"
                          >mdi-account</v-icon
                        >
                        حساب شخصي
                      </v-card-title>
                      <v-card-text class="text-center">
                        <p class="account-description">
                          مناسب للمستخدمين الذين يبحثون عن خدمات الشركات
                        </p>
                      </v-card-text>
                    </v-card>
                  </v-col>

                  <v-col cols="12" sm="6">
                    <v-card
                      class="account-card pa-4"
                      :class="{ 'active-card': accountType === 'business' }"
                      @click="accountType = 'business'"
                      hover
                      elevation="2"
                    >
                      <v-card-title class="account-card-title justify-center">
                        <v-icon large color="orange" class="mr-2"
                          >mdi-briefcase</v-icon
                        >
                        حساب تجاري
                      </v-card-title>
                      <v-card-text class="text-center">
                        <p class="account-description">
                          مناسب لأصحاب الشركات الذين يريدون عرض خدماتهم
                        </p>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>
              </div>

              <v-form ref="form" v-model="valid">
                <p class="pargraph-name">
                  الإسم بالكامل
                  <v-icon color="red">*</v-icon>
                </p>
                <v-text-field
                  label="الإسم بالكامل"
                  placeholder="أدخل إسمك بالكامل"
                  solo
                  type="text"
                  :rules="[rules.required]"
                  v-model="name"
                ></v-text-field>
                <p class="pargraph-name">
                  البريد الإلكتروني
                  <v-icon color="red">*</v-icon>
                </p>
                <v-text-field
                  label="البريد الإلكتروني"
                  placeholder="أدخل بريدك الإلكتروني"
                  v-model="email"
                  :rules="[rules.required, rules.email]"
                  solo
                  type="email"
                ></v-text-field>
                <p class="pargraph-name">
                  كلمة السر
                  <v-icon color="red">*</v-icon>
                </p>
                <v-text-field
                  v-model="password"
                  label="كلمة السر"
                  placeholder="أكتب كلمة السر"
                  solo
                  :rules="[rules.required, rules.strongPassword]"
                  :type="showPassword ? 'text' : 'password'"
                  append-icon="mdi-eye"
                  @click:append="togglePasswordVisibility"
                />
                <p class="pargraph-name">
                  تأكيد كلمة السر
                  <v-icon color="red">*</v-icon>
                </p>
                <v-text-field
                  v-model="password_confirmation"
                  label="تأكيد كلمة السر"
                  placeholder="أكتب كلمة السر مرة أخرى"
                  solo
                  :rules="[rules.required, rules.matchPassword]"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  append-icon="mdi-eye"
                  @click:append="toggleConfirmPasswordVisibility"
                />

                <!-- Business account fields - only shown if business account type is selected -->
                <template v-if="accountType === 'business'">
                  <p class="pargraph-name">رقم الهاتف</p>
                  <v-text-field
                    label="رقم الهاتف"
                    placeholder="أدخل رقم هاتفك"
                    solo
                    type="text"
                    v-model="phone"
                  ></v-text-field>
                  <p class="pargraph-name">العنوان</p>
                  <v-text-field
                    label="العنوان"
                    placeholder="أدخل عنوانك"
                    solo
                    type="text"
                    v-model="address"
                  ></v-text-field>
                  <p class="pargraph-name">المدينة</p>
                  <v-text-field
                    label="المدينة"
                    placeholder="أدخل مدينتك"
                    solo
                    type="text"
                    v-model="city"
                  ></v-text-field>
                  <p class="pargraph-name">البلد</p>
                  <v-text-field
                    label="البلد"
                    placeholder="أدخل بلدك"
                    solo
                    type="text"
                    v-model="country"
                  ></v-text-field>
                </template>

                <a href="/ForgetPass" class="forgot-password">
                  هل نسيت كلمة السر؟
                </a>
                <v-row style="justify-content: center">
                  <v-btn
                    color="orange"
                    large
                    block
                    :class="{ 'white--text': true }"
                    class="mt-4 sign-button"
                    @click="register"
                    :loading="loading"
                  >
                    إنشاء حساب
                  </v-btn>
                </v-row>
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
      accountType: "normal", // Default to normal account
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      phone: "",
      address: "",
      city: "",
      country: "",
      showPassword: false,
      showConfirmPassword: false,
      loading: false,
      errorMessage: null,
      rules: {
        required: (value) => !!value || "هذا الحقل مطلوب",
        strongPassword: (value) =>
          /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(
            value
          ) ||
          "يجب أن تحتوي كلمة السر على 8 أحرف على الأقل، وحرف كبير، وحرف صغير، ورمز خاص.",
        matchPassword: (value) =>
          value === this.password || "كلمات السر غير متطابقة.",
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
    async register() {
      if (this.$refs.form.validate()) {
        this.loading = true;
        this.errorMessage = null;
        const isBusinessAccount = this.accountType === "business";

        try {
          const userData = {
            name: this.name,
            email: this.email,
            password: this.password,
            password_confirmation: this.password_confirmation,
            is_business_owner: isBusinessAccount,
          };

          // Only add additional fields for business accounts
          if (isBusinessAccount) {
            userData.phone = this.phone || null;
            userData.address = this.address || null;
            userData.city = this.city || null;
            userData.country = this.country || null;
          }

          const response = await api.post("register", userData);

          if (response.data && response.data.token) {
            // Save the token in localStorage
            localStorage.setItem("token", response.data.token);

            // Update global state
            if (localStorage.getItem("token")) {
      await globalState.fetchUserProfile();
        }

            // Redirect based on account type
            if (isBusinessAccount) {
              this.$router.push("/Business1");
            } else {
              this.$router.push("/");
            }
          }
        } catch (error) {
          console.error("Registration error:", error);
          if (
            error.response &&
            error.response.data &&
            error.response.data.errors
          ) {
            // Format validation errors
            const errors = error.response.data.errors;
            const errorMessages = Object.keys(errors).map((key) =>
              errors[key].join(", ")
            );
            this.errorMessage = errorMessages.join(". ");
          } else {
            this.errorMessage =
              "حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى.";
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
    toggleConfirmPasswordVisibility() {
      this.showConfirmPassword = !this.showConfirmPassword;
    },
  },
};
</script>

<style>
.pargraph-name {
  justify-self: right;
  font-size: larger;
  color: #34495e;
  font-family: "ExpoLight" !important;
  margin-bottom: 0 !important;
}

.sign-button {
  font-size: larger !important;
  font-weight: bold !important;
  border: 2px solid !important;
}

.account-type-toggle {
  width: 100%;
  margin-bottom: 20px;
}

.account-type-btn {
  flex: 1;
  height: 48px;
  font-size: 16px !important;
}

.v-btn-toggle .v-btn.v-item--active {
  background-color: #f39c12 !important;
  color: white !important;
}

/* Account Type Selection Styles */
.account-selection-container {
  margin-top: 20px;
}

.account-selection-title {
  text-align: center;
  font-size: 1.5rem;
  font-weight: bold;
  color: #34495e;
  margin-bottom: 20px;
}

.account-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  height: 100%;
}

.account-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.active-card {
  border: 2px solid #f39c12 !important;
  background-color: #fff9e6 !important;
}

.account-card-title {
  font-size: 1.3rem !important;
  font-weight: bold !important;
  color: #34495e !important;
}

.account-description {
  color: #666;
  margin-bottom: 15px;
  font-size: 1rem;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .account-selection-container .v-card {
    margin-bottom: 15px;
  }
}
</style>
