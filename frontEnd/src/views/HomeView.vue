<template>
  <v-app>
    <ContentSection
      background="/photo/h.png"
      heading="اكتشف أفضل الشركات المحلية بسهولة!"
      description="يوفر موقعنا دليلًا شاملاً يمكنك من خلاله البحث عن المطاعم، الفنادق، والمتاجر، وصالات الرياضة في منطقتك، مع تقييمات مفصلة من المستخدمين لمساعدتك في اتخاذ القرار الأفضل. سواء كنت تبحث عن خدمة مميزة أو عروض خاصة."
      additionalText="نحن هنا لتوصيلك بالأفضل!"
      imageSrc="photo/girlimg.png"
    />

    <v-content>
      <v-container v-for="section in sections" :key="section.title">
        <v-col v-if="!loading[section.type] && businesses[section.type] && businesses[section.type].length > 0" style="margin-top: 50px">
          <v-row justify="center">
            <v-col style="justify-content: center">
              <h1 class="son son2 title1">{{ section.title }}</h1>
              <h2 class="son son2 title2">{{ section.subtitle }}</h2>
            </v-col>
          </v-row>

          <v-row>
            <CompanyCard
              v-for="business in businesses[section.type]"
              :key="business.id"
              :image="business.main_picture_url || getDefaultImage(section.type)"
              :title="business.name"
              :rating="business.rating"
              :location="`${business.city}, ${business.country}`"
              :description="business.description"
              :businessId="business.id"
            />
          </v-row>

          <v-row style="justify-content: center; margin-top: 40px">
            <router-link :to="section.link" custom v-slot="{ navigate }">
              <v-btn
                color="#F39C12"
                class="white--text read"
                @click="navigate"
                role="link"
                style="width: 274px !important"
              >
                إكتشف المزيد
              </v-btn>
            </router-link>
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
  name: "HomeView",
  components: {
    CompanyCard,
    ContentSection,
  },
  data() {
    return {
      loaded: true,
      primaryColor: "#FF9C00",
      apiUrl: API_URL,
      loading: {
        company: true,
        restaurant: true,
        hotel: true,
        gym: true
      },
      error: {
        company: null,
        restaurant: null,
        hotel: null,
        gym: null
      },
      businesses: {
        company: [],
        restaurant: [],
        hotel: [],
        gym: []
      },
      categories: [],
      loadingCategories: true,
      categoryMap: {
        company: 1,    // ID for Company category
        restaurant: 2, // ID for Restaurant category
        hotel: 3,      // ID for Hotel category
        gym: 4         // ID for Gym category
      },
      sections: [
        {
          title: "الشركات",
          subtitle: "أعثر على الشركات التي تلبي احتياجك بسهولة!",
          link: "/Company",
          type: "company"
        },
        {
          title: "المطاعم",
          subtitle: "استمتع بتجربة تناول طعام لا تُنسى!",
          link: "/Restaurant",
          type: "restaurant"
        },
        {
          title: "الفنادق",
          subtitle: "اختر راحتك بكل ثقة!",
          link: "/Hotel",
          type: "hotel"
        },
        {
          title: "صالات الرياضة",
          subtitle: "اكتشف أفضل الصالات الرياضية في منطقتك بسهولة!",
          link: "/Sport",
          type: "gym"
        }
      ]
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
      return defaultImages[type];
    },
    async fetchCategories() {
      try {
        this.loadingCategories = true;
        const response = await api.get('/categories');
        
        if (response.data.status === 'success' && Array.isArray(response.data.categories)) {
          this.categories = response.data.categories;
        }
      } catch (err) {
        console.error('Error fetching categories:', err);
      } finally {
        this.loadingCategories = false;
      }
    },
    async fetchBusinesses(type, arabicType) {
      try {
        this.loading[type] = true;
        const categoryId = this.categoryMap[type];
        const response = await api.get(`/businesses`, {
          params: {
            category_id: categoryId
          }
        });
        
        if (response.data.status === 'success' && Array.isArray(response.data.businesses)) {
          this.businesses[type] = response.data.businesses.map(business => ({
            ...business,
            rating: parseFloat(business.rating) || 0
          }));
        } else {
          this.error[type] = response.data.message || `لا توجد ${arabicType} متاحة`;
        }
      } catch (err) {
        this.error[type] = `حدث خطأ أثناء جلب بيانات ${arabicType}`;
        console.error(`Error fetching ${type}:`, err);
      } finally {
        this.loading[type] = false;
      }
    }
  },
  async created() {
    await this.fetchCategories();
    await Promise.all([
      this.fetchBusinesses('company', 'شركة'),
      this.fetchBusinesses('restaurant', 'مطعم'),
      this.fetchBusinesses('hotel', 'فندق'),
      this.fetchBusinesses('gym', 'صالة رياضة')
    ]);
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
