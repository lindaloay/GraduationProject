<template>
  <v-app>
    <!-- Admin Navigation Drawer -->
    <admin-sidebar 
      :drawer.sync="drawer"
      :mini.sync="miniVariant"
    />

    <!-- App Bar -->
    <v-app-bar app dark color="primary" elevation="2">
      <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title class="font-weight-bold">إدارة التقييمات</v-toolbar-title>
      <v-spacer></v-spacer>
      
      <!-- Admin Account Menu -->
      <v-menu
        offset-y
        transition="slide-y-transition"
        bottom
        left
      >
        <template #activator="{ on, attrs }">
          <v-btn
            icon
            v-bind="attrs"
            v-on="on"
          >
            <v-avatar size="32" color="indigo lighten-4">
              <v-icon dark>mdi-account</v-icon>
            </v-avatar>
          </v-btn>
        </template>

        <v-card min-width="200">
          <v-list>
            <v-list-item>
              <v-list-item-avatar>
                <v-avatar color="indigo">
                  <v-icon dark>mdi-account</v-icon>
                </v-avatar>
              </v-list-item-avatar>
              
              <v-list-item-content>
                <v-list-item-title>{{ adminName }}</v-list-item-title>
                <v-list-item-subtitle>مدير النظام</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
            
            <v-divider></v-divider>
            
            <v-list-item link @click="logout">
              <v-list-item-icon>
                <v-icon>mdi-logout</v-icon>
              </v-list-item-icon>
              <v-list-item-title>تسجيل الخروج</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card>
      </v-menu>
    </v-app-bar>

    <!-- Main Content Area -->
    <v-main class="admin-main-content">
      <v-container fluid>
        <!-- Error Alert if API fails -->
        <v-alert
          v-if="error"
          type="error"
          dismissible
          class="mb-4"
          @input="error = ''"
        >
          {{ error }}
        </v-alert>

        <!-- Statistics Cards -->
        <v-row v-if="stats" class="mb-4">
          <v-col cols="12" md="3">
            <v-card class="elevation-2">
              <v-card-text class="text-center pa-4">
                <div class="text-h4 font-weight-bold primary--text">{{ stats.total }}</div>
                <div class="text-subtitle-1 grey--text text--darken-1">إجمالي التقييمات</div>
              </v-card-text>
            </v-card>
          </v-col>
          
          <v-col cols="12" md="3">
            <v-card class="elevation-2">
              <v-card-text class="text-center pa-4">
                <div class="text-h4 font-weight-bold amber--text text--darken-2">
                  {{ stats.average_rating }} / 5
                </div>
                <div class="text-subtitle-1 grey--text text--darken-1">متوسط التقييم</div>
                <v-rating
                  :value="stats.average_rating"
                  color="amber"
                  dense
                  half-increments
                  readonly
                  size="18"
                  class="mt-1"
                ></v-rating>
              </v-card-text>
            </v-card>
          </v-col>
          
          <v-col cols="12" md="6">
            <v-card class="elevation-2">
              <v-card-text class="pa-4">
                <div class="text-subtitle-1 grey--text text--darken-1 mb-2">توزيع التقييمات</div>
                <div v-for="i in 5" :key="i" class="d-flex align-center mb-1">
                  <div style="width: 40px">{{ i }} <v-icon small color="amber darken-2">mdi-star</v-icon></div>
                  <v-progress-linear
                    :value="stats.rating_distribution[i]?.percentage || 0"
                    height="8"
                    :color="getRatingColor(i)"
                    class="flex-grow-1 mx-2"
                  ></v-progress-linear>
                  <div style="width: 80px" class="text-right">
                    {{ stats.rating_distribution[i]?.count || 0 }}
                    <small class="grey--text">({{ stats.rating_distribution[i]?.percentage || 0 }}%)</small>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      
        <!-- Feedbacks Management Card -->
        <v-card class="mb-4">
          <v-card-title>
            <div class="d-flex flex-wrap align-center w-100">
              <h2 class="text-h5 font-weight-bold mr-4">إدارة التقييمات</h2>
              <v-spacer></v-spacer>
              
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="بحث"
                single-line
                hide-details
                outlined
                dense
                class="mx-2"
                style="max-width: 300px;"
                @input="debouncedSearch"
              ></v-text-field>
              
              <v-select
                v-model="ratingFilter"
                :items="ratingFilterOptions"
                label="تصفية حسب التقييم"
                outlined
                dense
                hide-details
                class="mx-2"
                style="max-width: 200px;"
                @change="loadFeedbacks(1)"
              ></v-select>
            </div>
          </v-card-title>
          
          <v-divider></v-divider>
          
          <v-data-table
            :headers="headers"
            :items="feedbacks"
            :loading="loading"
            loading-text="جاري تحميل التقييمات..."
            no-data-text="لا يوجد تقييمات متاحة"
            :footer-props="{
              'items-per-page-text': 'التقييمات في كل صفحة',
              'items-per-page-options': [5, 10, 15, 20, 50],
            }"
            :items-per-page="perPage"
            class="elevation-0"
            :server-items-length="totalFeedbacks"
            @update:options="handleTableOptions"
          >
            <!-- Rating column -->
            <template #[`item.rating`]="{ item }">
              <v-rating
                :value="item.rating"
                color="amber"
                dense
                half-increments
                readonly
                size="18"
              ></v-rating>
            </template>
            
            <!-- Business column -->
            <template #[`item.business`]="{ item }">
              <div v-if="item.business">
                <a 
                  class="link"
                  :href="`/Product/${item.business.id}`"
                  target="_blank"
                >
                  {{ item.business.name }}
                </a>
                <v-chip
                  x-small
                  color="primary"
                  class="mr-1"
                  v-if="item.business.category"
                >
                  {{ item.business.category.name_ar }}
                </v-chip>
              </div>
              <span v-else class="grey--text">غير متوفر</span>
            </template>
            
            <!-- User column -->
            <template #[`item.user`]="{ item }">
              <div v-if="item.user">
                {{ item.user.name }}
                <div class="caption grey--text">{{ item.user.email }}</div>
              </div>
              <span v-else class="grey--text">غير متوفر</span>
            </template>
            
            <!-- Comment column -->
            <template #[`item.comment`]="{ item }">
              <div class="comment-text">{{ item.comment || 'لا يوجد تعليق' }}</div>
            </template>
            
            <!-- Created at column -->
            <template #[`item.created_at`]="{ item }">
              <span class="time-since">{{ item.created_since || formatDate(item.created_at) }}</span>
            </template>
            
            <!-- Actions column -->
            <template #[`item.actions`]="{ item }">
              <v-btn
                icon
                small
                color="primary"
                class="mr-1"
                @click="viewFeedbackDetails(item)"
                title="عرض"
              >
                <v-icon small>mdi-eye</v-icon>
              </v-btn>
              
              <v-btn
                icon
                small
                color="error"
                @click="confirmDelete(item)"
                title="حذف"
              >
                <v-icon small>mdi-delete</v-icon>
              </v-btn>
            </template>
          </v-data-table>
          
          <!-- Pagination -->
          <div class="text-center pt-2 pb-2">
            <v-pagination
              v-model="page"
              :length="totalPages"
              :total-visible="7"
              @input="loadFeedbacks"
            ></v-pagination>
          </div>
        </v-card>
      </v-container>
    </v-main>
    
    <!-- Footer -->
    <v-footer app color="primary darken-2" dark class="admin-footer py-2" :inset="true">
      <div class="text-center w-100">
        <div class="text-caption">
          BizAdvisor Admin Dashboard &copy; {{ new Date().getFullYear() }}
        </div>
      </div>
    </v-footer>
    
    <!-- Feedback Details Dialog -->
    <v-dialog v-model="feedbackDialog" max-width="600">
      <v-card v-if="selectedFeedback">
        <v-card-title class="headline teal white--text">
          تفاصيل التقييم
          <v-spacer></v-spacer>
          <v-btn icon dark @click="feedbackDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pa-4">
          <v-row>
            <v-col cols="12">
              <v-rating
                :value="selectedFeedback.rating"
                color="amber"
                half-increments
                readonly
                size="32"
                class="mb-2"
              ></v-rating>
              <div class="text-body-1 mb-4">
                {{ selectedFeedback.comment || 'لا يوجد تعليق' }}
              </div>
              
              <div class="detail-item">
                <div class="detail-label">التاريخ</div>
                <div class="detail-value">{{ formatDate(selectedFeedback.created_at) }}</div>
              </div>
            </v-col>
            
            <v-col cols="12">
              <v-divider class="my-3"></v-divider>
              <div class="detail-label mb-2">صاحب التقييم</div>
              
              <v-list-item v-if="selectedFeedback.user" two-line class="px-0">
                <v-list-item-avatar size="40" color="indigo">
                  <v-icon dark>mdi-account</v-icon>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title>{{ selectedFeedback.user.name }}</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedFeedback.user.email }}</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-col>
            
            <v-col cols="12">
              <v-divider class="my-3"></v-divider>
              <div class="detail-label mb-2">العمل التجاري</div>
              
              <v-list-item v-if="selectedFeedback.business" two-line class="px-0">
                <v-list-item-avatar size="40" color="primary">
                  <v-icon dark>mdi-store</v-icon>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title>{{ selectedFeedback.business.name }}</v-list-item-title>
                  <v-list-item-subtitle v-if="selectedFeedback.business.category">
                    {{ selectedFeedback.business.category.name_ar }}
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-col>
          </v-row>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="error"
            text
            @click="confirmDelete(selectedFeedback)"
          >
            <v-icon small left>mdi-delete</v-icon>
            حذف التقييم
          </v-btn>
          <v-btn
            color="grey darken-1"
            text
            @click="feedbackDialog = false"
          >
            إغلاق
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="headline error white--text">
          تأكيد الحذف
        </v-card-title>
        
        <v-card-text class="py-4">
          <p>هل أنت متأكد من رغبتك في حذف هذا التقييم؟</p>
          <p class="mb-0 text-body-2 warning--text">
            <v-icon small color="warning">mdi-alert</v-icon>
            هذا الإجراء لا يمكن التراجع عنه.
          </p>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey darken-1"
            text
            @click="deleteDialog = false"
          >
            إلغاء
          </v-btn>
          <v-btn
            color="error"
            :loading="deleting"
            :disabled="deleting"
            @click="deleteFeedback"
          >
            تأكيد الحذف
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Snackbar for notifications -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="snackbar.timeout"
      bottom
      right
    >
      {{ snackbar.text }}
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
  </v-app>
