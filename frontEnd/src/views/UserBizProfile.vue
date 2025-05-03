<template>
  <v-app style="padding-top: 120px">
    <v-container class="my-5">
      <v-row justify="right">
        <v-col cols="12" md="12">
          <v-form ref="form">
            <v-row>
              <h1 class="text-right">معلومات حسابك:</h1>
            </v-row>
            <!-- User Details Section -->
            <v-row
              v-for="(field, index) in userFields"
              :key="'user-field-' + index"
              align="center"
              class="mt-4"
            >
              <v-col cols="12" md="3">
                <h2 class="text-right">{{ field.label }}</h2>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="field.value"
                  class="flex-grow-1"
                  :label="field.placeholder"
                  solo
                  :type="field.type"
                  :rules="field.rules"
                  @input="handleUserFieldChange"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row justify="center" class="mt-4">
              <v-col cols="12" md="3">
                <v-btn
                  color="teal"
                  style="color: white; width: 100px"
                  :disabled="!hasUserChanges"
                  @click="saveUserProfile"
                >
                  حفظ
                </v-btn>
              </v-col>
            </v-row>

            <!-- Business Details Section (only for business owners) -->
            <template v-if="isBusinessUser">
              <v-row class="mt-15 mb-8" align="center" justify="space-between">
                <h1 class="text-right">معلومات شركتك:</h1>
                <v-btn
                  color="teal"
                  dark
                  @click="$router.push('/Business1')"
                  class="edit-button"
                >
                  <v-icon left right>mdi-pencil</v-icon>
                  تعديل المعلومات
                </v-btn>
              </v-row>

              <!-- Main Business Card -->
              <v-card class="business-card mb-8" elevation="0">
                <v-row no-gutters>
                  <!-- Business Image -->
                  <v-col cols="12" md="4" class="business-image-container">
                    <v-img
                      :src="businessData.main_picture || '/default-business.jpg'"
                      height="100%"
                      class="business-main-image"
                      :aspect-ratio="16/9"
                      cover
                    >
                      <div class="business-image-overlay">
                        <h2 class="business-name">{{ businessData.name }}</h2>
                        <p class="business-type">{{ businessData.type }}</p>
                      </div>
                      <!-- Favorites Count Badge -->
                      <div class="favorites-badge-container">
                        <v-chip
                          v-if="favoritesCount > 0"
                          color="#FF5A58"
                          text-color="white"
                          class="favorites-badge"
                        >
                          <v-icon left size="24" right side="10">mdi-heart</v-icon>
                          {{ favoritesCount }} إعجاب
                        </v-chip>
                      </div>
                    </v-img>
                  </v-col>

                  <!-- Business Info -->
                  <v-col cols="12" md="8" class="business-info-container">
                    <v-card-text class="business-info">
                      <div class="info-section">
                        <h3 class="section-title">معلومات الاتصال</h3>
                        <div class="info-grid">
                          <div class="info-item">
                            <v-icon color="teal" class="mr-2">mdi-email</v-icon>
                            <span>{{ businessData.email }}</span>
                          </div>
                          <div class="info-item">
                            <v-icon color="teal" class="mr-2">mdi-phone</v-icon>
                            <span>{{ businessData.phone }}</span>
                          </div>
                          <div class="info-item">
                            <v-icon color="teal" class="mr-2">mdi-web</v-icon>
                            <span>{{ businessData.website }}</span>
                          </div>
                          <div class="info-item">
                            <v-icon color="teal" class="mr-2">mdi-clock-outline</v-icon>
                            <span>{{
                             

                             typeof businessData.working_times === 'string' 
              ? JSON.parse(businessData.working_times) 
              : businessData.working_times
                             }}</span>
                          </div>
                        </div>
                      </div>

                      <div class="info-section">
                        <h3 class="section-title">الموقع</h3>
                        <div class="info-grid">
                          <div class="info-item">
                            <v-icon color="teal" class="mr-2">mdi-map-marker</v-icon>
                            <span>{{ userFields.find(f => f.field === 'address')?.value }}, {{ userFields.find(f => f.field === 'city')?.value }}, {{ userFields.find(f => f.field === 'country')?.value }}</span>
                          </div>
                          <div class="info-item">
                            <v-icon color="teal" class="mr-2">mdi-crosshairs-gps</v-icon>
                            <span>خط العرض: {{ businessData.latitude }}, خط الطول: {{ businessData.longitude }}</span>
                          </div>
                        </div>
                      </div>

                      <div class="info-section">
                        <h3 class="section-title">المرافق</h3>
                        <div class="amenities-grid">
                          <div class="amenity-item" v-if="businessData.has_wifi">
                            <v-icon color="teal" class="mr-2">mdi-wifi</v-icon>
                            <span>واي فاي</span>
                          </div>
                          <div class="amenity-item" v-if="businessData.has_online_booking">
                            <v-icon color="teal" class="mr-2">mdi-calendar-check</v-icon>
                            <span>حجز إلكتروني</span>
                          </div>
                          <div class="amenity-item" v-if="businessData.near_transportation">
                            <v-icon color="teal" class="mr-2">mdi-bus</v-icon>
                            <span>قريب من المواصلات</span>
                          </div>
                          <div class="amenity-item" v-if="businessData.has_parking">
                            <v-icon color="teal" class="mr-2">mdi-parking</v-icon>
                            <span>موقف سيارات</span>
                          </div>
                        </div>
                      </div>

                      <div class="info-section">
                        <h3 class="section-title">روابط التواصل الاجتماعي</h3>
                        <div class="social-links">
                          <a v-if="businessData.facebook_link" :href="businessData.facebook_link" target="_blank" class="social-link">
                            <v-icon color="blue" class="mr-2">mdi-facebook</v-icon>
                            <span>فيسبوك</span>
                          </a>
                          <a v-if="businessData.instagram_link" :href="businessData.instagram_link" target="_blank" class="social-link">
                            <v-icon color="pink" class="mr-2">mdi-instagram</v-icon>
                            <span>انستغرام</span>
                          </a>
                          <a v-if="businessData.twitter_link" :href="businessData.twitter_link" target="_blank" class="social-link">
                            <v-icon color="light-blue" class="mr-2">mdi-twitter</v-icon>
                            <span>تويتر</span>
                          </a>
                        </div>
                      </div>
                    </v-card-text>
                  </v-col>
                </v-row>
              </v-card>

              <!-- Gallery Section -->
              <v-card class="gallery-card mb-8" elevation="0">
                <v-card-title class="gallery-title">
                  <v-icon color="teal" class="mr-2">mdi-image-multiple</v-icon>
                  معرض الصور
                </v-card-title>
                <v-card-text>
                  <v-row v-if="businessData.gallery_pictures && businessData.gallery_pictures.length > 0">
                    <v-col
                      v-for="(image, index) in businessData.gallery_pictures"
                      :key="index"
                      cols="12"
                      sm="6"
                      md="4"
                      lg="3"
                    >
                      <v-img
                        :src="image"
                        height="200"
                        class="gallery-image"
                      ></v-img>
                    </v-col>
                  </v-row>
                  <v-row v-else justify="center" align="center" style="min-height: 150px">
                    <v-col cols="12" class="text-center">
                      <v-icon size="64" color="grey lighten-1">mdi-image-outline</v-icon>
                      <p class="mt-3 grey--text">لا توجد صور في المعرض حالياً</p>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>

              <!-- Feedbacks Section -->
              <v-card class="feedback-card mb-8" elevation="0" v-if="feedbacks.length > 0">
                <v-card-title class="feedback-title">
                  <v-icon color="teal" class="mr-2">mdi-star</v-icon>
                  تقييمات العملاء ({{ feedbacks.length }})
                </v-card-title>
                <v-card-text>
                  <div class="feedback-cards">
                    <v-card 
                      v-for="(feedback, index) in feedbacks" 
                      :key="index" 
                      class="feedback-item"
                      :class="{ 'highlighted': isNewFeedback(feedback) }"
                    >
                      <div class="feedback-header">
                        <div class="user-info">
                          <v-avatar size="40" class="user-avatar">
                            <v-icon color="primary">mdi-account</v-icon>
                          </v-avatar>
                          <div class="user-details">
                            <div class="user-name">{{ feedback.user.name }}</div>
                            <div class="feedback-date">{{ getRelativeTime(feedback.created_at) }}</div>
                          </div>
                        </div>
                        <div class="feedback-rating">
                          <v-rating
                            :value="feedback.rating"
                            color="amber"
                            background-color="grey lighten-3"
                            dense
                            readonly
                            size="14"
                          ></v-rating>
                          <span class="rating-value">{{ feedback.rating }}</span>
                        </div>
                      </div>
                      
                      <div class="feedback-body">
                        <p class="feedback-text">{{ feedback.comment }}</p>
                      </div>
                      
                      <!-- Aspect Ratings -->
                      <div v-if="feedback.aspect_ratings && feedback.aspect_ratings.length > 0" class="feedback-aspect-ratings">
                        <div 
                          v-for="aspectRating in feedback.aspect_ratings" 
                          :key="aspectRating.id"
                          class="feedback-aspect-rating-item"
                          :class="{ 'user-created-aspect': aspectRating.aspect.is_user_created }"
                        >
                          <div class="feedback-aspect-name">
                            {{ aspectRating.aspect.name }}
                            <v-chip x-small v-if="aspectRating.aspect.is_user_created" class="user-created-chip">مخصص</v-chip>
                          </div>
                          <div class="feedback-aspect-rating">
                            <v-rating
                              :value="aspectRating.rating"
                              color="amber"
                              background-color="grey lighten-3"
                              readonly
                              dense
                              small
                            ></v-rating>
                            <span class="aspect-rating-value">{{ aspectRating.rating }}</span>
                          </div>
                        </div>
                      </div>
                      
                      <div v-if="isNewFeedback(feedback)" class="new-badge-container">
                        <v-chip small class="new-badge">
                          جديد
                        </v-chip>
                      </div>
                    </v-card>
                  </div>
                </v-card-text>
              </v-card>
            </template>
          </v-form>
        </v-col>
      </v-row>
    </v-container>
  </v-app>
