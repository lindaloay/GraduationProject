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
                ></v-text-field>
              </v-col>
            </v-row>

            <!-- Save Button -->
            <v-row justify="center" class="mt-4">
              <v-col cols="12" md="3">
                <v-btn
                  color="teal"
                  style="color: white; width: 100px"
                  @click="saveUserProfile"
                >
                  حفظ
                </v-btn>
              </v-col>
            </v-row>
          </v-form>
        </v-col>
      </v-row>
    </v-container>

    <v-container style="padding-top: 70px">
      <v-row>
        <v-row>
          <h1 class="text-right">الأعمال التي أعجبتك:</h1>
        </v-row>
      </v-row>

      <!-- Loading State -->
      <v-row v-if="loadingFavorites" justify="center" class="my-5">
        <v-progress-circular indeterminate color="teal"></v-progress-circular>
      </v-row>

      <!-- Empty State -->
      <v-row v-else-if="likedCards.length === 0" justify="center" class="my-5">
        <v-col cols="12" class="text-center">
          <v-icon size="64" color="grey lighten-1">mdi-heart-outline</v-icon>
          <h3 class="mt-4 grey--text text--darken-1">لم تقم بإضافة أي أعمال إلى المفضلة بعد</h3>
          <v-btn color="teal" dark class="mt-4" @click="$router.push('/')">
            تصفح الأعمال
          </v-btn>
        </v-col>
      </v-row>

      <!-- Cards Section -->
      <v-row v-else>
        <v-col
          v-for="(card, index) in likedCards"
          :key="index"
          cols="12"
          md="4"
        >
          <CompanyCard
            :title="card.title"
            :image="card.image"
            :rating="card.rating"
            :businessId="card.id"
            :location="card.location"
            :description="card.description"
            :buttonText="card.buttonText"
            :isFavorite="true"
            @update:isFavorite="handleFavoriteChange($event, card.id)"
          />
        </v-col>
      </v-row>
    </v-container>
    
    <!-- Snackbar for notifications -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
      bottom
      right
      rounded="pill"
    >
      {{ snackbar.text }}
    </v-snackbar>
  </v-app>
</template>

<script>
import CompanyCard from "../components/CompanyCard.vue";
import api from "@/services/api";
import { globalState } from "@/state";

export default {
  components: {
    CompanyCard,
  },
  data() {
    return {
      loadingFavorites: false,
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      },
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
            v => !v || /^[0-9+-\s()]{8,20}$/.test(v) || 'رقم الهاتف غير صالح'
          ],
          field: "phone"
        },
        {
          label: "العنوان",
          placeholder: "أدخل عنوانك",
          value: "",
          type: "text",
          rules: [],
          field: "address"
        },
        {
          label: "المدينة",
          placeholder: "أدخل مدينتك",
          value: "",
          type: "text",
          rules: [],
          field: "city"
        },
        {
          label: "البلد",
          placeholder: "أدخل بلدك",
          value: "",
          type: "text",
          rules: [],
          field: "country"
        }
      ],
      likedCards: [],
    };
  },
  async created() {
    // Listen for global snackbar events
    this.$root.$on('show-snackbar', this.showSnackbar);
    
    try {
      // Fetch user profile data
      const response = await api.get('/user/profile');
      console.log('User Profile Response:', response.data);
      
      if (response.data.success === true) {
        const userData = response.data.user;
        console.log('User Data:', userData);
        
        // Update user fields with the received data
        this.userFields = this.userFields.map(field => ({
          ...field,
          value: userData[field.field] || ''
        }));
      }
    } catch (error) {
      console.error('Error fetching user profile:', error);
      alert('حدث خطأ أثناء جلب بيانات الملف الشخصي');
    }

    // Fetch user's favorite businesses
    await this.fetchFavoriteBusinesses();
  },
  beforeDestroy() {
    // Clean up event listener
    this.$root.$off('show-snackbar', this.showSnackbar);
  },
  methods: {
    async saveUserProfile() {
      // Validate the form
      const isValid = await this.$refs.form.validate();
      console.log('Form validation result:', isValid);

      if (!isValid) {
        let errorMessage = 'الرجاء تصحيح الأخطاء التالية:\n';
        
        // Get all form inputs
        const inputs = this.$refs.form.$children;
        
        // Check each field for errors
        this.userFields.forEach((field, index) => {
          if (inputs[index] && inputs[index].errorBucket && inputs[index].errorBucket.length > 0) {
            errorMessage += `- ${field.label}: ${inputs[index].errorBucket[0]}\n`;
          }
        });

        alert(errorMessage);
        return;
      }

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
    async fetchFavoriteBusinesses() {
      try {
        this.loadingFavorites = true;
        const response = await api.get('/favorites');
        
        if (response.data.status === 'success') {
          const favorites = response.data.data;
          console.log('Fetched favorites:', favorites);
          
          this.likedCards = favorites.map(business => {
            return {
              id: business.id,
              title: business.name,
              image: business.main_picture_url || '/default-business.jpg',
              rating: business.rating || 0,
              location: business.city || '',
              description: business.description || '',
              buttonText: "عرض التفاصيل"
            };
          });
        }
      } catch (error) {
        console.error('Error fetching favorite businesses:', error);
      } finally {
        this.loadingFavorites = false;
      }
    },
    handleFavoriteChange(isFavorite, businessId) {
      if (!isFavorite) {
        // Remove the unfavorited business from the likedCards array
        this.likedCards = this.likedCards.filter(card => card.id !== businessId);
      }
    },
    // Show snackbar notification
    showSnackbar({ text, color }) {
      this.snackbar.text = text;
      this.snackbar.color = color;
      this.snackbar.show = true;
    }
  },
};
</script>

<style>
.text-right {
  text-align: right;
  color: #34495e;
}
.flex-grow-1 {
  flex-grow: 1;
  margin-bottom: 0 !important;
  width: 500px;
}
.flex-grow-1.v-text-field.v-text-field--enclosed .v-text-field__details {
  display: none !important;
}
</style>
