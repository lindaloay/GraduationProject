<template>
  <v-app>
    <v-content class="product-view">
      <!-- Loading & Error States -->
      <div class="state-container" v-if="loading || error">
        <v-row v-if="loading" justify="center" align="center" style="height: 80vh">
          <v-progress-circular indeterminate color="primary" size="64" width="5"></v-progress-circular>
        </v-row>
        
        <v-alert v-if="error" type="error" class="error-alert" elevation="2">
          {{ error }}
        </v-alert>
      </div>

      <!-- Main Content -->
      <div v-if="!loading && !error && business" class="business-container">
        <!-- Hero Section -->
        <section class="hero">
          <div class="hero-media">
            <v-img
              :src="business.main_picture_url ? `${business.main_picture_url}` : getDefaultImage(business.type)"
              class="hero-image"
              gradient="to bottom, rgba(0,0,0,0), rgba(0,0,0,0.7)"
              height="500"
            >
              <!-- Floating Action Cards -->
              <div class="floating-actions">
                <v-chip class="category-chip" color="primary" outlined>
                  <v-icon left small right class="category-icon">{{ business.category ? business.category.icon : getCategoryIcon(business.type) }}</v-icon>
                  <span class="category-text">{{ business.category ? business.category.name_ar : getCategoryName(business.type) }}</span>
                </v-chip>
                
                <div class="action-chips">
                  <v-btn icon color="white" class="action-icon" @click="scrollToSection('contact')">
                    <v-icon>mdi-phone</v-icon>
                  </v-btn>
                  <v-btn icon color="white" class="action-icon" @click="scrollToSection('map')">
                    <v-icon>mdi-map-marker</v-icon>
                  </v-btn>
                  <v-btn icon color="white" class="action-icon" @click="dialog = true">
                    <v-icon>mdi-star</v-icon>
                  </v-btn>
                </div>
              </div>
              
              <!-- Hero Content -->
              <div class="hero-content">
                <h1 class="business-name">{{ business.name }}</h1>
                <div class="business-meta">
                  <div class="rating-container">
                    <v-rating
                      :value="business.rating"
                      color="amber"
                      background-color="rgba(255,255,255,0.3)"
                      dense
                      half-increments
                      readonly
                      size="18"
                    ></v-rating>
                    <span class="rating-value">{{ business.rating }}</span>
                  </div>
                  <div class="location-container">
                    <v-icon small color="white">mdi-map-marker</v-icon>
                    <span> {{ business.address }}, {{ business.city }}, {{ business.country }}</span>
                  </div>
                </div>
                
                <!-- Favorites Count Badge -->
                <div v-if="favoritesCount > 0" class="favorites-count-badge">
                  <v-chip
                    color="#FF5A58"
                    text-color="white"
                    class="favorites-badge"
                  >
                    <v-icon left size="24" right="10">mdi-heart</v-icon>
                    {{ favoritesCount }} إعجاب
                  </v-chip>
                </div>
              </div>
            </v-img>
          </div>
          
          <!-- Quick Actions Bar -->
          <div class="quick-actions-bar">
            <div class="container">
              <div class="quick-actions-wrapper">
                <v-btn color="primary" rounded class="quick-action-btn" @click="scrollToSection('contact')">
                  <v-icon left right>mdi-phone</v-icon>
                  اتصل بنا
                </v-btn>
                <v-btn color="primary" outlined rounded class="quick-action-btn" @click="scrollToSection('map')">
                  <v-icon left right>mdi-map-marker</v-icon>
                  الموقع
                </v-btn>
                <v-btn v-if="isLoggedIn" 
                  :color="isFavorite ? 'red' : 'primary'" 
                  :outlined="!isFavorite"
                  rounded 
                  class="quick-action-btn favorite-action-btn" 
                  @click="toggleFavorite"
                  :loading="favoriteLoading"
                >
                  <v-icon left right>{{ isFavorite ? 'mdi-heart' : 'mdi-heart-outline' }}</v-icon>
                  {{ isFavorite ? 'إزالة من المفضلة' : 'أضف إلى المفضلة' }}
                </v-btn>
                <v-btn color="primary" text rounded class="quick-action-btn" @click="dialog = true">
                  <v-icon left right>mdi-star</v-icon>
                  شاركنا تجربتك
                </v-btn>
              </div>
            </div>
          </div>
        </section>
        
        <!-- Main Content Section -->
        <section class="main-content">
          <v-container>
            <v-row>
              <!-- Left Column (Details) -->
              <v-col cols="12" md="8" lg="9">
                <!-- Business Description -->
                <div class="content-section">
                  <h2 class="section-title">
                    <v-icon color="primary">mdi-information-outline</v-icon>
                    عن المكان
                  </h2>
                  <v-card class="content-card description-card">
                    <v-card-text>
                      <p class="description-text">{{ business.description }}</p>
                    </v-card-text>
                  </v-card>
                </div>
                
                <!-- Gallery Section -->
                <div class="content-section" v-if="galleryImages && galleryImages.length > 0">
                  <h2 class="section-title">
                    <v-icon color="primary">mdi-image-multiple-outline</v-icon>
                    معرض الصور
                  </h2>
                  <v-card class="content-card gallery-card">
                    <v-card-text>
                      <div class="gallery-grid">
                        <div 
                          v-for="(image, index) in galleryImages" 
                          :key="index" 
                          class="gallery-item"
                          @click="openGalleryViewer(index)"
                        >
                          <v-img
                            :src=image
                            aspect-ratio="1"
                            class="gallery-image"
                            cover
                          >
                            <template v-slot:placeholder>
                              <v-row
                                class="fill-height ma-0"
                                align="center"
                                justify="center"
                              >
                                <v-progress-circular
                                  indeterminate
                                  color="primary"
                                ></v-progress-circular>
                              </v-row>
                            </template>
                          </v-img>
                        </div>
                      </div>
                    </v-card-text>
                  </v-card>
                </div>
                
                <!-- Amenities Section -->
                <div class="content-section">
                  <h2 class="section-title">
                    <v-icon color="primary">mdi-star-outline</v-icon>
                    الخدمات والمرافق
                  </h2>
                  <v-card class="content-card amenities-card">
                    <v-card-text>
                      <div class="amenities-grid">
                        <div class="amenity-item" v-if="business.has_wifi">
                          <div class="amenity-icon-wrapper">
                            <v-icon color="primary">mdi-wifi</v-icon>
                          </div>
                          <div class="amenity-label">واي فاي</div>
                        </div>
                        <div class="amenity-item" v-if="business.has_parking">
                          <div class="amenity-icon-wrapper">
                            <v-icon color="primary">mdi-parking</v-icon>
                          </div>
                          <div class="amenity-label">موقف سيارات</div>
                        </div>
                        <div class="amenity-item" v-if="business.near_transportation">
                          <div class="amenity-icon-wrapper">
                            <v-icon color="primary">mdi-bus</v-icon>
                          </div>
                          <div class="amenity-label">قريب من المواصلات</div>
                        </div>
                        <div class="amenity-item" v-if="business.has_online_booking">
                          <div class="amenity-icon-wrapper">
                            <v-icon color="primary">mdi-calendar-check</v-icon>
                          </div>
                          <div class="amenity-label">حجز أونلاين</div>
                        </div>
                      </div>
                    </v-card-text>
                  </v-card>
                </div>
                
                <!-- Working Hours -->
                <div class="content-section">
                  <h2 class="section-title">
                    <v-icon color="primary">mdi-clock-outline</v-icon>
                    ساعات العمل
                  </h2>
                  <v-card class="content-card hours-card">
                    <v-card-text>
                      <div class="hours-content">
                        {{ typeof business.working_times === 'string' 
                          ? JSON.parse(business.working_times) 
                          : business.working_times }}
                      </div>
                    </v-card-text>
                  </v-card>
                </div>
                
                <!-- Feedbacks Section -->
                <div class="content-section" v-if="business.feedbacks && business.feedbacks.length > 0">
                  <h2 class="section-title">
                    <v-icon color="primary">mdi-comment-multiple-outline</v-icon>
                    آراء العملاء
                    <v-chip small color="primary" text-color="white" class="count-chip">
                      {{ business.feedbacks.length }}
                    </v-chip>
                  </h2>
                  
                  <div class="feedback-cards">
                    <v-card 
                      v-for="(feedback, index) in business.feedbacks" 
                      :key="index" 
                      class="feedback-card"
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
                      
                      <div v-if="isNewFeedback(feedback)" class="new-badge-container">
                        <v-chip small class="new-badge">
                          جديد
                        </v-chip>
                      </div>
                    </v-card>
                  </div>
                </div>
                
                <!-- Map Section -->
                <div class="content-section" id="map">
                  <h2 class="section-title">
                    <v-icon color="primary">mdi-map-outline</v-icon>
                    الموقع
                  </h2>
                  <v-card class="content-card map-card">
                    <v-card-text class="map-container">
                      <MapComponent
                        :latitude="business.latitude"
                        :longitude="business.longitude"
                        :name="business.name"
                      />
                    </v-card-text>
                  </v-card>
                </div>
              </v-col>
              
              <!-- Right Column (Contact) -->
              <v-col cols="12" md="4" lg="3">
                <!-- Contact Card -->
                <v-card class="sidebar-card contact-card" id="contact">
                  <v-card-title class="sidebar-card-title">
                    <v-icon color="primary">mdi-phone</v-icon>
                    معلومات التواصل
                  </v-card-title>
                  <v-card-text class="sidebar-card-content">
                    <div class="contact-list">
                      <div class="contact-item">
                        <div class="contact-icon">
                          <v-icon color="primary">mdi-phone</v-icon>
                        </div>
                        <div class="contact-content">
                          <div class="contact-label">رقم الهاتف</div>
                          <div class="contact-value">{{ business.phone }}</div>
                        </div>
                        <div class="contact-action">
                          <v-btn icon x-small @click="copyToClipboard(business.phone)">
                            <v-icon size="16">mdi-content-copy</v-icon>
                          </v-btn>
                        </div>
                      </div>
                      
                      <div class="contact-item">
                        <div class="contact-icon">
                          <v-icon color="primary">mdi-email</v-icon>
                        </div>
                        <div class="contact-content">
                          <div class="contact-label">البريد الإلكتروني</div>
                          <div class="contact-value">{{ business.email }}</div>
                        </div>
                        <div class="contact-action">
                          <v-btn icon x-small @click="copyToClipboard(business.email)">
                            <v-icon size="16">mdi-content-copy</v-icon>
                          </v-btn>
                        </div>
                      </div>
                      
                      <div class="contact-item" v-if="business.website">
                        <div class="contact-icon">
                          <v-icon color="primary">mdi-web</v-icon>
                        </div>
                        <div class="contact-content">
                          <div class="contact-label">الموقع الإلكتروني</div>
                          <div class="contact-value website">
                            <a :href="business.website" target="_blank">{{ business.website }}</a>
                          </div>
                        </div>
                        <div class="contact-action">
                          <v-btn icon x-small @click="copyToClipboard(business.website)">
                            <v-icon size="16">mdi-content-copy</v-icon>
                          </v-btn>
                        </div>
                      </div>
                    </div>
                  </v-card-text>
                </v-card>
                
                <!-- Social Media Card -->
                <v-card class="sidebar-card social-card" v-if="hasSocialMedia">
                  <v-card-title class="sidebar-card-title">
                    <v-icon color="primary">mdi-share-variant</v-icon>
                    وسائل التواصل الاجتماعي
                  </v-card-title>
                  <v-card-text class="sidebar-card-content">
                    <div class="social-links-container">
                      <v-btn
                        v-if="business.facebook_link"
                        fab
                        small
                        color="blue darken-1"
                        dark
                        :href="business.facebook_link"
                        target="_blank"
                        class="social-btn"
                      >
                        <v-icon>mdi-facebook</v-icon>
                      </v-btn>
                      
                      <v-btn
                        v-if="business.instagram_link"
                        fab
                        small
                        color="purple"
                        dark
                        :href="business.instagram_link"
                        target="_blank"
                        class="social-btn"
                      >
                        <v-icon>mdi-instagram</v-icon>
                      </v-btn>
                      
                      <v-btn
                        v-if="business.twitter_link"
                        fab
                        small
                        color="light-blue"
                        dark
                        :href="business.twitter_link"
                        target="_blank"
                        class="social-btn"
                      >
                        <v-icon>mdi-twitter</v-icon>
                      </v-btn>
                    </div>
                  </v-card-text>
                </v-card>
                
                <!-- Feedback CTA Card -->
                <v-card class="sidebar-card cta-card">
                  <v-card-text class="sidebar-card-content">
                    <div class="cta-content">
                      <v-icon color="primary" size="36" class="cta-icon">mdi-star-outline</v-icon>
                      <div class="cta-text">شاركنا تجربتك مع هذا المكان</div>
                      <v-btn 
                        color="primary" 
                        rounded 
                        block 
                        class="cta-btn"
                        @click="dialog = true"
                      >
                        <v-icon left>mdi-star</v-icon>
                        إضافة تقييم
                      </v-btn>
                    </div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-container>
        </section>
      </div>

      <!-- Gallery Viewer Dialog -->
      <v-dialog
        v-model="galleryDialog"
        fullscreen
        hide-overlay
        transition="dialog-bottom-transition"
        class="gallery-dialog"
      >
        <v-card class="gallery-viewer">
          <v-toolbar dark color="primary">
            <v-btn icon @click="galleryDialog = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
            <v-toolbar-title>معرض الصور</v-toolbar-title>
            <v-spacer></v-spacer>
            <div class="gallery-counter">{{ currentImageIndex + 1 }} / {{ galleryImages.length }}</div>
          </v-toolbar>
          
          <div class="gallery-content">
            <v-btn 
              icon 
              large 
              class="gallery-nav prev" 
              @click="prevImage"
              v-if="galleryImages.length > 1"
            >
              <v-icon>mdi-chevron-left</v-icon>
            </v-btn>
            
            <v-img
              :src="currentGalleryImageUrl"
              max-height="90vh"
              contain
              class="gallery-full-image"
            ></v-img>
            
            <v-btn 
              icon 
              large 
              class="gallery-nav next" 
              @click="nextImage"
              v-if="galleryImages.length > 1"
            >
              <v-icon>mdi-chevron-right</v-icon>
            </v-btn>
          </div>
          
          <div class="gallery-thumbnails" v-if="galleryImages.length > 1">
            <div 
              v-for="(image, index) in galleryImages" 
              :key="index"
              class="thumbnail-item"
              :class="{ active: currentImageIndex === index }"
              @click="currentImageIndex = index"
            >
              <v-img
                :src="image"
                height="60"
                width="80"
                cover
              ></v-img>
            </div>
          </div>
        </v-card>
      </v-dialog>

      <!-- Feedback Dialog -->
      <v-dialog v-model="dialog" max-width="500px" transition="dialog-bottom-transition">
        <v-card class="feedback-dialog">
          <v-card-title class="dialog-title">
            <v-icon color="primary" class="dialog-icon">mdi-star-outline</v-icon>
            <span>شاركنا تجربتك</span>
          </v-card-title>
          
          <v-card-text class="dialog-content">
            <div class="rating-section">
              <div class="rating-header">
                <span class="rating-label">تقييمك</span>
                <div class="rating-value-display">
                  <span class="dialog-rating-value">{{ rating }}</span>
                  <span class="rating-max">/5</span>
                </div>
              </div>
              
              <v-rating
                v-model="rating"
                color="amber"
                background-color="grey lighten-3"
                hover
                size="48"
                class="rating-stars"
              ></v-rating>
            </div>
            
            <v-textarea
              v-model="comment"
              label="تعليقك"
              outlined
              color="primary"
              rows="4"
              class="comment-textarea"
              placeholder="شاركنا تجربتك مع هذا المكان..."
              maxlength="500"
              counter
            ></v-textarea>
          </v-card-text>
          
          <v-card-actions class="dialog-actions">
            <v-btn
              color="primary"
              rounded
              class="submit-btn"
              :disabled="!rating"
              :loading="submitting"
              @click="submitFeedback"
            >
              <v-icon left>mdi-send</v-icon>
              إرسال
            </v-btn>
            
            <v-btn
              text
              color="grey darken-1"
              class="cancel-btn"
              @click="dialog = false"
            >
              إلغاء
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Snackbar -->
      <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        :timeout="3000"
        top
        shaped
      >
        <div class="d-flex align-center">
          <v-icon v-if="snackbar.color === 'success'" left>mdi-check-circle</v-icon>
          <v-icon v-else-if="snackbar.color === 'error'" left>mdi-alert-circle</v-icon>
          {{ snackbar.text }}
        </div>
        <template v-slot:action="{ attrs }">
          <v-btn
            text
            v-bind="attrs"
            @click="snackbar.show = false"
          >
            إغلاق
          </v-btn>
        </template>
      </v-snackbar>
    </v-content>
  </v-app>