</template>

<script>
import api from "@/services/api";
import { globalState } from "@/state";

export default {
  components: {
   
  },
  data() {
    return {
      isBusinessUser: false,
      hasUserChanges: false,
      originalUserData: {},
      email: "noor@gmail.com",
      fullName: "نور علي",
      preview: null,
      files: [],
      feedbacks: [],
      favoritesCount: 0,
      likedCards: [
        {
          image: "photo/Companies/abjd.jpg",
          title: "شركة أبجد",
          rating: 2,
          location: "الأردن",
          description:
            "البقالة والمواد التموينية · الأطعمة والوجبات الجاهزة · الأطعمة الصحية ومكملات الغذائية...",
        },
        {
          image: "photo/Hotel/fairmont.jpg",
          title: "فندق Fairmont",
          rating: "1.5",
          location: "مصر",
          description:
            "يتمتع فندق Fairmont Amman الفاخر والمصنف 5 نجوم بموقع مثالي في منطقة الدوار الخامس",
        },
      ],
      userFields: [
        {
          label: "الاسم الكامل",
          placeholder: "أدخل اسمك الكامل",
          value: "",
          type: "text",
          rules: [
            v => !!v || 'الاسم الكامل مطلوب',
            v => (v && v.length >= 3) || 'يجب أن يكون الاسم 3 أحرف على الأقل'
          ],
          field: "name"
        },
        {
          label: "البريد الإلكتروني",
          placeholder: "أدخل بريدك الإلكتروني",
          value: "",
          type: "email",
          rules: [
            v => !!v || 'البريد الإلكتروني مطلوب',
            v => /.+@.+\..+/.test(v) || 'البريد الإلكتروني غير صالح'
          ],
          field: "email"
        },
        {
          label: "رقم الهاتف",
          placeholder: "أدخل رقم هاتفك",
          value: "",
          type: "tel",
          rules: [
            v => !!v || 'رقم الهاتف مطلوب',
            v => /^[0-9+-\s()]{8,20}$/.test(v) || 'رقم الهاتف غير صالح'
          ],
          field: "phone"
        },
        {
          label: "العنوان",
          placeholder: "أدخل عنوانك",
          value: "",
          type: "text",
          rules: [
            v => !!v || 'العنوان مطلوب',
            v => (v && v.length >= 5) || 'العنوان قصير جداً'
          ],
          field: "address"
        },
        {
          label: "المدينة",
          placeholder: "أدخل مدينتك",
          value: "",
          type: "text",
          rules: [
            v => !!v || 'المدينة مطلوبة',
            v => (v && v.length >= 2) || 'اسم المدينة قصير جداً'
          ],
          field: "city"
        },
        {
          label: "البلد",
          placeholder: "أدخل بلدك",
          value: "",
          type: "text",
          rules: [
            v => !!v || 'البلد مطلوب',
            v => (v && v.length >= 2) || 'اسم البلد قصير جداً'
          ],
          field: "country"
        }
      ],
      businessData: {
        name: '',
        type: '',
        description: '',
        email: '',
        phone: '',
        website: '',
        working_times: '',
        street: '',
        city: '',
        latitude: '',
        longitude: '',
        facebook_link: '',
        instagram_link: '',
        twitter_link: '',
        has_wifi: false,
        has_online_booking: false,
        near_transportation: false,
        has_parking: false,
        main_picture: '',
        gallery_pictures: []
      }
    };
  },
  async created() {
    try {
      // Fetch user profile data
      const response = await api.get('/user/profile');
      console.log('User Profile Response:', response.data);
      
      if (response.data.success === true) {
        const userData = response.data.user;
        console.log('User Data:', userData);
        
        // Store original data for comparison
        this.originalUserData = { ...userData };
        
        // Update user fields with the received data
        this.userFields = this.userFields.map(field => ({
          ...field,
          value: userData[field.field] || ''
        }));
      }
    } catch (error) {
      console.error('Error fetching user profile:', error);
      this.$store.dispatch('showError', 'حدث خطأ أثناء جلب بيانات الملف الشخصي');
    }

    // Check if user is business owner and fetch business data if needed
    this.isBusinessUser = globalState.user?.is_business_owner === 1;
    if (this.isBusinessUser) {
      await this.fetchBusinessData();
      await this.fetchBusinessFeedbacks();
    }
  },
  methods: {
    handleUserFieldChange() {
      // Check if any field has changed from its original value
      this.hasUserChanges = this.userFields.some(field => {
        return field.value !== (this.originalUserData[field.field] || '');
      });
    },
    async saveUserProfile() {
      // Validate the form
      const isValid = await this.$refs.form.validate();
      console.log('Form validation result:', isValid);

      try {
        // Prepare the data to send
        const userData = {};
        this.userFields.forEach(field => {
          userData[field.field] = field.value;
        });

        const response = await api.post('/user/update', userData);
        
        if (response.data.success === true) {
          // Show success message
          alert('تم تحديث الملف الشخصي بنجاح');
          
          // Update original data
          this.originalUserData = { ...userData };
          this.hasUserChanges = false;
          
          // Update the user data in the global state
          if (globalState.user) {
            globalState.user = {
              ...globalState.user,
              ...userData
            };
          }
        } else {
          // Handle API error response
          alert(response.data.message || 'حدث خطأ أثناء تحديث الملف الشخصي');
        }
      } catch (error) {
        console.error('Error saving user profile:', error);
        
        // Handle validation errors
        if (error.response && error.response.status === 422) {
          const errors = error.response.data.errors;
          let errorMessage = 'الرجاء تصحيح الأخطاء التالية:\n';
          Object.keys(errors).forEach(key => {
            errorMessage += `- ${errors[key][0]}\n`;
          });
          alert(errorMessage);
        } else {
          // Handle other errors
          alert('حدث خطأ أثناء تحديث الملف الشخصي');
        }
      }
    },
    async fetchUserData() {
      try {
        const response = await api.get('/user/profile');
        console.log('User Profile Response:', response.data);
        
        if (response.data.success === true) {
          const userData = response.data.user;
          console.log('User Data:', userData);
          
          // Store original data for comparison
          this.originalUserData = { ...userData };
          
          // Update user fields with the received data
          this.userFields = this.userFields.map(field => ({
            ...field,
            value: userData[field.field] || ''
          }));

          // Update the user data in the global state
          if (globalState.user) {
            globalState.user = {
              ...globalState.user,
              ...userData
            };
          }
        } else {
          this.$store.dispatch('showError', response.data.message || 'حدث خطأ أثناء جلب بيانات الملف الشخصي');
        }
      } catch (error) {
        console.error('Error fetching user data:', error);
        this.$store.dispatch('showError', 'حدث خطأ أثناء جلب بيانات الملف الشخصي');
      }
    },
    async fetchBusinessData() {
      try {
        const response = await api.get('/business/details');
        if (response.data.status === 'success') {
          const businessData = response.data.data;
          console.log('Business Data:', businessData);

          const mainPicture = businessData.main_picture_url;
          const galleryPictures = businessData.gallery_picture_urls || [];

          this.businessData = {
            ...this.businessData,
            ...businessData,
            main_picture: mainPicture,
            gallery_pictures: galleryPictures
          };

          console.log('Business Data after processing:', this.businessData);
          
          // Fetch favorites count after getting business data
          await this.fetchFavoritesCount();
        }
      } catch (error) {
        console.error('Error fetching business data:', error);
        alert('حدث خطأ أثناء جلب بيانات الشركة');
      }
    },
    async fetchBusinessFeedbacks() {
      try {
        // Check if business ID is available
        if (!this.businessData || !this.businessData.id) {
          console.log('No business ID available to fetch feedbacks');
          return;
        }
        
        const response = await api.get(`/businesses/${this.businessData.id}/feedbacks`);
        if (response.data.status === 'success') {
          this.feedbacks = response.data.feedbacks;
          console.log('Feedbacks loaded:', this.feedbacks);
        }
      } catch (error) {
        console.error('Error fetching business feedbacks:', error);
      }
    },
    
    isNewFeedback(feedback) {
      const feedbackDate = new Date(feedback.created_at);
      const now = new Date();
      const diffInHours = (now - feedbackDate) / (1000 * 60 * 60);
      return diffInHours < 24; // Consider feedbacks from the last 24 hours as new
    },
    
    getRelativeTime(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);
      
      // Arabic time units
      const timeUnits = {
        year: 'سنة',
        years: 'سنوات',
        month: 'شهر',
        months: 'أشهر',
        week: 'أسبوع',
        weeks: 'أسابيع',
        day: 'يوم',
        days: 'أيام',
        hour: 'ساعة',
        hours: 'ساعات',
        minute: 'دقيقة',
        minutes: 'دقائق',
        second: 'ثانية',
        seconds: 'ثواني'
      };
      
      if (diffInSeconds < 60) {
        return 'منذ لحظات';
      } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `منذ ${minutes} ${minutes === 1 ? timeUnits.minute : timeUnits.minutes}`;
      } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `منذ ${hours} ${hours === 1 ? timeUnits.hour : timeUnits.hours}`;
      } else if (diffInSeconds < 604800) {
        const days = Math.floor(diffInSeconds / 86400);
        return `منذ ${days} ${days === 1 ? timeUnits.day : timeUnits.days}`;
      } else if (diffInSeconds < 2592000) {
        const weeks = Math.floor(diffInSeconds / 604800);
        return `منذ ${weeks} ${weeks === 1 ? timeUnits.week : timeUnits.weeks}`;
      } else if (diffInSeconds < 31536000) {
        const months = Math.floor(diffInSeconds / 2592000);
        return `منذ ${months} ${months === 1 ? timeUnits.month : timeUnits.months}`;
      } else {
        const years = Math.floor(diffInSeconds / 31536000);
        return `منذ ${years} ${years === 1 ? timeUnits.year : timeUnits.years}`;
      }
    },
    async fetchFavoritesCount() {
      try {
        // Check if business ID is available
        if (!this.businessData || !this.businessData.id) {
          console.log('No business ID available to fetch favorites count');
          return;
        }
        
        const response = await api.get(`/businesses/${this.businessData.id}/favorites/count`);
        if (response.data.status === 'success') {
          this.favoritesCount = response.data.count;
          console.log('Favorites count:', this.favoritesCount);
        }
      } catch (error) {
        console.error('Error fetching favorites count:', error);
      }
    },
  }
};
</script>

