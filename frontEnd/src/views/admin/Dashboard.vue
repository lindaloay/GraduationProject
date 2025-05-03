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
      <v-toolbar-title class="font-weight-bold">لوحة تحكم الإدارة</v-toolbar-title>
      <v-spacer></v-spacer>
      
      <!-- Admin Account Menu -->
      <v-menu
        offset-y
        transition="slide-y-transition"
        bottom
        left
      >
        <template v-slot:activator="{ on, attrs }">
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
      
        <!-- Welcome Card -->
        <v-row>
          <v-col cols="12">
            <v-card class="mb-4 welcome-card">
              <v-card-text>
                <div class="d-flex align-center">
                  <div>
                    <h2 class="text-h4 font-weight-bold primary--text">
                      مرحباً، {{ adminName }}!
                    </h2>
                    <p class="text-subtitle-1 mt-1 mb-0 grey--text text--darken-1">
                      هذه لوحة تحكم للإدارة. يمكنك إدارة الأعمال والمستخدمين والفئات من هنا.
                    </p>
                  </div>
                  <v-spacer></v-spacer>
                  <v-img
                    src="/admin/dashboard-illustration.svg"
                    max-width="150"
                    contain
                    class="d-none d-md-flex"
                  ></v-img>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
            
        <!-- Statistics Cards -->
        <v-row>
          <v-col
            v-for="(stat, index) in statistics"
            :key="index"
            cols="12"
            sm="6"
            md="3"
          >
            <v-card
              :color="stat.color"
              dark
              class="stat-card"
              elevation="2"
            >
              <v-card-text class="pa-4">
                <div class="d-flex flex-no-wrap justify-space-between">
                  <div>
                    <v-skeleton-loader
                      v-if="loading"
                      type="text"
                      width="60"
                    ></v-skeleton-loader>
                    <p v-else class="text-h4 font-weight-bold mb-1">
                      {{ stat.value }}
                    </p>
                    <p class="text-subtitle-1 mb-0">{{ stat.title }}</p>
                  </div>
                  <v-avatar
                    class="stat-icon-background"
                    size="56"
                  >
                    <v-icon size="32">{{ stat.icon }}</v-icon>
                  </v-avatar>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        
        <!-- Recent Data Section -->
        <v-row class="mt-4">
          <v-col cols="12" lg="6">
            <v-card class="mb-4">
              <v-card-title class="d-flex align-center">
                <v-icon left color="primary">mdi-store</v-icon>
                <span>أحدث الأعمال المضافة</span>
                <v-spacer></v-spacer>
                <v-btn small text color="primary" to="/admin/businesses">
                  عرض الكل
                  <v-icon small left>mdi-arrow-right</v-icon>
                </v-btn>
              </v-card-title>
              
              <v-divider></v-divider>
              
              <v-card-text v-if="loading" class="text-center py-5">
                <v-progress-circular
                  indeterminate
                  color="primary"
                ></v-progress-circular>
                <p class="mt-3 mb-0">جاري تحميل البيانات...</p>
              </v-card-text>
              
              <v-list v-else two-line>
                <v-list-item v-if="recentData.businesses && recentData.businesses.length === 0">
                  <v-list-item-content>
                    <v-list-item-title>لا توجد أعمال حتى الآن</v-list-item-title>
                    <v-list-item-subtitle>ستظهر الأعمال المضافة هنا</v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
                
                <template v-else>
                  <v-list-item
                    v-for="(business, index) in formattedBusinesses"
                    :key="index"
                  >
                    <v-list-item-avatar>
                      <v-avatar color="primary lighten-3" size="48">
                        <v-img 
                          v-if="business.main_picture_url" 
                          :src="business.main_picture_url"
                          alt="Business Image"
                        ></v-img>
                        <v-icon v-else dark>mdi-store</v-icon>
                      </v-avatar>
                    </v-list-item-avatar>
                    <v-list-item-content>
                      <v-list-item-title>{{ business.name }}</v-list-item-title>
                      <v-list-item-subtitle>
                        {{ business.category ? business.category.name_ar : 'بدون تصنيف' }} • 
                        <span class="time-since">{{ business.timeAgo }}</span>
                      </v-list-item-subtitle>
                    </v-list-item-content>
                    
                    <v-list-item-action>
                      <v-btn
                        icon
                        small
                        @click="viewBusinessDetails(business)"
                      >
                        <v-icon small>mdi-eye</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </template>
              </v-list>
            </v-card>
          </v-col>
          
          <v-col cols="12" lg="6">
            <v-card class="mb-4">
              <v-card-title class="d-flex align-center">
                <v-icon left color="deep-purple">mdi-account-group</v-icon>
                <span>أحدث المستخدمين المسجلين</span>
                <v-spacer></v-spacer>
                <v-btn small text color="deep-purple" to="/admin/users">
                  عرض الكل
                  <v-icon small left>mdi-arrow-right</v-icon>
                </v-btn>
              </v-card-title>
              
              <v-divider></v-divider>
              
              <v-card-text v-if="loading" class="text-center py-5">
                <v-progress-circular
                  indeterminate
                  color="deep-purple"
                ></v-progress-circular>
                <p class="mt-3 mb-0">جاري تحميل البيانات...</p>
              </v-card-text>
              
              <v-list v-else two-line>
                <v-list-item v-if="recentData.users && recentData.users.length === 0">
                  <v-list-item-content>
                    <v-list-item-title>لا يوجد مستخدمين حتى الآن</v-list-item-title>
                    <v-list-item-subtitle>ستظهر المستخدمين المضافين هنا</v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
                
                <template v-else>
                  <v-list-item
                    v-for="(user, index) in formattedUsers"
                    :key="index"
                  >
                    <v-list-item-avatar>
                      <v-avatar color="deep-purple lighten-3">
                        <v-icon dark>{{ user.is_business_owner ? 'mdi-briefcase' : 'mdi-account' }}</v-icon>
                      </v-avatar>
                    </v-list-item-avatar>
                    
                    <v-list-item-content>
                      <v-list-item-title>{{ user.name }}</v-list-item-title>
                      <v-list-item-subtitle>
                        {{ user.email }} • 
                        {{ user.is_business_owner ? 'صاحب عمل' : user.is_admin ? 'مدير النظام' : 'مستخدم عادي' }} •
                        <span class="time-since">{{ user.timeAgo }}</span>
                      </v-list-item-subtitle>
                    </v-list-item-content>
                    
                    <v-list-item-action>
                      <v-btn
                        icon
                        small
                        @click="viewUserDetails(user)"
                      >
                        <v-icon small>mdi-eye</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </template>
              </v-list>
            </v-card>
          </v-col>
        </v-row>
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
    
    <!-- User Details Dialog -->
    <v-dialog v-model="userDialog" max-width="700">
      <v-card v-if="selectedUser">
        <v-card-title class="headline deep-purple white--text">
          تفاصيل المستخدم
          <v-spacer></v-spacer>
          <v-btn icon dark @click="userDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pa-4">
          <v-row>
            <v-col cols="12" sm="6">
              <v-list-item two-line>
                <v-list-item-avatar size="48" color="indigo">
                  <v-icon dark>{{ selectedUser.is_business_owner ? 'mdi-briefcase' : 'mdi-account' }}</v-icon>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title class="text-h6">{{ selectedUser.name }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ selectedUser.is_business_owner ? 'صاحب عمل' : selectedUser.is_admin ? 'مدير النظام' : 'مستخدم عادي' }}
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-col>
            
            <v-col cols="12" sm="6">
              <div class="detail-item">
                <div class="detail-label">البريد الإلكتروني</div>
                <div class="detail-value">{{ selectedUser.email }}</div>
              </div>
              
              <div class="detail-item" v-if="selectedUser.phone">
                <div class="detail-label">رقم الهاتف</div>
                <div class="detail-value">{{ selectedUser.phone }}</div>
              </div>

              <div class="detail-item" v-if="selectedUser.address">
                <div class="detail-label">العنوان</div>
                <div class="detail-value">{{ selectedUser.address }}</div>
              </div>
              
              <div class="detail-item" v-if="selectedUser.city || selectedUser.country">
                <div class="detail-label">المدينة / الدولة</div>
                <div class="detail-value">
                  {{ [selectedUser.city, selectedUser.country].filter(Boolean).join(', ') }}
                </div>
              </div>
              
              <div class="detail-item">
                <div class="detail-label">تاريخ التسجيل</div>
                <div class="detail-value">{{ formatDate(selectedUser.created_at) }}</div>
              </div>
            </v-col>
          </v-row>
          
          <v-divider class="my-4"></v-divider>
          
          <!-- User's Businesses (if business owner) -->
          <div v-if="selectedUser.is_business_owner && selectedUser.businesses && selectedUser.businesses.length > 0">
            <h3 class="text-subtitle-1 font-weight-bold mb-3">الأعمال التجارية المسجلة</h3>
            
            <v-simple-table>
              <template #default>
                <thead>
                  <tr>
                    <th class="text-right">الاسم</th>
                    <th class="text-right">البريد الإلكتروني</th>
                    <th class="text-right">الهاتف</th>
                    <th class="text-right">التصنيف</th>
                    <th class="text-right">تاريخ الإنشاء</th>
                    <th class="text-right">الإجراءات</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(business, index) in selectedUser.businesses" :key="index">
                    <td>{{ business.name }}</td>
                    <td>{{ business.email }}</td>
                    <td>{{ business.phone || 'غير متوفر' }}</td>
                    <td>{{ business.category ? business.category.name_ar : 'غير مصنف' }}</td>
                    <td>{{ business.created_since || formatDate(business.created_at) }}</td>
                    <td>
                      <v-btn
                        icon
                        x-small
                        color="primary"
                        @click="viewBusinessDetails(business)"
                      >
                        <v-icon x-small>mdi-eye</v-icon>
                      </v-btn>
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </div>
          
          <div v-else-if="selectedUser.is_business_owner" class="text-center my-4 grey--text">
            لا يوجد أعمال تجارية مسجلة لهذا المستخدم
          </div>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="amber darken-2"
            text
            @click="editUserDetails"
            class="mr-2"
          >
            <v-icon small left>mdi-pencil</v-icon>
            تعديل
          </v-btn>
          <v-btn
            color="grey darken-1"
            text
            @click="userDialog = false"
          >
            إغلاق
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Business Details Dialog -->
    <v-dialog v-model="businessDialog" max-width="800">
      <v-card v-if="selectedBusiness">
        <v-card-title class="headline primary white--text">
          تفاصيل العمل التجاري
          <v-spacer></v-spacer>
          <v-btn icon dark @click="businessDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pa-4">
          <v-row>
            <v-col cols="12" sm="4">
              <v-img
                v-if="selectedBusiness.main_picture_url"
                :src="selectedBusiness.main_picture_url"
                :alt="selectedBusiness.name"
                height="200"
                class="rounded-lg"
                contain
              ></v-img>
              <v-sheet
                v-else
                height="200"
                color="grey lighten-2"
                class="d-flex align-center justify-center rounded-lg"
              >
                <v-icon size="64" color="grey darken-2">mdi-image</v-icon>
              </v-sheet>
            </v-col>
            
            <v-col cols="12" sm="8">
              <h3 class="text-h5 mb-2">{{ selectedBusiness.name }}</h3>
              
              <div class="mb-4">
                <v-chip
                  small
                  color="primary"
                  class="mr-2"
                  v-if="selectedBusiness.category"
                >
                  {{ selectedBusiness.category.name_ar }}
                </v-chip>
                <v-chip
                  small
                  color="success"
                  v-if="selectedBusiness.rating"
                >
                  <v-icon small left right>mdi-star</v-icon>
                  {{ selectedBusiness.rating ? selectedBusiness.rating.toFixed(1) : '0.0' }} ({{ selectedBusiness.rating_count || 0 }})
                </v-chip>
              </div>
              
              <v-row dense>
                <v-col cols="12" sm="6">
                  <div class="detail-item">
                    <div class="detail-label">البريد الإلكتروني</div>
                    <div class="detail-value">{{ selectedBusiness.email || 'غير متوفر' }}</div>
                  </div>
                </v-col>
                
                <v-col cols="12" sm="6">
                  <div class="detail-item">
                    <div class="detail-label">رقم الهاتف</div>
                    <div class="detail-value">{{ selectedBusiness.phone || 'غير متوفر' }}</div>
                  </div>
                </v-col>
                
                <v-col cols="12" sm="6">
                  <div class="detail-item">
                    <div class="detail-label">الموقع الإلكتروني</div>
                    <div class="detail-value">
                      <a v-if="selectedBusiness.website" :href="selectedBusiness.website" target="_blank">{{ selectedBusiness.website }}</a>
                      <span v-else class="grey--text">غير متوفر</span>
                    </div>
                  </div>
                </v-col>
                
                <v-col cols="12" sm="6">
                  <div class="detail-item">
                    <div class="detail-label">المدينة</div>
                    <div class="detail-value">
                      {{ selectedBusiness.city || (selectedBusiness.user && selectedBusiness.user.city) || 'غير متوفر' }}
                    </div>
                  </div>
                </v-col>
                
                <v-col cols="12" sm="6">
                  <div class="detail-item">
                    <div class="detail-label">تاريخ الإنشاء</div>
                    <div class="detail-value">{{ formatDate(selectedBusiness.created_at) }}</div>
                  </div>
                </v-col>
                
                <v-col cols="12" sm="6">
                  <div class="detail-item">
                    <div class="detail-label">صاحب العمل</div>
                    <div class="detail-value">
                      <a 
                        v-if="selectedBusiness.user" 
                        @click="viewUserDetails(selectedBusiness.user)"
                        class="link"
                      >
                        {{ selectedBusiness.user.name }}
                      </a>
                      <span v-else class="grey--text">غير متوفر</span>
                    </div>
                  </div>
                </v-col>
              </v-row>
            </v-col>
            
            <v-col cols="12">
              <v-divider class="my-3"></v-divider>
              <div class="detail-item">
                <div class="detail-label">الوصف</div>
                <div class="detail-value">{{ selectedBusiness.description || 'لا يوجد وصف متاح' }}</div>
              </div>
            </v-col>
            
            <v-col cols="12" v-if="selectedBusiness.gallery_pictures_urls && selectedBusiness.gallery_pictures_urls.length">
              <v-divider class="my-3"></v-divider>
              <div class="detail-label mb-2">معرض الصور</div>
              <v-row>
                <v-col
                  v-for="(image, index) in selectedBusiness.gallery_pictures_urls"
                  :key="index"
                  cols="6"
                  sm="4"
                  md="3"
                >
                  <v-img
                    :src="image"
                    aspect-ratio="1"
                    class="rounded-lg"
                    cover
                  ></v-img>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            text
            :href="`/Product/${selectedBusiness.id}`"
            target="_blank"
          >
            <v-icon small left>mdi-open-in-new</v-icon>
            عرض في الموقع
          </v-btn>
         
          <v-btn
            color="grey darken-1"
            text
            @click="businessDialog = false"
          >
            إغلاق
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Edit User Dialog -->
    <v-dialog v-model="editDialog" max-width="600">
      <v-card v-if="selectedUser">
        <v-card-title class="headline primary white--text">
          تعديل معلومات المستخدم
          <v-spacer></v-spacer>
          <v-btn icon dark @click="editDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pt-4">
          <v-form ref="editForm" v-model="editFormValid" @submit.prevent="updateUser">
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    v-model="editedUser.name"
                    label="الاسم"
                    required
                    outlined
                    :rules="[v => !!v || 'الاسم مطلوب']"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12">
                  <v-text-field
                    v-model="editedUser.email"
                    label="البريد الإلكتروني"
                    type="email"
                    required
                    outlined
                    :rules="[
                      v => !!v || 'البريد الإلكتروني مطلوب',
                      v => /.+@.+\..+/.test(v) || 'البريد الإلكتروني غير صحيح'
                    ]"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12">
                  <v-text-field
                    v-model="editedUser.phone"
                    label="رقم الهاتف"
                    outlined
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12">
                  <v-text-field
                    v-model="editedUser.address"
                    label="العنوان"
                    outlined
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedUser.city"
                    label="المدينة"
                    outlined
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedUser.country"
                    label="الدولة"
                    outlined
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey darken-1"
            text
            @click="editDialog = false"
          >
            إلغاء
          </v-btn>
          <v-btn
            color="primary"
            :loading="updating"
            :disabled="!editFormValid || updating"
            @click="updateUser"
          >
            حفظ التغييرات
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
import AdminSidebar from "@/components/admin/AdminSidebar.vue";

