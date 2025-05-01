<template>
  <v-app>
    <ContentSection
      background="/photo/v3.png"
      heading="اكتشف أفضل صالات الرياضة في منطقتك!"
      headingColor="#FF9C00"
      description="استكشف مجموعة متنوعة من صالات الرياضة التي تقدم أحدث الأجهزة والتدريبات. سواء كنت تبحث عن صالة رياضية متكاملة، مركز لياقة بدنية، أو دروس جماعية، ستجد هنا كل ما تحتاجه لتحقيق أهدافك الرياضية."
      imageSrc="photo/Gym/gymGuy.png"
      :imageWidth="750"
    />

    <v-content>
      <v-container>
        <v-col style="margin-top: 50px">
          <v-row justify="center">
            <v-col style="justify-content: center">
              <h1 class="son son2 title1">جميع صالات الرياضة</h1>
              <h2 class="son son2 title2">ابدأ رحلتك نحو اللياقة البدنية!</h2>
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

          <v-row v-else-if="sports.length === 0">
            <v-col cols="12" class="text-center">
              <h3>لا توجد صالات رياضة متاحة حالياً</h3>
            </v-col>
          </v-row>

          <v-row v-else>
            <CompanyCard
              v-for="sport in sports"
              :key="sport.id"
              :image="sport.main_picture_url || 'photo/Sport/default-sport.jpg'"
              :title="sport.name"
              :rating="sport.rating"
              :businessId="sport.id"
              :location="`${sport.city}, ${sport.country}`"
              :description="sport.description"
              
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
  name: "SportPage",
  components: {
    CompanyCard,
    ContentSection,
  },
  data() {
    return {
      loaded: true,
      primaryColor: "#FF9C00",
      sports: [],
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
          category_id: 4 // Gym category ID
        } 
      });
      console.log('Full Response:', response);
      console.log('Sports Data:', response.data.businesses);
      
      if (response.data.status === 'success' && Array.isArray(response.data.businesses)) {
        this.sports = response.data.businesses.map(sport => ({
          ...sport,
          rating: parseFloat(sport.rating) || 0
        }));
        console.log('Processed Sports:', this.sports);
      } else {
        this.error = response.data.message || 'لا توجد صالات رياضة متاحة';
      }
    } catch (err) {
      this.error = 'حدث خطأ أثناء جلب بيانات صالات الرياضة';
      console.error('Error fetching sports:', err);
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
