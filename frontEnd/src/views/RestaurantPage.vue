<template>
  <v-app>
    <ContentSection
      background="/photo/v3.png"
      heading="اكتشف أفضل المطاعم في منطقتك!"
      headingColor="#FF9C00"
      description="استمتع بتجربة طعام فريدة مع مجموعة متنوعة من المطاعم التي تقدم أشهى الأطباق وألذها. سواء كنت تبحث عن مطعم فاخر أو مكان عائلي أو وجبة سريعة، ستجد هنا كل ما تحتاجه لتجربة طعام لا تُنسى."
      imageSrc="photo/restorant.png"
      :imageWidth="750"
    />

    <v-content>
      <v-container>
        <v-col style="margin-top: 50px">
          <v-row justify="center">
            <v-col style="justify-content: center">
              <h1 class="son son2 title1">جميع المطاعم</h1>
              <h2 class="son son2 title2">اكتشف تجربة طعام فريدة!</h2>
            </v-col>
          </v-row>

          <v-row v-if="loading">
            <v-col cols="12" class="text-center">
              <v-progress-circular
                indeterminate
                color="primary"
              ></v-progress-circular>
            </v-col>
          </v-row>

          <v-alert
            v-if="error"
            type="error"
            class="mb-4"
          >
            {{ error }}
          </v-alert>

          <v-row v-else-if="restaurants.length === 0">
            <v-col cols="12" class="text-center">
              <h3>لا توجد مطاعم متاحة حالياً</h3>
            </v-col>
          </v-row>

          <v-row v-else>
            <CompanyCard
              v-for="restaurant in restaurants"
              :key="restaurant.id"
              :image="restaurant.main_picture_url || 'photo/Restaurant/default-restaurant.jpg'"
              :title="restaurant.name"
              :rating="restaurant.rating"
              :location="`${restaurant.city}, ${restaurant.country}`"
              :description="restaurant.description"
              :businessId="restaurant.id"
            />
          </v-row>
        </v-col>
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
import CompanyCard from "@/components/CompanyCard.vue";
import ContentSection from "@/components/ContentSection.vue";
import api, { API_URL } from '@/services/api';

export default {
  name: "RestaurantPage",
  components: {
    CompanyCard,
    ContentSection,
  },
  data() {
    return {
      loaded: true,
      primaryColor: "#FF9C00",
      restaurants: [],
      loading: true,
      error: null,
      apiUrl: API_URL
    };
  },
  async created() {
    try {
      this.loading = true;
      const response = await api.get('/businesses', { 
        params: { 
          category_id: 2 // Restaurant category ID
        } 
      });
      console.log('Full Response:', response);
      console.log('Restaurants Data:', response.data.businesses);
      
      if (response.data.status === 'success' && Array.isArray(response.data.businesses)) {
        this.restaurants = response.data.businesses.map(restaurant => ({
          ...restaurant,
          rating: parseFloat(restaurant.rating) || 0
        }));
        console.log('Processed Restaurants:', this.restaurants);
      } else {
        this.error = response.data.message || 'لا توجد مطاعم متاحة';
      }
    } catch (err) {
      this.error = 'حدث خطأ أثناء جلب بيانات المطاعم';
      console.error('Error fetching restaurants:', err);
    } finally {
      this.loading = false;
    }
  }
};
</script>

<style>
.son {
  font-size: 32px;
  font-family: "ExpoBook", sans-serif;
  color: #34495e;
}

.son2 {
  max-width: 90%;
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