export default {
  name: 'AdminDashboard',
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
      recentData: {
        businesses: [],
        users: [],
        feedbacks: []
      },
      statistics: [
        { title: 'المستخدمين', value: 0, icon: 'mdi-account-group', color: 'deep-purple' },
        { title: 'الأعمال', value: 0, icon: 'mdi-store', color: 'primary' },
        { title: 'التقييمات', value: 0, icon: 'mdi-comment-text-multiple', color: 'teal' },
        { title: 'التصنيفات', value: 0, icon: 'mdi-shape', color: 'amber darken-2' }
      ],
      userDialog: false,
      selectedUser: null,
      editDialog: false,
      editFormValid: true,
      updating: false,
      editedUser: {
        name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        country: ''
      },
      snackbar: {
        show: false,
        color: '',
        text: '',
        timeout: 3000
      },
      businessDialog: false,
      selectedBusiness: null
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
    
    // Load dashboard data
    this.loadDashboardData();
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
    async loadDashboardData() {
      this.loading = true;
      this.error = '';
      
      try {
        const response = await api.get('/admin/dashboard');
        
        if (response.data.status === 'success') {
          // Update statistics
          const stats = response.data.statistics;
          
          this.statistics[0].value = stats.totalUsers;
          this.statistics[1].value = stats.totalBusinesses;
          this.statistics[2].value = stats.totalFeedbacks;
          this.statistics[3].value = stats.totalCategories;
          
          // Update recent data
          this.recentData = response.data.recentData;
          
          console.log('Dashboard data loaded successfully');
        } else {
          this.error = 'Failed to load dashboard data: ' + (response.data.message || 'Unknown error');
        }
      } catch (error) {
        console.error('Error loading dashboard data:', error);
        this.error = 'An error occurred while loading dashboard data. Please try again later.';
        
        if (error.response && error.response.status === 401) {
          // Token expired or invalid
          this.logout();
        }
      } finally {
        this.loading = false;
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
    },
    formatDate(dateString) {
      if (!dateString) return '';
      
      // Try to calculate time since (1 day ago, 2 hours ago, etc.)
      const date = new Date(dateString);
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);
      
      if (diffInSeconds < 60) {
        return 'منذ لحظات';
      } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `منذ ${minutes} ${minutes === 1 ? 'دقيقة' : 'دقائق'}`;
      } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `منذ ${hours} ${hours === 1 ? 'ساعة' : 'ساعات'}`;
      } else if (diffInSeconds < 2592000) {
        const days = Math.floor(diffInSeconds / 86400);
        return `منذ ${days} ${days === 1 ? 'يوم' : 'أيام'}`;
      } else if (diffInSeconds < 31536000) {
        const months = Math.floor(diffInSeconds / 2592000);
        return `منذ ${months} ${months === 1 ? 'شهر' : 'أشهر'}`;
      } else {
        const years = Math.floor(diffInSeconds / 31536000);
        return `منذ ${years} ${years === 1 ? 'سنة' : 'سنوات'}`;
      }
    },
    async viewUserDetails(user) {
      try {
        this.loading = true;
        const response = await api.get(`/admin/users/${user.id}`);
        
        if (response.data && response.data.status === 'success') {
          this.selectedUser = response.data.user;
          this.userDialog = true;
        } else {
          this.error = 'Failed to load user details: ' + (response.data.message || 'Unknown error');
        }
      } catch (error) {
        console.error('Error loading user details:', error);
        this.error = 'حدث خطأ أثناء تحميل تفاصيل المستخدم. يرجى المحاولة مرة أخرى.';
      } finally {
        this.loading = false;
      }
    },
    editUserDetails() {
      this.editedUser = {
        name: this.selectedUser.name,
        email: this.selectedUser.email,
        phone: this.selectedUser.phone || '',
        address: this.selectedUser.address || '',
        city: this.selectedUser.city || '',
        country: this.selectedUser.country || ''
      };
      this.userDialog = false;
      this.editDialog = true;
    },
    async updateUser() {
      try {
        this.updating = true;
        const response = await api.put(`/admin/users/${this.selectedUser.id}`, this.editedUser);
        
        if (response.data && response.data.status === 'success') {
          // Update the user in the local data
          this.selectedUser = response.data.user;
          
          // Show success message
          this.snackbar = {
            show: true,
            color: 'success',
            text: response.data.message || 'تم تحديث بيانات المستخدم بنجاح',
            timeout: 3000
          };
          
          // Close the dialog
          this.editDialog = false;
          
          // Refresh the dashboard data
          this.loadDashboardData();
        } else {
          this.error = 'Failed to update user: ' + (response.data.message || 'Unknown error');
        }
      } catch (error) {
        console.error('Error updating user:', error);
        this.error = error.response?.data?.message || 'حدث خطأ أثناء تحديث بيانات المستخدم. يرجى المحاولة مرة أخرى.';
      } finally {
        this.updating = false;
      }
    },
    async viewBusinessDetails(business) {
      try {
        this.loading = true;
        // Handle being passed either a business object or just the ID
        const businessId = business.id || business;
        const response = await api.get(`/admin/businesses/${businessId}`);
        
        if (response.data && response.data.status === 'success') {
          this.selectedBusiness = response.data.business;
          this.businessDialog = true;
        } else {
          this.error = 'فشل في تحميل تفاصيل العمل التجاري: ' + (response.data.message || 'خطأ غير معروف');
        }
      } catch (error) {
        console.error('Error loading business details:', error);
        this.error = 'حدث خطأ أثناء تحميل تفاصيل العمل التجاري. يرجى المحاولة مرة أخرى.';
      } finally {
        this.loading = false;
      }
    }
  },
  computed: {
    formattedBusinesses() {
      return (this.recentData.businesses || []).map(business => {
        return {
          ...business,
          // Use created_since from API if available, otherwise format locally
          timeAgo: business.created_since || this.formatDate(business.created_at)
        };
      });
    },
    formattedUsers() {
      return (this.recentData.users || []).map(user => {
        return {
          ...user,
          timeAgo: this.formatDate(user.created_at)
        };
      });
    }
  }
};
</script>

<style scoped>
.admin-main-content {
  background-color: #f5f7fa;
}

.welcome-card {
  background: linear-gradient(to right, #ffffff, #f5f7fa);
  border-left: 5px solid #1976d2;
}

.stat-card {
  border-radius: 8px;
  transition: transform 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon-background {
  background-color: rgba(255, 255, 255, 0.2);
}

.admin-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.12);
}

.time-since {
  color: #757575;
  font-size: 0.8rem;
  font-style: italic;
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
</style> 