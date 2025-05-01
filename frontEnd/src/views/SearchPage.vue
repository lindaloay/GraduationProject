<template>
  <v-container style="padding-top: 100px; height: 100%">
    <!-- Search Bar -->
    <v-row justify="center" class="mb-8" dir="rtl">
      <v-col cols="12" md="8" lg="6">
        <div class="search-container">
          <v-text-field
            v-model="searchQuery"
            label="ابحث عن شركة، مطعم، فندق، أو صالة رياضية"
            prepend-inner-icon="mdi-magnify"
            solo
            flat
            hide-details
            class="search-field"
            @keydown.enter="performSearch"
          >
            <template v-slot:append>
              <v-btn
                color="primary"
                class="search-button"
                @click="performSearch"
                :disabled="!searchQuery.trim()"
              >
                بحث
              </v-btn>
            </template>
          </v-text-field>
        </div>
      </v-col>
    </v-row>

    <!-- Loading State -->
    <v-row v-if="loading" justify="center">
      <v-progress-circular
        indeterminate
        color="primary"
        size="64"
      ></v-progress-circular>
    </v-row>

    <!-- Error State -->
    <v-alert
      v-if="error"
      type="error"
      class="mb-4"
      elevation="2"
    >
      {{ error }}
    </v-alert>

    <!-- Results -->
    <v-container v-if="!loading && !error">
      <v-row v-if="filteredBusinesses.length">
        <CompanyCard
          v-for="business in filteredBusinesses"
          :key="business.id"
          :image="business.main_picture_url || getDefaultImage(business.type)"
          :title="business.name"
          :rating="business.rating"
          :location="`${business.city}, ${business.country}`"
          :description="business.description"
          :businessId="business.id"
        />
      </v-row>

      <!-- No Results Message -->
      <v-row v-else-if="searchQuery">
        <v-col cols="12" class="text-center">
          <v-icon size="64" color="grey lighten-1" class="mb-4">mdi-magnify-close</v-icon>
          <h3 class="grey--text text--darken-1">لا توجد نتائج لـ "{{ searchQuery }}"</h3>
          <p class="grey--text">حاول البحث بكلمات مختلفة أو أكثر عمومية</p>
        </v-col>
      </v-row>
    </v-container>
  </v-container>
</template>

<script>
import CompanyCard from "@/components/CompanyCard.vue";
import api, { API_URL } from '@/services/api';

export default {
  name: "SearchPage",
  components: {
    CompanyCard,
  },
  data() {
    return {
      searchQuery: "",
      loading: false,
      error: null,
      apiUrl: API_URL,
      allBusinesses: [],
      filteredBusinesses: []
    };
  },
  methods: {
    getDefaultImage(type) {
      const defaultImages = {
        company: 'photo/Company/default-company.jpg',
        restaurant: 'photo/Restaurant/default-restaurant.jpg',
        hotel: 'photo/Hotel/default-hotel.jpg',
        gym: 'photo/Gym/default-gym.jpg'
      };
      return defaultImages[type] || 'photo/Company/default-company.jpg';
    },
    async fetchAllBusinesses() {
      try {
        this.loading = true;
        const response = await api.get('/businesses');
        
        if (response.data.status === 'success' && Array.isArray(response.data.businesses)) {
          this.allBusinesses = response.data.businesses.map(business => ({
            ...business,
            rating: parseFloat(business.rating) || 0
          }));
          this.filteredBusinesses = [...this.allBusinesses];
        } else {
          this.error = 'حدث خطأ أثناء جلب بيانات الشركات';
        }
      } catch (err) {
        this.error = 'حدث خطأ أثناء جلب بيانات الشركات';
        console.error('Error fetching businesses:', err);
      } finally {
        this.loading = false;
      }
    },
    performSearch() {
      if (!this.searchQuery.trim()) {
        this.filteredBusinesses = [...this.allBusinesses];
        return;
      }

      const query = this.searchQuery.toLowerCase().trim();
      this.filteredBusinesses = this.allBusinesses.filter(business => 
        business.name.toLowerCase().includes(query)
      );
    }
  },
  watch: {
    searchQuery(newVal) {
      if (!newVal.trim()) {
        this.filteredBusinesses = [...this.allBusinesses];
      }
    }
  },
  async created() {
    await this.fetchAllBusinesses();
  }
};
</script>

<style scoped>
.search-container {
  position: relative;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.search-container:focus-within {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.search-field {
  font-size: 1.1rem;
}

.search-field :deep(.v-input__slot) {
  padding: 0 16px !important;
  min-height: 56px !important;
}

.search-field :deep(.v-text-field__slot) {
  margin-right: 8px;
}

.search-button {
  height: 40px !important;
  margin: 8px !important;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0;
}

.search-button:disabled {
  background-color: rgba(0, 0, 0, 0.12) !important;
  color: rgba(0, 0, 0, 0.26) !important;
}

/* RTL specific styles */
:deep(.v-input__prepend-inner) {
  margin-right: 0 !important;
  margin-left: 8px !important;
}

:deep(.v-input__append-inner) {
  margin-right: 8px !important;
  margin-left: 0 !important;
}

:deep(.v-label) {
  right: 0 !important;
  left: auto !important;
  transform-origin: right !important;
}
</style>