<style>
.SocialFields {
  color: cornflowerblue;
}

.product-image1 {
  height: 150px;
  width: 200px;
  border-radius: 5px;
}

.file-upload {
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.upload-label {
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.icon-container {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 120px;
  height: 120px;
  border: 2px solid #ccc;
  border-radius: 8px;
}

.file-input {
  display: none;
}

.add-photo-column {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 8px;
}

.plusButton {
  border-radius: 50% !important;
  height: 70px !important;
  width: 70px !important;
  color: white !important;
  background-color: #49bf5a !important;
}

.v-card {
  border-radius: 8px;
}
.v-card-title {
  font-size: 1.2rem;
  font-weight: 500;
  padding: 16px;
  border-bottom: 1px solid #e0e0e0;
}
.v-card-text {
  padding: 16px;
}

/* Business Card Styles */
.business-card {
  border-radius: 16px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  margin-top: 24px;
}

.business-image-container {
  position: relative;
  min-height: 400px;
}

.business-main-image {
  border-radius: 16px 0 0 16px;
}

.business-image-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 20px;
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
  color: white;
}

.business-name {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 8px;
}

.business-type {
  font-size: 16px;
  opacity: 0.9;
}

.business-info-container {
  padding: 24px;
}

.info-section {
  margin-bottom: 32px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.info-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 2px solid #e0e0e0;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.info-item {
  display: flex;
  align-items: center;
  padding: 8px;
  background: #f8f9fa;
  border-radius: 8px;
}

.amenities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
}