</template>

<script>
import MapComponent from "@/components/MapComponent.vue";
import api, { API_URL } from '@/services/api';
import { authService } from '@/services/auth';

export default {
  name: "ProductPage",
  components: {
    MapComponent,
  },
  props: {
    businessId: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      loading: true,
      error: null,
      apiUrl: API_URL,
      business: null,
      galleryImages: [],
      dialog: false,
      rating: 0,
      comment: '',
      carouselModel: 0,
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      },
      submitting: false,
      galleryDialog: false,
      currentImageIndex: 0,
      isFavorite: false,
      favoriteLoading: false,
      favoritesCount: 0
    };
  },
  computed: {
    hasSocialMedia() {
      return this.business && (
        this.business.facebook_link ||
        this.business.instagram_link ||
        this.business.twitter_link
      );
    },
    currentGalleryImageUrl() {
      if (!this.galleryImages || this.galleryImages.length === 0) return '';
      return this.galleryImages[this.currentImageIndex];
    },
    isLoggedIn() {
      return authService.isTokenValid();
    }
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
    getCategoryIcon(type) {
      const icons = {
        company: 'mdi-domain',
        restaurant: 'mdi-silverware-fork-knife',
        hotel: 'mdi-bed',
        gym: 'mdi-dumbbell'
      };
      return icons[type] || 'mdi-store';
    },
    getCategoryName(type) {
      return type;
    },
    scrollToSection(sectionId) {
      const element = document.getElementById(sectionId);
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    },
    async fetchBusinessDetails() {
      try {
        this.loading = true;
        const [businessResponse, feedbacksResponse] = await Promise.all([
          api.get(`/businesses/${this.businessId}`),
          api.get(`/businesses/${this.businessId}/feedbacks`)
        ]);
        
        if (businessResponse.data.status === 'success') {
          this.business = businessResponse.data.business;
          this.galleryImages = this.business.gallery_picture_urls || [];
          
          // Add feedbacks to business object if they exist
          if (feedbacksResponse.data.status === 'success') {
            this.business.feedbacks = feedbacksResponse.data.feedbacks;
          }
          
          // Check if business is favorited (if user is logged in)
          if (this.isLoggedIn) {
            await this.checkFavoriteStatus();
          }
          
          // Fetch favorites count
          await this.fetchFavoritesCount();
        } else {
          this.error = 'حدث خطأ أثناء جلب بيانات الشركة';
        }
      } catch (err) {
        this.error = 'حدث خطأ أثناء جلب بيانات الشركة';
        console.error('Error fetching business details:', err);
      } finally {
        this.loading = false;
      }
    },
    async submitFeedback() {
      try {
        this.submitting = true;
        const response = await api.post('/feedback', {
          business_id: this.businessId,
          rating: this.rating,
          comment: this.comment
        });

        if (response.data.status === 'success') {
          this.dialog = false;
          this.rating = 0;
          this.comment = '';
          this.showSnackbar('تم إرسال تقييمك بنجاح', 'success');
          await this.fetchBusinessDetails();
        }
      } catch (err) {
        this.showSnackbar(err.response.data.message, 'error');
        console.error('Error submitting feedback:', err);
      } finally {
        this.submitting = false;
      }
    },
    copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        this.showSnackbar('تم نسخ النص بنجاح', 'success');
      }).catch(() => {
        this.showSnackbar('حدث خطأ أثناء النسخ', 'error');
      });
    },
    showSnackbar(text, color) {
      this.snackbar.text = text;
      this.snackbar.color = color;
      this.snackbar.show = true;
    },
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('ar-SA', options);
    },
    getRelativeTime(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);
      
      const times = {
        year: Math.floor(diffInSeconds / 31536000),
        month: Math.floor(diffInSeconds / 2592000),
        week: Math.floor(diffInSeconds / 604800),
        day: Math.floor(diffInSeconds / 86400),
        hour: Math.floor(diffInSeconds / 3600),
        minute: Math.floor(diffInSeconds / 60)
      };

      const arabicUnits = {
        year: ['سنة', 'سنتين', 'سنوات'],
        month: ['شهر', 'شهرين', 'أشهر'],
        week: ['أسبوع', 'أسبوعين', 'أسابيع'],
        day: ['يوم', 'يومين', 'أيام'],
        hour: ['ساعة', 'ساعتين', 'ساعات'],
        minute: ['دقيقة', 'دقيقتين', 'دقائق']
      };

      for (const [unit, value] of Object.entries(times)) {
        if (value > 0) {
          if (value === 1) return `منذ ${arabicUnits[unit][0]}`;
          if (value === 2) return `منذ ${arabicUnits[unit][1]}`;
          if (value < 11) return `منذ ${value} ${arabicUnits[unit][2]}`;
          return `منذ ${value} ${arabicUnits[unit][2]}`;
        }
      }
      return 'الآن';
    },
    isNewFeedback(feedback) {
      const feedbackDate = new Date(feedback.created_at);
      const now = new Date();
      const diffInHours = (now - feedbackDate) / (1000 * 60 * 60);
      return diffInHours < 24; // Consider feedbacks from the last 24 hours as new
    },
    openGalleryViewer(index) {
      this.currentImageIndex = index;
      this.galleryDialog = true;
    },
    prevImage() {
      this.currentImageIndex = (this.currentImageIndex - 1 + this.galleryImages.length) % this.galleryImages.length;
    },
    nextImage() {
      this.currentImageIndex = (this.currentImageIndex + 1) % this.galleryImages.length;
    },
    async toggleFavorite() {
      try {
        this.favoriteLoading = true;
        
        // If already favorited, unfavorite it, otherwise favorite it
        if (this.isFavorite) {
          // Remove from favorites
          const response = await api.delete('/favorites', { 
            data: { business_id: this.businessId } 
          });
          
          if (response.data.status === 'success') {
            this.isFavorite = false;
            // Decrease favorites count
            if (this.favoritesCount > 0) {
              this.favoritesCount -= 1;
            }
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
            this.isFavorite = true;
            // Increase favorites count
            this.favoritesCount += 1;
            this.showSnackbar('تمت إضافة المكان إلى المفضلة', 'success');
          } else {
            this.showSnackbar('حدث خطأ أثناء إضافة المكان إلى المفضلة', 'error');
          }
        }
      } catch (err) {
        this.showSnackbar('حدث خطأ في عملية المفضلة', 'error');
        console.error('Error toggling favorite:', err);
      } finally {
        this.favoriteLoading = false;
      }
    },
    async checkFavoriteStatus() {
      try {
        const response = await api.get(`/favorites/check/${this.businessId}`);
        if (response.data.status === 'success') {
          this.isFavorite = response.data.is_favorite;
        }
      } catch (err) {
        // If there's an error, just assume not favorited
        this.isFavorite = false;
        console.error('Error checking favorite status:', err);
      }
    },
    async fetchFavoritesCount() {
      try {
        const response = await api.get(`/businesses/${this.businessId}/favorites/count`);
        if (response.data.status === 'success') {
          this.favoritesCount = response.data.count;
          console.log('Favorites count:', this.favoritesCount);
        }
      } catch (error) {
        console.error('Error fetching favorites count:', error);
      }
    }
  },
  async created() {
    await this.fetchBusinessDetails();
  }
};
</script>

