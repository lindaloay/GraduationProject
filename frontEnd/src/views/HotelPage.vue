<template>
  <v-app>
    <ContentSection
      background="/photo/v3.png"
      heading="جعلنا اختيار الفندق المثالي أسهل من أي وقت مضى!"
      headingColor="#FF9C00"
      description="استعرض أفضل الفنادق في منطقتك أو وجهتك القادمة مع تقييمات تفصيلية تشمل نظافة الغرف، جودة الخدمة، المرافق، والأسعار. خطط لرحلتك بسهولة واحجز إقامتك بثقة لتضمن تجربة مريحة لا تُنسى. سواء كنت مسافرًا للعمل أو الترفيه، نحن هنا لتوصيلك بأفضل الخيارات!"
      imageSrc="photo/hotelgirl1.png"
      :imageWidth="750"
    />

    <v-content>
      <v-container>
        <v-col style="margin-top: 50px">
          <v-row justify="center">
            <v-col style="justify-content: center">
              <h1 class="son son2 title1">جميع الفنادق</h1>
              <h2 class="son son2 title2">اختر راحتك بكل ثقة!</h2>
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

          <v-row v-else-if="hotels.length === 0">
            <v-col cols="12" class="text-center">
              <h3>لا توجد فنادق متاحة حالياً</h3>
            </v-col>
          </v-row>

          <v-row v-else>
            <CompanyCard
              v-for="hotel in hotels"
              :key="hotel.id"
              :image="hotel.main_picture_url || 'photo/Hotel/default-hotel.jpg'"
              :title="hotel.name"
              :rating="hotel.rating"
              :businessId="hotel.id"
              :location="`${hotel.city}, ${hotel.country}`"
              :description="hotel.description"
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
  name: "HotelPage",
  components: {
    CompanyCard,
    ContentSection,
  },
  data() {
    return {
      loaded: true,
      primaryColor: "#FF9C00",
      hotels: [],
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
          category_id: 3 // Hotel category ID
        } 
      });
      console.log('Full Response:', response);
      console.log('Businesses Data:', response.data.businesses);
      
      if (response.data.status === 'success' && Array.isArray(response.data.businesses)) {
        this.hotels = response.data.businesses.map(hotel => ({
          ...hotel,
          rating: parseFloat(hotel.rating) || 0
        }));
        console.log('Processed Hotels:', this.hotels);
      } else {
        this.error = response.data.message || 'لا توجد فنادق متاحة';
      }
    } catch (err) {
      this.error = 'حدث خطأ أثناء جلب بيانات الفنادق';
      console.error('Error fetching hotels:', err);
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
