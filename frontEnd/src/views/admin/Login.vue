<template>
  <v-app>
    <v-container fluid class="admin-login-container fill-height">
      <v-row class="fill-height" justify="center" align="center">
        <v-col cols="12" sm="8" md="5" lg="4">
          <v-card class="admin-login-card elevation-10 pa-4">
            <div class="text-center mb-6">
              <div class="admin-logo-container">
                <v-icon size="48" color="primary" class="mb-2">mdi-shield-account</v-icon>
              </div>
              <h1 class="admin-title">BizAdvisor Admin</h1>
              <p class="admin-subtitle">تسجيل دخول لوحة التحكم</p>
            </div>
            
            <v-alert v-if="error" type="error" class="mb-4" dense>
              {{ error }}
            </v-alert>
            
            <v-form ref="form" v-model="valid" @submit.prevent="login">
              <v-text-field
                v-model="email"
                label="البريد الإلكتروني"
                prepend-inner-icon="mdi-email"
                type="email"
                outlined
                :rules="[rules.required, rules.email]"
                autocomplete="email"
                dir="rtl"
                :disabled="loading"
                color="primary"
              ></v-text-field>
              
              <v-text-field
                v-model="password"
                label="كلمة المرور"
                prepend-inner-icon="mdi-lock"
                :append-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                :type="showPassword ? 'text' : 'password'"
                @click:append="showPassword = !showPassword"
                outlined
                :rules="[rules.required]"
                autocomplete="current-password"
                dir="rtl"
                :disabled="loading"
                color="primary"
              ></v-text-field>
              
              <v-btn
                color="primary"
                block
                x-large
                elevation="2"
                class="mt-6 admin-login-btn"
                :loading="loading"
                @click="login"
                :disabled="!valid"
              >
                <v-icon left>mdi-login</v-icon>
                تسجيل الدخول
              </v-btn>
            </v-form>
            
            <div class="text-center mt-4">
              <v-btn text small color="grey darken-1" @click="goToHome" class="text-none">
                <v-icon small left>mdi-home</v-icon>
                العودة للصفحة الرئيسية
              </v-btn>
            </div>
          </v-card>
          
          <div class="text-center mt-4 admin-footer">
            <p class="caption">© {{ new Date().getFullYear() }} BizAdvisor. جميع الحقوق محفوظة</p>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-app>
</template>

<script>
import api from '@/services/api';

export default {
  name: 'AdminLogin',
  computed: {
    isAdminRoute() {
      return this.$route.path.startsWith('/admin');
    }
  },
  data() {
    return {
      valid: false,
      loading: false,
      email: '',
      password: '',
      showPassword: false,
      error: null,
      rules: {
        required: v => !!v || 'هذا الحقل مطلوب',
        email: v => /.+@.+\..+/.test(v) || 'الرجاء إدخال بريد إلكتروني صحيح'
      }
    };
  },
  mounted() {
    // Clear any existing admin tokens
    localStorage.removeItem('admin_token');
    localStorage.removeItem('admin_user');
    delete api.defaults.headers.common['Authorization'];
    
    // Hide header and footer by directly targeting the components
    const header = document.querySelector('.transparent-header');
    const footerElements = document.querySelectorAll('footer');
    
    if (header) {
      console.log('Found header, hiding it');
      header.style.display = 'none';
    } else {
      console.log('Header not found');
    }
    
    footerElements.forEach(footer => {
      if (footer) footer.style.display = 'none';
    });
    
    // Add a body class to indicate admin mode
    document.body.classList.add('admin-mode');
  },
  methods: {
    async login() {
      if (!this.$refs.form.validate()) return;
      
      this.loading = true;
      this.error = null;
      
      try {
        const response = await api.post('/admin/login', {
          email: this.email,
          password: this.password
        });
        
        if (response.data.status === 'success') {
          // Show success animation
          this.$nextTick(() => {
            // Store token and user info
            localStorage.setItem('admin_token', response.data.token);
            localStorage.setItem('admin_user', JSON.stringify(response.data.user));
            
            // Set auth header for future requests
            api.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
            
            // Redirect to dashboard
            this.$router.push('/admin/dashboard');
          });
        } else {
          this.error = response.data.message || 'فشل تسجيل الدخول';
        }
      } catch (error) {
        console.error('Login error:', error);
        
        if (error.response) {
          if (error.response.status === 403) {
            if (error.response.data && error.response.data.message) {
              this.error = error.response.data.message;
            } else {
              this.error = 'غير مصرح بالدخول. مطلوب صلاحيات مدير النظام.';
            }
          } else if (error.response.status === 401) {
            this.error = 'بيانات الاعتماد غير صحيحة';
          } else {
            this.error = error.response.data?.message || 'حدث خطأ أثناء تسجيل الدخول';
          }
        } else {
          this.error = 'خطأ في الاتصال بالخادم. الرجاء المحاولة مرة أخرى.';
        }
      } finally {
        this.loading = false;
      }
    },
    goToHome() {
      // Remove admin-mode class before navigating home
      document.body.classList.remove('admin-mode');
      // Clear any display:none styles from header/footer
      const header = document.querySelector('.transparent-header');
      if (header) header.style.display = '';
      
      const footerElements = document.querySelectorAll('footer');
      footerElements.forEach(footer => {
        if (footer) footer.style.display = '';
      });
      
      this.$router.push('/');
    }
  }
};
</script>

<style scoped>
.admin-login-container {
  background: linear-gradient(135deg, #0d47a1, #42a5f5);
  min-height: 100vh;
}

.admin-login-card {
  border-radius: 8px;
  overflow: hidden;
  background: white;
}

.admin-title {
  color: #1976d2;
  font-size: 1.8rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.admin-subtitle {
  color: #607d8b;
  font-size: 1.1rem;
  margin-top: 0;
}

.admin-login-btn {
  text-transform: none;
  font-weight: 500;
  font-size: 1.1rem;
  letter-spacing: 0.5px;
}

.admin-logo-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 80px;
  height: 80px;
  margin: 0 auto 16px;
  border-radius: 50%;
  background: rgba(25, 118, 210, 0.1);
}

.admin-footer {
  color: rgba(255, 255, 255, 0.7);
}
</style>

<style>
/* Global styles for admin mode */
body.admin-mode .transparent-header,
body.admin-mode footer {
  display: none !important;
}

body.admin-mode {
  min-height: 100vh;
  background: linear-gradient(135deg, #0d47a1, #42a5f5);
}
</style> 