<style scoped>
/* Global Styles */
.product-view {
  background: #f8f9fa;
  min-height: 100vh;
}

/* Hero Section */
.hero {
  position: relative;
  margin-bottom: 80px;
}

.hero-media {
  width: 100%;
  position: relative;
}

.hero-image {
  width: 100%;
  position: relative;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.floating-actions {
  position: absolute;
  top: 20px;
  left: 0;
  width: 100%;
  padding: 0 20px;
  display: flex;
  justify-content: space-between;
  z-index: 2;
}

.category-chip {
  font-weight: 500;
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.2) !important;
  padding: 0 12px;
}

.category-icon {
  margin-right: 8px;
}

.category-text {
  margin-left: 4px;
}

.action-chips {
  display: flex;
  gap: 8px;
}

.action-icon {
  backdrop-filter: blur(10px);
  background: rgba(0, 0, 0, 0.2);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.action-icon:hover {
  transform: scale(1.1);
  background: rgba(0, 0, 0, 0.4);
}

.hero-content {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 40px;
  color: white;
  z-index: 1;
}

.business-name {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 16px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.business-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  margin-bottom: 16px;
}

.rating-container, .location-container {
  display: flex;
  align-items: center;
  gap: 8px;
}

.rating-value {
  font-weight: 600;
  font-size: 1rem;
}

/* Quick Actions Bar */
.quick-actions-bar {
  position: absolute;
  bottom: -30px;
  left: 0;
  width: 85%;
  z-index: 10;
}

.quick-actions-wrapper {
  max-width: 800px;
  margin: 0 auto;
  background: white;
  padding: 16px 24px;
  border-radius: 16px;
  display: flex;
  justify-content: space-around;
  gap: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.quick-action-btn {
  font-weight: 500;
  transition: all 0.3s ease;
  min-width: 140px;
  padding: 10px 16px;
  margin: 0 4px;
}

.quick-action-btn:hover {
  transform: translateY(-2px);
}

/* Main Content */
.main-content {
  padding-bottom: 60px;
}

.content-section {
  margin-bottom: 40px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 16px;
  color: #333;
}

.content-card {
  border-radius: 16px;
  overflow: hidden;
  background: white;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.content-card:hover {
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

/* Description Card */
.description-text {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #444;
  white-space: pre-line;
}

/* Gallery Card */
.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.gallery-item {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.gallery-item:hover {
  transform: translateY(-5px);
}

.gallery-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Amenities Section */
.amenities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.amenity-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.amenity-item:hover {
  background: #e3f2fd;
  transform: translateY(-5px);
}

.amenity-icon-wrapper {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.amenity-label {
  font-weight: 500;
  color: #333;
}

/* Hours Card */
.hours-content {
  white-space: pre-line;
  line-height: 1.8;
  font-size: 1.1rem;
}

/* Feedback Cards */
.feedback-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
}

.feedback-card {
  position: relative;
  padding: 20px;
  border-radius: 12px !important;
  transition: all 0.3s ease;
}

.feedback-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.feedback-card.highlighted {
  border-right: 4px solid #4caf50;
  background: #f1f8e9;
}

.feedback-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  background: #f1f3f5;
  border: 2px solid #e0e0e0;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  color: #333;
  font-size: 1rem;
}

.feedback-date {
  color: #666;
  font-size: 0.8rem;
}

.feedback-rating {
  display: flex;
  align-items: center;
  gap: 8px;
}

.feedback-body {
  margin-bottom: 18px;
}

.feedback-text {
  white-space: pre-line;
  line-height: 1.6;
  color: #444;
  font-size: 0.95rem;
  margin: 0;
}

.new-badge-container {
  position: absolute;
  top: 4px;
  left: 16px;
}

.new-badge {
  font-weight: 500;
  font-size: 0.8rem;
  background: linear-gradient(45deg, #4CAF50, #45a049);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4);
  }
  70% {
    box-shadow: 0 0 0 6px rgba(76, 175, 80, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
  }
}

/* Sidebar Card Styles */
.sidebar-card {
  margin-bottom: 24px;
  border-radius: 16px;
  overflow: hidden;
  background: white;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.sidebar-card:hover {
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.sidebar-card-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.2rem;
  font-weight: 600;
  padding: 16px 20px;
  background: linear-gradient(to right, #fff, #f8f9fa);
  border-bottom: 1px solid #eee;
}

.sidebar-card-content {
  padding: 20px;
}

/* Contact Card Specific */
.contact-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.contact-item {
  display: flex;
  align-items: center;
  background: #f8f9fa;
  border-radius: 12px;
  padding: 12px;
  transition: all 0.3s ease;
}

.contact-item:hover {
  background: #e3f2fd;
}

.contact-icon {
  margin-left: 16px;
}

.contact-content {
  flex: 1;
}

.contact-label {
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 4px;
}

.contact-value {
  font-weight: 500;
  color: #333;
  word-break: break-all;
}

.contact-value.website a {
  color: #1976d2;
  text-decoration: none;
  transition: color 0.3s ease;
}

.contact-value.website a:hover {
  color: #1565c0;
  text-decoration: underline;
}

/* Social Media Card */
.social-links-container {
  display: flex;
  justify-content: space-around;
  padding: 12px 0;
}

.social-btn {
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.social-btn:hover {
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

/* CTA Card */
.cta-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 16px;
}

.cta-text {
  font-weight: 500;
  color: #333;
  margin-bottom: 16px;
}

.cta-btn {
  font-weight: 500;
  transition: all 0.3s ease;
}

.cta-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* Feedback Dialog */
.feedback-dialog {
  padding: 20px;
}

.dialog-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.5rem;
  font-weight: 600;
  padding: 16px 20px;
  background: linear-gradient(to right, #fff, #f8f9fa);
  border-bottom: 1px solid #eee;
}

.dialog-icon {
  font-size: 2rem;
}

.dialog-content {
  padding: 20px;
}

.rating-section {
  margin-bottom: 20px;
}

.rating-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.rating-label {
  font-size: 1rem;
  font-weight: 600;
}

.rating-value-display {
  display: flex;
  align-items: center;
  gap: 8px;
}

.rating-value {
  font-size: 1.2rem;
  font-weight: 600;
}

.rating-max {
  font-size: 0.8rem;
  font-weight: 500;
}

.rating-stars {
  margin-bottom: 16px;
}

.comment-textarea {
  margin-bottom: 20px;
}

.dialog-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.submit-btn {
  font-weight: 500;
  transition: all 0.3s ease;
}

.submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.cancel-btn {
  font-weight: 500;
  transition: all 0.3s ease;
}

.cancel-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* Gallery Viewer Dialog */
.gallery-dialog {
  background: rgba(0, 0, 0, 0.8);
}

.gallery-viewer {
  background: white;
  border-radius: 16px;
  overflow: hidden;
}

.gallery-toolbar {
  background: linear-gradient(to right, #fff, #f8f9fa);
}

.gallery-counter {
  font-size: 1.2rem;
  font-weight: 500;
  color: #333;
}

.gallery-content {
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  height: calc(100vh - 180px);
  background: #f1f3f5;
}

.gallery-full-image {
  max-width: 80%;
  max-height: 80vh;
}

.gallery-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 2;
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.8) !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.gallery-nav.prev {
  left: 20px;
}

.gallery-nav.next {
  right: 20px;
}

.gallery-thumbnails {
  display: flex;
  justify-content: center;
  gap: 12px;
  padding: 12px 0;
}

.thumbnail-item {
  width: 80px;
  height: 60px;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.thumbnail-item:hover {
  transform: scale(1.1);
}

.thumbnail-item.active {
  border: 2px solid #4caf50;
}

/* Responsive Design */
@media (max-width: 960px) {
  .hero {
    margin-bottom: 60px;
  }
  
  .business-name {
    font-size: 2rem;
  }
  
  .hero-content {
    padding: 30px;
  }
  
  .quick-actions-wrapper {
    flex-wrap: wrap;
    justify-content: center;
    padding: 12px 16px;
  }
  
  .quick-action-btn {
    margin: 4px;
  }
  
  .section-title {
    font-size: 1.3rem;
  }
}

@media (max-width: 600px) {
  .hero {
    margin-bottom: 100px;
  }
  
  .hero-image {
    height: 400px !important;
  }
  
  .business-name {
    font-size: 1.8rem;
  }
  
  .business-meta {
    flex-direction: column;
    gap: 12px;
  }
  
  .quick-actions-wrapper {
    flex-direction: column;
    gap: 12px;
    padding: 16px;
  }
  
  .quick-action-btn {
    width: 100%;
  }
  
  .section-title {
    font-size: 1.2rem;
  }
}

.favorite-btn {
  width: 40px;
  height: 40px;
  background-color: rgba(0, 0, 0, 0.2) !important;
  backdrop-filter: blur(5px);
  transition: all 0.3s ease;
}

.favorite-btn:hover {
  transform: scale(1.1);
  background-color: rgba(0, 0, 0, 0.4) !important;
}

.favorite-btn .v-icon {
  font-size: 20px;
}

.favorite-action-btn {
  transition: all 0.3s ease;
  min-width: 160px;
}

.favorite-action-btn.v-btn--is-elevated {
  box-shadow: 0 3px 5px rgba(244, 67, 54, 0.3) !important;
}

.favorite-action-btn.v-btn--is-elevated:hover {
  box-shadow: 0 5px 8px rgba(244, 67, 54, 0.4) !important;
}

.favorite-action-btn .v-icon {
  margin-right: 8px;
}

/* Add CSS styles for the favorites badge */
.favorites-count-badge {
  position: absolute;
  top: 20px;
  left: 20px;
  z-index: 5;
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
</style>