</template>

<script>
import api from '@/services/api';
import { debounce } from 'lodash';
import AdminSidebar from "@/components/admin/AdminSidebar.vue";

export default {
  name: 'AdminFeedbacks',
  components: {
    AdminSidebar
  },
  data() {
    return {
      drawer: true,
      miniVariant: false,
      loading: false,
      error: '',
      adminName: 'المدير',
      search: '',
      page: 1,
      perPage: 10,
      totalFeedbacks: 0,
      totalPages: 0,
      feedbacks: [],
      ratingFilter: '',
      ratingFilterOptions: [
        { text: 'الكل', value: '' },
        { text: '5 نجوم', value: '5' },
        { text: '4 نجوم', value: '4' },
        { text: '3 نجوم', value: '3' },
        { text: '2 نجوم', value: '2' },
        { text: '1 نجمة', value: '1' }
      ],
      headers: [
        { text: 'التقييم', value: 'rating', align: 'center' },
        { text: 'العمل التجاري', value: 'business', align: 'start' },
        { text: 'المستخدم', value: 'user' },
        { text: 'التعليق', value: 'comment' },
        { text: 'تاريخ الإنشاء', value: 'created_at' },
        { text: 'الإجراءات', value: 'actions', sortable: false, align: 'center' }
      ],
      menuItems: [
        { title: 'لوحة التحكم', icon: 'mdi-view-dashboard', route: '/admin/dashboard', exact: true },
        { title: 'المستخدمين', icon: 'mdi-account-group', route: '/admin/users', exact: false },
        { title: 'الأعمال', icon: 'mdi-store', route: '/admin/businesses', exact: false },
        { title: 'التصنيفات', icon: 'mdi-shape', route: '/admin/categories', exact: false },
        { title: 'التقييمات', icon: 'mdi-comment-text-multiple', route: '/admin/feedbacks', exact: false },
      ],
      sortBy: 'created_at',
      sortDesc: true,
      
      // Statistics
      stats: null,
      
      // Feedback details dialog
      feedbackDialog: false,
      selectedFeedback: null,
      
      // Delete dialog
      deleteDialog: false,
      deleting: false,
      
      // Snackbar
      snackbar: {
        show: false,
        color: '',
        text: '',
        timeout: 3000
      }
    };
  },
  created() {
    // Check if admin is logged in
    const adminToken = localStorage.getItem('admin_token');
    const adminUser = localStorage.getItem('admin_user');
    
    if (!adminToken) {
      this.$router.push('/admin/login');
      return;
    }
    
    // Set auth header
    api.defaults.headers.common['Authorization'] = `Bearer ${adminToken}`;
    
    // Set admin name
    if (adminUser) {
      try {
        const user = JSON.parse(adminUser);
        this.adminName = user.name || 'المدير';
      } catch (e) {
        console.error('Error parsing admin user data:', e);
      }
    }
    
    // Load feedback stats
    this.loadStats();
    
    // Load initial feedbacks
    this.loadFeedbacks(1);
    
    // Initialize debounced search
    this.debouncedSearch = debounce(() => {
      this.loadFeedbacks(1);
    }, 500);
  },
  mounted() {
    // Hide header and footer by adding admin-mode class
    document.body.classList.add('admin-mode');
    
    // Hide any header that might be visible
    const header = document.querySelector('.transparent-header');
    if (header) header.style.display = 'none';
    
    // Hide any footer that might be visible
    const footerElements = document.querySelectorAll('footer');
    footerElements.forEach(footer => {
      if (footer) footer.style.display = 'none';
    });
  },
  beforeDestroy() {
    // Remove admin-mode class when leaving
    document.body.classList.remove('admin-mode');
  },
  methods: {
    async loadStats() {
      try {
        const response = await api.get('/admin/feedbacks-stats');
        
        if (response.data.status === 'success') {
          this.stats = response.data.stats;
        }
      } catch (error) {
        console.error('Error loading feedback stats:', error);
        // Don't show error for stats, it's not critical
      }
    },
    
    async loadFeedbacks(page = this.page) {
      this.loading = true;
      this.error = '';
      this.page = page;
      
      try {
        // Create query params
        const params = {
          page: this.page,
          per_page: this.perPage,
          sort_by: this.sortBy,
          sort_order: this.sortDesc ? 'desc' : 'asc'
        };
        
        // Add search if present
        if (this.search) {
          params.search = this.search;
        }
        
        // Add rating filter if present
        if (this.ratingFilter) {
          params.rating = this.ratingFilter;
        }
        
        const response = await api.get('/admin/feedbacks', { params });
        
        if (response.data.status === 'success') {
          this.feedbacks = response.data.feedbacks;
          this.totalFeedbacks = response.data.pagination.total;
          this.totalPages = response.data.pagination.last_page;
        } else {
          this.error = 'فشل في تحميل التقييمات: ' + (response.data.message || 'خطأ غير معروف');
        }
      } catch (error) {
        console.error('Error loading feedbacks:', error);
        this.error = 'حدث خطأ أثناء تحميل التقييمات. يرجى المحاولة مرة أخرى.';
        
        if (error.response && error.response.status === 401) {
          // Token expired or invalid
          this.logout();
        }
      } finally {
        this.loading = false;
      }
    },
    
    handleTableOptions({ page, itemsPerPage, sortBy, sortDesc }) {
      // Update pagination
      if (page !== this.page) {
        this.page = page;
      }
      
      if (itemsPerPage !== this.perPage) {
        this.perPage = itemsPerPage;
        this.page = 1; // Reset to first page when changing items per page
      }
      
      // Update sorting if changed
      if (sortBy.length > 0 && sortBy[0] !== this.sortBy) {
        this.sortBy = sortBy[0];
        this.sortDesc = sortDesc[0];
        this.page = 1; // Reset to first page when changing sort
      } else if (sortBy.length > 0 && sortDesc[0] !== this.sortDesc) {
        this.sortDesc = sortDesc[0];
        this.page = 1; // Reset to first page when changing sort direction
      }
      
      // Load feedbacks with new options
      this.loadFeedbacks();
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Intl.DateTimeFormat('ar-SA', options).format(date);
    },
    
    getRatingColor(rating) {
      const colors = {
        1: 'error',
        2: 'warning darken-2',
        3: 'amber darken-1',
        4: 'success lighten-1',
        5: 'success'
      };
      
      return colors[rating] || 'grey';
    },
    
    viewFeedbackDetails(feedback) {
      // Load full feedback details from API
      this.loading = true;
      api.get(`/admin/feedbacks/${feedback.id}`)
        .then(response => {
          if (response.data.status === 'success') {
            this.selectedFeedback = response.data.feedback;
            this.feedbackDialog = true;
          } else {
            this.error = 'فشل في تحميل تفاصيل التقييم: ' + (response.data.message || 'خطأ غير معروف');
          }
        })
        .catch(error => {
          console.error('Error loading feedback details:', error);
          this.error = 'حدث خطأ أثناء تحميل تفاصيل التقييم. يرجى المحاولة مرة أخرى.';
        })
        .finally(() => {
          this.loading = false;
        });
    },
    
    confirmDelete(feedback) {
      this.selectedFeedback = feedback;
      this.deleteDialog = true;
    },
    
    async deleteFeedback() {
      if (!this.selectedFeedback) return;
      
      this.deleting = true;
      
      try {
        const response = await api.delete(`/admin/feedbacks/${this.selectedFeedback.id}`);
        
        if (response.data.status === 'success') {
          // Show success message
          this.snackbar = {
            show: true,
            color: 'success',
            text: 'تم حذف التقييم بنجاح',
            timeout: 3000
          };
          
          // Close the dialogs
          this.deleteDialog = false;
          this.feedbackDialog = false;
          
          // Refresh feedbacks list
          this.loadFeedbacks();
          
          // Reload stats
          this.loadStats();
        }
      } catch (error) {
        console.error('Error deleting feedback:', error);
        this.error = error.response?.data?.message || 'حدث خطأ أثناء حذف التقييم. يرجى المحاولة مرة أخرى.';
        
        // Close the dialog
        this.deleteDialog = false;
      } finally {
        this.deleting = false;
      }
    },
    
    async logout() {
      try {
        await api.post('/admin/logout');
      } catch (error) {
        console.error('Error during logout:', error);
      } finally {
        // Always clear local storage and redirect
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        delete api.defaults.headers.common['Authorization'];
        
        // Remove admin-mode class before redirecting
        document.body.classList.remove('admin-mode');
        
        this.$router.push('/admin/login');
      }
    }
  }
};
</script>

<style scoped>
.admin-sidebar {
  background: linear-gradient(to bottom, #1e3c72, #2a5298);
}

.admin-header {
  padding-top: 12px;
  padding-bottom: 12px;
}

.admin-main-content {
  background-color: #f5f7fa;
}

.logout-item {
  border-top: 1px solid rgba(255, 255, 255, 0.12);
}

.admin-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.12);
}

.detail-item {
  margin-bottom: 12px;
}

.detail-label {
  font-size: 0.8rem;
  color: #757575;
  margin-bottom: 4px;
}

.detail-value {
  font-weight: 500;
}

.time-since {
  color: #757575;
  font-size: 0.8rem;
  font-style: italic;
}

.link {
  color: #1976d2;
  cursor: pointer;
  text-decoration: none;
}

.link:hover {
  text-decoration: underline;
}

/* Truncate comment text in table */
.comment-text {
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Add spacing between avatar and content */
.v-list-item__avatar {
  margin-right: 16px !important;
}

/* Additional selectors for spacing */
.v-list-item {
  padding-right: 8px !important;
}

.v-list-item .v-list-item__content {
  padding-right: 8px !important;
  margin-right: 8px !important;
}
</style> 