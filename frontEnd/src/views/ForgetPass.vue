<template>
  <v-container fluid class="login-page">
    <v-row class="fill-height">
      <v-col cols="12" md="6" class="form-section">
        <v-container>
          <v-row justify="center">
            <v-col cols="12" md="8" class="text-center">
              <h1 class="title mb-3" style="font-size: xx-large !important">
                تغيير كلمة السر
              </h1>
            </v-col>
          </v-row>

          <v-row justify="center">
            <v-col cols="12" md="8">
              <v-form ref="form" v-model="valid">
                <p class="pargraph-name">
                  البريد الإلكتروني الخاص بحسابك
                  <v-icon color="red">*</v-icon>
                </p>
                <v-text-field
                  label="البريد الإلكتروني"
                  placeholder="أدخل بريدك الإلكتروني"
                  v-model="email"
                  :rules="[rules.required, rules.email]"
                  solo
                ></v-text-field>
                <p class="pargraph-name">
                  كلمة السر الجديدة
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
                  تأكيد كلمة السر الجديدة
                  <v-icon color="red">*</v-icon>
                </p>
                <v-text-field
                  v-model="confirmPassword"
                  label="تأكيد كلمة السر"
                  placeholder="أكتب كلمة السر مرة أخرى"
                  solo
                  :rules="[rules.required, rules.matchPassword]"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  append-icon="mdi-eye"
                  @click:append="toggleConfirmPasswordVisibility"
                />
                <v-row>
                  <v-btn
                    color="orange"
                    large
                    outlined
                    class="mt-4 mr-3 sign-button forget-pass"
                    @click="validateForm"
                  >
                    تسجيل دخول
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
export default {
  data() {
    return {
      valid: false,
      street: "",
      password: "",
      confirmPassword: "",
      showPassword: false,
      showConfirmPassword: false,
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
    validate() {
      if (this.$refs.form.validate()) {
        alert("Form is valid!");
      } else {
        alert("Please fill all required fields.");
      }
    },
    validateForm() {
      if (this.$refs.form.validate()) {
        this.$router.push("/");
      } else {
        alert("الرجاء ملء جميع الحقول المطلوبة.");
      }
    },
    validateForm1() {
      if (this.$refs.form.validate()) {
        this.$router.push("/Business1");
      } else {
        alert("الرجاء ملء جميع الحقول المطلوبة.");
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
.forget-pass {
  width: 95%;
}
</style>
