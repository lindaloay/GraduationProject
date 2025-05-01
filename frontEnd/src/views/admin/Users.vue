<template>
  <v-app>
    <!-- Admin Navigation Drawer -->
    <v-navigation-drawer
      v-model="drawer"
      app
      dark
      color="primary"
      class="admin-sidebar"
      :mini-variant="miniVariant"
    >
      <v-list-item class="px-2 admin-header">
        <v-list-item-avatar>
          <v-icon size="40">mdi-shield-account</v-icon>
        </v-list-item-avatar>
        <v-list-item-content>
          <v-list-item-title class="text-h6 font-weight-bold">
            BizAdvisor
          </v-list-item-title>
          <v-list-item-subtitle>
            لوحة تحكم الإدارة
          </v-list-item-subtitle>
        </v-list-item-content>
        <v-btn
          icon
          @click.stop="miniVariant = !miniVariant"
        >
          <v-icon>{{ miniVariant ? 'mdi-chevron-right' : 'mdi-chevron-left' }}</v-icon>
        </v-btn>
      </v-list-item>

      <v-divider></v-divider>

      <v-list dense nav>
        <v-list-item
          v-for="(item, i) in menuItems"
          :key="i"
          :to="item.route"
          link
          :exact="item.exact"
        >
          <v-list-item-icon>
            <v-icon>{{ item.icon }}</v-icon>
          </v-list-item-icon>
          <v-list-item-content>
            <v-list-item-title>{{ item.title }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </v-list>
      
      <template #append>
        <v-divider></v-divider>
        <v-list dense>
          <v-list-item link @click="logout" class="logout-item">
            <v-list-item-icon>
              <v-icon>mdi-logout</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>تسجيل الخروج</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </template>
    </v-navigation-drawer>

    <!-- App Bar -->
    <v-app-bar app dark color="primary" elevation="2">
      <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title class="font-weight-bold">إدارة المستخدمين</v-toolbar-title>
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
      
        <!-- Users Management Card -->
        <v-card class="mb-4">
          <v-card-title>
            <div class="d-flex flex-wrap align-center w-100">
              <h2 class="text-h5 font-weight-bold mr-4">إدارة المستخدمين</h2>
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
                v-model="roleFilter"
                :items="roleOptions"
                label="تصفية حسب الدور"
                outlined
                dense
                hide-details
                class="mx-2"
                style="max-width: 180px;"
                @change="loadUsers(1)"
              ></v-select>
            </div>
          </v-card-title>
          
          <v-divider></v-divider>
          
          <v-data-table
            :headers="headers"
            :items="users"
            :loading="loading"
            loading-text="جاري تحميل المستخدمين..."
            no-data-text="لا يوجد مستخدمين متاحين"
            :footer-props="{
              'items-per-page-text': 'المستخدمين في كل صفحة',
              'items-per-page-options': [5, 10, 15, 20, 50],
            }"
            :items-per-page="perPage"
            class="elevation-0"
            :server-items-length="totalUsers"
            @update:options="handleTableOptions"
          >
            <!-- User role column -->
            <template #[`item.role`]="{ item }">
              <v-chip
                small
                :color="getRoleColor(item)"
                text-color="white"
              >
                {{ getRoleText(item) }}
              </v-chip>
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
                @click="viewUserDetails(item)"
                class="mr-1"
                title="عرض التفاصيل"
              >
                <v-icon small>mdi-eye</v-icon>
              </v-btn>
              
              <v-menu
                bottom
                left
                offset-y
              >
                <template #activator="{ on, attrs }">
                  <v-btn
                    icon
                    small
                    color="grey darken-1"
                    v-bind="attrs"
                    v-on="on"
                    title="المزيد من الخيارات"
                  >
                    <v-icon small>mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>
                
                <v-list dense>
                  <v-list-item @click="editUserDetails(item)">
                    <v-list-item-icon>
                      <v-icon small color="amber darken-2">mdi-pencil</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title>تعديل المعلومات</v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                  
                  <v-list-item @click="toggleUserStatus(item)" v-if="!item.is_admin">
                    <v-list-item-icon>
                      <v-icon small :color="item.is_active ? 'red darken-2' : 'green darken-2'">
                        {{ item.is_active ? 'mdi-account-off' : 'mdi-account-check' }}
                      </v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title>{{ item.is_active ? 'تعطيل الحساب' : 'تفعيل الحساب' }}</v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                  
                  <v-list-item @click="resetUserPassword(item)">
                    <v-list-item-icon>
                      <v-icon small color="blue darken-2">mdi-key-variant</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title>إعادة تعيين كلمة المرور</v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </v-data-table>
          
          <v-divider></v-divider>
          
          <v-card-actions>
            <v-pagination
              v-model="page"
              :length="totalPages"
              :total-visible="7"
              @input="loadUsers"
            ></v-pagination>
            <v-spacer></v-spacer>
            <div class="text-caption grey--text pa-2">
              إجمالي المستخدمين: {{ totalUsers }}
            </div>
          </v-card-actions>
        </v-card>
        
        <!-- User Details Dialog -->
        <v-dialog v-model="userDialog" max-width="700">
          <v-card v-if="selectedUser">
            <v-card-title class="headline primary white--text">
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
                      <v-list-item-subtitle>{{ getRoleText(selectedUser) }}</v-list-item-subtitle>
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
                color="grey darken-1"
                text
                @click="userDialog = false"
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
        
        <!-- Reset Password Dialog -->
        <v-dialog v-model="resetPasswordDialog" max-width="500">
          <v-card v-if="resetPasswordInfo">
            <v-card-title class="headline success white--text">
              تم إعادة تعيين كلمة المرور
              <v-spacer></v-spacer>
              <v-btn icon dark @click="resetPasswordDialog = false">
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </v-card-title>
            
            <v-card-text class="pt-4">
              <p>تم إعادة تعيين كلمة المرور للمستخدم <strong>{{ resetPasswordInfo.userName }}</strong> بنجاح.</p>
              
              <v-alert
                type="warning"
                prominent
                class="mt-3"
              >
                <h3 class="text-subtitle-1 font-weight-bold mb-2">كلمة المرور الجديدة:</h3>
                <div class="d-flex align-center">
                  <v-text-field
                    v-model="resetPasswordInfo.newPassword"
                    readonly
                    outlined
                    dense
                    class="flex-grow-1 mr-2"
                    hide-details
                  ></v-text-field>
                  <v-btn
                    icon
                    color="primary"
                    @click="copyToClipboard(resetPasswordInfo.newPassword)"
                    title="نسخ إلى الحافظة"
                  >
                    <v-icon>mdi-content-copy</v-icon>
                  </v-btn>
                </div>
                <p class="mt-3 mb-0">يرجى الاحتفاظ بكلمة المرور هذه ومشاركتها مع المستخدم بطريقة آمنة.</p>
              </v-alert>
              
              <p class="mt-4">بريد المستخدم: <strong>{{ resetPasswordInfo.userEmail }}</strong></p>
            </v-card-text>
            
            <v-divider></v-divider>
            
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                color="primary"
                @click="resetPasswordDialog = false"
              >
                تم
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
          <template #action="{ attrs }">
            <v-btn
              text
              v-bind="attrs"
              @click="snackbar.show = false"
            >
              إغلاق
            </v-btn>
          </template>
        </v-snackbar>

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
                          {{ selectedBusiness.city || 'غير متوفر' }}
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
                          {{ selectedUser ? selectedUser.name : 'غير متوفر' }}
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
  </v-app>
