<template>
  <v-col style="margin-right: inherit">
    <v-card
      :loading="loading"
      max-width="374"
      style="border-radius: 10px; justify-self: center"
      height="450"
    >
      <v-img :src="image" height="170"></v-img>

      <v-card-title>
        <span>{{ title }}</span>
        <v-spacer></v-spacer>
        <v-btn
          v-if="isLoggedIn"
          icon
          :loading="favoriteLoading"
          :color="isFavoriteInternal ? '#FF5A58' : 'grey lighten-1'"
          @click="toggleFavorite"
        >
          <v-icon>{{ isFavoriteInternal ? "mdi-heart" : "mdi-heart-outline" }}</v-icon>
        </v-btn>
      </v-card-title>

      <v-card-text style="height: 120px; overflow: hidden; padding: 8px 16px;">
        <v-row align="center" class="mx-0 mb-2">
          <v-rating
            :value="rating"
            color="amber"
            background-color="grey lighten-2"
            dense
            half-increments
            readonly
            size="20"
          ></v-rating>
        </v-row>

        <div class="my-2 subtitle">
          <v-icon color="#ffa726">mdi-map-marker</v-icon>
          {{ location }}
        </div>

        <div class="discription" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-top: 8px;">{{ description }}</div>
      </v-card-text>

      <v-card-actions>
        <router-link :to="{ name: 'ProductPage', params: { businessId: businessId }}" custom v-slot="{ navigate }">
          <v-btn
            outlined
            color="warning"
            class="buttons text-orange--text"
            width="340"
            @click="navigate"
          >
            {{ buttonText }}
          </v-btn>
        </router-link>
      </v-card-actions>
    </v-card>
  </v-col>
</template>

<script>
import api from '@/services/api';
import { authService } from '@/services/auth';

export default {
  data() {
    return {
      isFavoriteInternal: this.isFavorite, // Internal favorite state
      favoriteLoading: false
    };
  },
  methods: {
    async toggleFavorite(event) {
      // Prevent navigation when clicking the heart icon
      event.stopPropagation();
      
      // Check if user is logged in
      if (!this.isLoggedIn) {
        this.$router.push('/login');
        return;
      }
      
      this.favoriteLoading = true;
      
      try {
        if (this.isFavoriteInternal) {
          // Remove from favorites
          const response = await api.delete('/favorites', { 
            data: { business_id: this.businessId } 
          });
          
          if (response.data.status === 'success') {
            this.isFavoriteInternal = false;
            this.$emit("update:isFavorite", false);
            this.showSnackbar('تمت إزالة المكان من المفضلة', 'success');
          } else {
            this.showSnackbar('حدث خطأ أثناء إزالة المكان من المفضلة', 'error');
          }
        } else {
          // Add to favorites
          const response = await api.post('/favorites', { 
            business_id: this.businessId 
          });
          
          if (response.data.status === 'success') {
            this.isFavoriteInternal = true;
            this.$emit("update:isFavorite", true);
            this.showSnackbar('تمت إضافة المكان إلى المفضلة', 'success');
          } else {
            this.showSnackbar('حدث خطأ أثناء إضافة المكان إلى المفضلة', 'error');
          }
        }
      } catch (err) {
        console.error('Error toggling favorite:', err);
        this.showSnackbar('حدث خطأ في عملية المفضلة', 'error');
      } finally {
        this.favoriteLoading = false;
      }
    },
    
    showSnackbar(text, color) {
      this.$root.$emit('show-snackbar', { text, color });
    },
    
    async checkFavoriteStatus() {
      if (!this.isLoggedIn) {
        this.isFavoriteInternal = false;
        return;
      }
      
      try {
        const response = await api.get(`/favorites/check/${this.businessId}`);
        if (response.data.status === 'success') {
          this.isFavoriteInternal = response.data.is_favorite;
          this.$emit("update:isFavorite", this.isFavoriteInternal);
        }
      } catch (err) {
        console.error('Error checking favorite status:', err);
        this.isFavoriteInternal = false;
      }
    }
  },
  computed: {
    isLoggedIn() {
      return authService.isTokenValid();
    }
  },
  watch: {
    isFavorite(newVal) {
      // Update internal state if prop changes
      this.isFavoriteInternal = newVal;
    }
  },
  mounted() {
    // Check favorite status when component is mounted
    this.checkFavoriteStatus();
  },
  name: "CompanyCard",
  props: {
    image: String,
    title: String,
    rating: {
      type: Number,
      default: 4,
    },
    location: String,
    description: String,
    businessId: {
      type: [String, Number],
      required: true
    },
    buttonText: {
      type: String,
      default: "المزيد",
    },
    loading: {
      type: Boolean,
      default: false,
    },
    isFavorite: {
      type: Boolean,
      default: false, // Default state is not favorite
    },
  },
};
</script>

<style>
.read {
  padding: 20px 25px !important;
  font-size: 15px !important;
  border-radius: 7px !important;
  font-family: "ExpoLight" !important;
  font-weight: bold !important;
}

.buttons {
  border-radius: 4px !important;
  background-color: white;
}

.text-orange--text {
  color: #ffa726 !important;
  font-family: "ExpoLight";
  font-weight: bold;
}

.v-card__actions {
  justify-content: center;
}

.v-card__title,
.subtitle,
.discription {
  font-family: "ExpoLight";
  font-weight: bold !important;
  color: #34495e;
}

.v-card__title {
  font-size: 1.8rem !important;
  margin-bottom: 12px;
  margin-top: 12px;
}

.subtitle {
  font-size: 1.3rem !important;
  padding-top: 8px;
}

.discription {
  font-size: 1.1rem !important;
}

.title1 {
  font-size: 3rem;
  justify-self: center;
}

.title2 {
  font-size: 2rem;
  justify-self: center;
}
</style>