.amenity-item {
  display: flex;
  align-items: center;
  padding: 8px;
  background: #f8f9fa;
  border-radius: 8px;
}

.social-links {
  display: flex;
  gap: 16px;
}

.social-link {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: inherit;
  padding: 8px 16px;
  background: #f8f9fa;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.social-link:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Gallery Styles */
.gallery-card {
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.gallery-title {
  font-size: 20px;
  font-weight: 600;
  color: #2c3e50;
  padding: 20px;
}

.gallery-image {
  border-radius: 8px;
  transition: transform 0.3s ease;
}

.gallery-image:hover {
  transform: scale(1.02);
}

.edit-button {
  margin-left: 16px;
  text-transform: none;
  letter-spacing: 0;
  font-weight: 500;
  z-index: 1;
}

.edit-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

/* Feedback Styles */
.feedback-card {
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.feedback-title {
  font-size: 20px;
  font-weight: 600;
  color: #2c3e50;
  padding: 20px;
}

.feedback-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  padding: 10px;
}

.feedback-item {
  position: relative;
  border-radius: 12px;
  padding: 16px;
  padding-top: 16px;
  background-color: #f9f9f9;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.feedback-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.feedback-item.highlighted {
  border: 1px solid #4caf50;
  background-color: #f1f8e9;
}

.feedback-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
  margin-top: 24px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  font-size: 1rem;
  color: #2c3e50;
}

.feedback-date {
  font-size: 0.8rem;
  color: #7f8c8d;
  margin-top: 2px;
}

.feedback-rating {
  display: flex;
  align-items: center;
  gap: 8px;
}

.rating-value {
  font-weight: 600;
  color: #f57c00;
}

.feedback-body {
  margin-top: 10px;
}

.feedback-text {
  font-size: 0.95rem;
  line-height: 1.5;
  color: #34495e;
  white-space: pre-wrap;
}

.new-badge-container {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 1; /* Ensure it's above other content */
}

.new-badge {
  background: linear-gradient(135deg, #4caf50, #2e7d32) !important;
  color: white;
  font-weight: 500;
  font-size: 0.8rem;
  height: 24px;
  padding: 0 12px;
  min-width: 80px;
  display: flex;
  justify-content: center;
  align-items: center;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(76, 175, 80, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
  }
}

/* Favorites Badge Styles */
.favorites-badge-container {
  position: absolute;
  top: 20px;
  left: 20px;
  z-index: 1;
}

.favorites-badge {
  font-weight: bold;
  font-size: 0.9rem;
  padding: 0 10px;
  box-shadow: 0 2px 12px rgba(255, 90, 88, 0.6);
  animation: pulse-light 2s infinite;
}

@keyframes pulse-light {
  0% {
    box-shadow: 0 0 0 0 rgba(255, 90, 88, 0.7);
  }
  70% {
    box-shadow: 0 0 0 8px rgba(255, 90, 88, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(255, 90, 88, 0);
  }
}

/* Feedback Aspect Ratings */
.feedback-aspect-ratings {
  margin-top: 12px;
  padding-top: 8px;
  border-top: 1px dashed rgba(0, 0, 0, 0.1);
}

.feedback-aspect-rating-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 4px;
  padding: 4px 0;
}

.feedback-aspect-name {
  font-size: 0.9rem;
  color: #616161;
  display: flex;
  align-items: center;
}

.feedback-aspect-rating {
  display: flex;
  align-items: center;
}

.aspect-rating-value {
  font-size: 0.9rem;
  margin-left: 8px;
  color: #F57C00;
  font-weight: 500;
}

/* User-created aspect styling */
.user-created-aspect {
  background-color: rgba(255, 248, 225, 0.3);
  border-radius: 6px;
  padding: 4px 8px;
}

.user-created-chip {
  margin-right: 8px;
  background-color: #FBC02D !important;
  color: white;
  font-size: 10px;
  height: 16px !important;
}

/* Add responsive styles for aspect ratings on mobile */
@media (max-width: 600px) {
  .feedback-aspect-rating-item {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .feedback-aspect-rating {
    margin-top: 4px;
    align-self: flex-start;
  }
  
  .feedback-aspect-name {
    margin-bottom: 4px;
  }
}
</style>
