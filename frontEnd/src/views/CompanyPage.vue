<template>
  <v-app>
    <ContentSection
      background="/photo/v3.png"
      heading="اكتشف أفضل الشركات في منطقتك!"
      headingColor="#FF9C00"
      description="استكشف مجموعة متنوعة من الشركات المحلية التي تقدم خدمات متميزة. سواء كنت تبحث عن شركات تكنولوجيا، خدمات استشارية، أو شركات تصنيع، ستجد هنا كل ما تحتاجه للتواصل مع أفضل الشركات في مجالك."
      imageSrc="photo/Compogirl.png"
      :imageWidth="750"
    />

    <v-content>
      <v-container>
        <v-col style="margin-top: 50px">
          <v-row justify="center">
            <v-col style="justify-content: center">
              <h1 class="son son2 title1">جميع الشركات</h1>
              <h2 class="son son2 title2">اكتشف أفضل الشركات في مجالك!</h2>
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

          <v-row v-else-if="companies.length === 0">
            <v-col cols="12" class="text-center">
              <h3>لا توجد شركات متاحة حالياً</h3>
            </v-col>
          </v-row>

          <v-row v-else>
            <CompanyCard
              v-for="company in companies"
              :key="company.id"
              :image="company.main_picture_url || 'photo/Company/default-company.jpg'"
              :title="company.name"
              :rating="company.rating"
              :businessId="company.id"
              :location="`${company.city}, ${company.country}`"
              :description="company.description"
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
  name: "CompanyPage",
  components: {
    CompanyCard,
    ContentSection,
  },
  data() {
    return {
      loaded: true,
      primaryColor: "#FF9C00",
      companies: [],
      loading: true,
      error: null,
      apiUrl: API_URL
    };
  },
  async created() {
    try {
      // Load companies
      this.loading = true;
      const response = await api.get('/businesses', { 
        params: { 
          category_id: 1 // Company category ID
        } 
      });
      console.log('Full Response:', response);
      console.log('Companies Data:', response.data.businesses);
      
      if (response.data.status === 'success' && Array.isArray(response.data.businesses)) {
        this.companies = response.data.businesses.map(company => ({
          ...company,
          rating: parseFloat(company.rating) || 0
        }));
        console.log('Processed Companies:', this.companies);
      } else {
        this.error = response.data.message || 'لا توجد شركات متاحة';
      }
    } catch (err) {
      this.error = 'حدث خطأ أثناء جلب بيانات الشركات';
      console.error('Error fetching companies:', err);
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