</template>

<script>
import api from '@/services/api';
import { debounce } from 'lodash';

export default {
  name: 'AdminUsers',
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
      totalUsers: 0,
      totalPages: 0,
      users: [],
      selectedUser: null,
      userDialog: false,
      editDialog: false,
      resetPasswordDialog: false,
      resetPasswordInfo: null,
      businessDialog: false,
      selectedBusiness: null,
      snackbar: {
        show: false,
        color: '',
        text: '',
        timeout: 3000
      },
      sortBy: 'created_at',
      sortDesc: true,
      roleFilter: '',
      roleOptions: [
        { text: 'الكل', value: '' },
        { text: 'مدير', value: 'admin' },
        { text: 'صاحب عمل', value: 'business' },
        { text: 'مستخدم عادي', value: 'user' }
      ],
      menuItems: [
        { title: 'لوحة التحكم', icon: 'mdi-view-dashboard', route: '/admin/dashboard', exact: true },
        { title: 'المستخدمين', icon: 'mdi-account-group', route: '/admin/users', exact: false },
        { title: 'الأعمال', icon: 'mdi-store', route: '/admin/businesses', exact: false },
        { title: 'التصنيفات', icon: 'mdi-shape', route: '/admin/categories', exact: false },
        { title: 'التقييمات', icon: 'mdi-comment-text-multiple', route: '/admin/feedbacks', exact: false },
      ],
      headers: [
        { text: 'الاسم', value: 'name', align: 'start' },
        { text: 'البريد الإلكتروني', value: 'email' },
        { text: 'المدينة', value: 'city' },
        { text: 'الدور', value: 'role' },
        { text: 'تاريخ التسجيل', value: 'created_at' },
        { text: 'الإجراءات', value: 'actions', sortable: false, align: 'center' }
      ],
      editedUser: {
        name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        country: ''
      },
      editFormValid: true,
      updating: false
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
    
    // Initialize debounced search
    this.debouncedSearch = debounce(() => {
      this.loadUsers(1);
    }, 500);
    
    // Load users
    this.loadUsers(1);
    
    // Listen for showSnackbar event
    this.$root.$on('showSnackbar', data => {
      this.snackbar.text = data.message;
      this.snackbar.color = data.color || 'info';
      this.snackbar.timeout = data.timeout || 3000;
      this.snackbar.show = true;
    });
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
    
    // Clean up event listeners
    this.$root.$off('showSnackbar');
  },
  methods: {
    async loadUsers(page) {
      if (page) {
        this.page = page;
      }
      
      this.loading = true;
      this.error = '';
      
      try {
        const params = {
          page: this.page,
          per_page: this.perPage,
          search: this.search,
          sort_by: this.sortBy,
          sort_order: this.sortDesc ? 'desc' : 'asc',
          role: this.roleFilter
        };
        
        const response = await api.get('/admin/users', { params });
        
        if (response.data && response.data.status === 'success') {
          this.users = response.data.data;
          this.totalUsers = response.data.pagination.total;
          this.totalPages = response.data.pagination.last_page;
          console.log('Users loaded successfully');
        } else {
          this.error = 'Failed to load users: ' + (response.data.message || 'Unknown error');
        }
      } catch (error) {
        console.error('Error loading users:', error);
        this.error = 'حدث خطأ أثناء تحميل بيانات المستخدمين. يرجى المحاولة مرة أخرى.';
        
        if (error.response && error.response.status === 401) {
          // Token expired or invalid
          this.logout();
        }
      } finally {
        this.loading = false;
      }
    },
    handleTableOptions(options) {
      const { itemsPerPage, page, sortBy, sortDesc } = options;
      
      let changed = false;
      
      // Update items per page if changed
      if (this.perPage !== itemsPerPage) {
        this.perPage = itemsPerPage;
        changed = true;
      }
      
      // Update page if changed
      if (this.page !== page) {
        this.page = page;
        changed = true;
      }
      
      // Update sort if changed
      if (sortBy.length > 0 && this.sortBy !== sortBy[0]) {
        this.sortBy = sortBy[0];
        changed = true;
      }
      
      if (sortDesc.length > 0 && this.sortDesc !== sortDesc[0]) {
        this.sortDesc = sortDesc[0];
        changed = true;
      }
      
      // Reload data if anything changed
      if (changed) {
        this.loadUsers();
      }
    },
    getRoleText(user) {
      if (user.is_admin) {
        return 'مدير النظام';
      } else if (user.is_business_owner) {
        return 'صاحب عمل';
      } else {
        return 'مستخدم عادي';
      }
    },
    getRoleColor(user) {
      if (user.is_admin) {
        return 'red darken-1';
      } else if (user.is_business_owner) {
        return 'primary';
      } else {
        return 'green darken-1';
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
    editUserDetails(user) {
      this.selectedUser = { ...user };
      this.editedUser = {
        name: user.name,
        email: user.email,
        phone: user.phone,
        address: user.address,
        city: user.city,
        country: user.country
      };
      this.editDialog = true;
    },
    async toggleUserStatus(user) {
      try {
        this.loading = true;
        const response = await api.patch(`/admin/users/${user.id}/toggle-status`);
        
        if (response.data && response.data.status === 'success') {
          // Update the user status in the local data
          const index = this.users.findIndex(u => u.id === user.id);
          if (index !== -1) {
            this.users[index].is_active = response.data.user.is_active;
          }
          
          // Show success message
          this.$root.$emit('showSnackbar', {
            message: response.data.message,
            color: 'success'
          });
          
          // Refresh the data
          this.loadUsers(this.page);
        } else {
          this.error = 'Failed to update user status: ' + (response.data.message || 'Unknown error');
        }
      } catch (error) {
        console.error('Error updating user status:', error);
        this.error = error.response?.data?.message || 'حدث خطأ أثناء تحديث حالة المستخدم. يرجى المحاولة مرة أخرى.';
      } finally {
        this.loading = false;
      }
    },
    async resetUserPassword(user) {
      try {
        // Show confirmation dialog
        if (!confirm(`هل أنت متأكد من إعادة تعيين كلمة المرور للمستخدم "${user.name}"؟`)) {
          return;
        }
        
        this.loading = true;
        const response = await api.post(`/admin/users/${user.id}/reset-password`);
        
        if (response.data && response.data.status === 'success') {
          // Show success dialog with the new password
          this.resetPasswordDialog = true;
          this.resetPasswordInfo = {
            userName: user.name,
            userEmail: user.email,
            newPassword: response.data.new_password
          };
          
          // Show success message
          this.$root.$emit('showSnackbar', {
            message: 'تم إعادة تعيين كلمة المرور بنجاح',
            color: 'success'
          });
        } else {
          this.error = 'Failed to reset password: ' + (response.data.message || 'Unknown error');
        }
      } catch (error) {
        console.error('Error resetting password:', error);
        this.error = error.response?.data?.message || 'حدث خطأ أثناء إعادة تعيين كلمة المرور. يرجى المحاولة مرة أخرى.';
      } finally {
        this.loading = false;
      }
    },
    async updateUser() {
      try {
        this.updating = true;
        const response = await api.put(`/admin/users/${this.selectedUser.id}`, this.editedUser);
        
        if (response.data && response.data.status === 'success') {
          // Update the user in the local data
          const index = this.users.findIndex(u => u.id === this.selectedUser.id);
          if (index !== -1) {
            this.users[index] = response.data.user;
          }
          
          // Show success message
          this.$root.$emit('showSnackbar', {
            message: response.data.message,
            color: 'success'
          });
          
          // Close the dialog
          this.editDialog = false;
          
          // Refresh the data
          this.loadUsers(this.page);
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
    copyToClipboard(text) {
      const input = document.createElement('input');
      input.value = text;
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      document.body.removeChild(input);
      
      this.$root.$emit('showSnackbar', {
        message: 'تم نسخ الكود إلى الحافظة',
        color: 'success'
      });
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

/* Add spacing between avatar and content */
.v-list-item__avatar {
  margin-right: 16px !important;
}
</style> 