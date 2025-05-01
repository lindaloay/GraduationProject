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
      <v-toolbar-title class="font-weight-bold">إدارة الأعمال التجارية</v-toolbar-title>
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
      
        <!-- Businesses Management Card -->
        <v-card class="mb-4">
          <v-card-title>
            <div class="d-flex flex-wrap align-center w-100">
              <h2 class="text-h5 font-weight-bold mr-4">إدارة الأعمال التجارية</h2>
              <v-spacer></v-spacer>
              
              <v-text-field
                v-model="searchTerm"
                prepend-icon="mdi-magnify"
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
                v-model="categoryFilter"
                :items="categoryOptions"
                label="تصفية حسب التصنيف"
                outlined
                dense
                hide-details
                class="mx-2"
                style="max-width: 200px;"
                @change="loadBusinesses(1)"
              ></v-select>
            </div>
          </v-card-title>
          
          <v-divider></v-divider>
          
          <v-data-table
            :headers="headers"
            :items="businesses"
            :loading="loading"
            loading-text="جاري تحميل الأعمال التجارية..."
            no-data-text="لا يوجد أعمال تجارية متاحة"
            :footer-props="{
              'items-per-page-text': 'الأعمال في كل صفحة',
              'items-per-page-options': [5, 10, 15, 20, 50],
            }"
            :items-per-page="perPage"
            class="elevation-0"
            :server-items-length="totalBusinesses"
            @update:options="handleTableOptions"
          >
            <!-- Category column -->
            <template #[`item.category`]="{ item }">
              <v-chip
                small
                color="primary"
                text-color="white"
                v-if="item.category"
              >
                {{ item.category.name_ar }}
              </v-chip>
              <span v-else class="grey--text">بدون تصنيف</span>
            </template>
            
            <!-- Main picture column -->
            <template #[`item.main_picture_url`]="{ item }">
              <v-avatar
                size="40"
                v-if="item.main_picture_url"
              >
                <v-img :src="item.main_picture_url" :alt="item.name"></v-img>
              </v-avatar>
              <v-avatar
                size="40"
                color="primary lighten-3"
                v-else
              >
                <v-icon dark>mdi-store</v-icon>
              </v-avatar>
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
                @click="viewBusinessDetails(item)"
                title="عرض"
              >
                <v-icon small>mdi-eye</v-icon>
              </v-btn>
              
              <v-btn
                icon
                small
                color="info"
                class="mr-1"
                @click="editBusiness(item)"
                title="تعديل"
              >
                <v-icon small>mdi-pencil</v-icon>
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
              @input="loadBusinesses"
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
                  {{ selectedBusiness.rating.toFixed(1) }} ({{ selectedBusiness.rating_count }})
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
                    <div class="detail-label">المدينة / الدولة</div>
                    <div class="detail-value">
                      {{ [selectedBusiness.user.city, selectedBusiness.user.country].filter(Boolean).join(', ') || 'غير متوفر' }}
                    </div>
                  </div>
                </v-col>
                
                <v-col cols="12" sm="6">
                  <div class="detail-item">
                    <div class="detail-label">أوقات العمل</div>
                    <div class="detail-value">
                      {{ displayWorkingTimes(selectedBusiness.working_times) }}
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
                    @click="openGalleryImage(image)"
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
    
    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="headline error white--text">
          تأكيد الحذف
        </v-card-title>
        
        <v-card-text class="py-4">
          <p>هل أنت متأكد من رغبتك في حذف العمل التجاري "<strong>{{ selectedBusiness ? selectedBusiness.name : '' }}</strong>"؟</p>
          <p class="mb-0 text-body-2 warning--text">
            <v-icon small color="warning">mdi-alert</v-icon>
            هذا الإجراء لا يمكن التراجع عنه وسيتم حذف جميع البيانات المرتبطة، بما في ذلك التقييمات والصور.
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
            @click="deleteBusiness"
          >
            تأكيد الحذف
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Image Gallery Dialog -->
    <v-dialog v-model="galleryDialog" max-width="800">
      <v-card>
        <v-img
          :src="selectedImage"
          max-height="600"
          contain
        ></v-img>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            icon
            @click="galleryDialog = false"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- User Details Dialog (simplified) -->
    <v-dialog v-model="userDialog" max-width="600">
      <v-card v-if="selectedUser">
        <v-card-title class="headline deep-purple white--text">
          تفاصيل المستخدم
          <v-spacer></v-spacer>
          <v-btn icon dark @click="userDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pa-4">
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
          
          <v-divider class="my-3"></v-divider>
          
          <div class="py-2">
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
            
            
          </div>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            text
            :to="`/admin/users?user=${selectedUser.id}`"
          >
            <v-icon small left>mdi-account-details</v-icon>
            مزيد من التفاصيل
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
    
    <!-- Edit Business Dialog -->
    <v-dialog v-model="editDialog" persistent max-width="800">
      <v-card v-if="businessToEdit">
        <v-card-title class="headline primary white--text">
          تعديل بيانات العمل التجاري
          <v-spacer></v-spacer>
          <v-btn icon dark @click="closeEditDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pa-4">
          <v-form ref="editForm" v-model="editFormValid">
            <v-row>
              <!-- Basic Information -->
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="businessToEdit.name"
                  label="اسم العمل"
                  outlined
                  dense
                  :rules="[v => !!v || 'هذا الحقل مطلوب']"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-select
                  v-model="businessToEdit.category_id"
                  :items="categoryOptions"
                  item-text="text"
                  item-value="value"
                  label="تصنيف العمل"
                  outlined
                  dense
                  :rules="[v => !!v || 'هذا الحقل مطلوب']"
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="businessToEdit.phone"
                  label="رقم الهاتف"
                  outlined
                  dense
                  :rules="[v => !!v || 'هذا الحقل مطلوب']"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="businessToEdit.email"
                  label="البريد الإلكتروني"
                  outlined
                  dense
                  :rules="[
                    v => !!v || 'هذا الحقل مطلوب',
                    v => /.+@.+\..+/.test(v) || 'البريد الإلكتروني غير صحيح'
                  ]"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="businessToEdit.website"
                  label="الموقع الإلكتروني"
                  outlined
                  dense
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="businessToEdit.working_times"
                  label="أوقات العمل"
                  outlined
                  dense
                ></v-text-field>
              </v-col>
              
              <v-col cols="12">
                <v-textarea
                  v-model="businessToEdit.description"
                  label="وصف العمل"
                  outlined
                  :rules="[v => !!v || 'هذا الحقل مطلوب']"
                  rows="3"
                ></v-textarea>
              </v-col>
              
              <!-- Image Management Section -->
              <v-col cols="12">
                <h3 class="text-subtitle-1 mb-2">صور العمل التجاري</h3>
                <v-divider class="mb-3"></v-divider>
                
                <!-- Main Image -->
                <v-row>
                  <v-col cols="12" md="6">
                    <h4 class="text-subtitle-2 mb-2">الصورة الرئيسية</h4>
                    <div class="image-upload-container">
                      <div class="image-preview-wrapper">
                        <v-img
                          v-if="businessToEdit.main_picture_url"
                          :src="businessToEdit.main_picture_url"
                          max-height="200"
                          contain
                          class="rounded main-image-preview"
                        ></v-img>
                        <v-sheet
                          v-else
                          class="d-flex align-center justify-center rounded"
                          height="200"
                          width="100%"
                        >
                          <v-icon size="64" color="grey darken-2">mdi-image</v-icon>
                        </v-sheet>
                        
                        <div class="upload-overlay">
                          <label for="main-image-upload" class="upload-btn">
                            <v-icon>mdi-camera</v-icon>
                            <span>تغيير الصورة</span>
                          </label>
                          <input
                            id="main-image-upload"
                            type="file"
                            accept="image/*"
                            class="hidden-input"
                            @change="(e) => previewMainImage(e.target.files[0])"
                          />
                        </div>
                      </div>
                      <div v-if="mainImageFile" class="selected-file-name mt-2">
                        <v-icon small color="success" class="mr-1">mdi-check-circle</v-icon>
                        تم اختيار: {{ mainImageFile.name }}
                        <v-btn x-small text color="error" @click="clearMainImage" class="mr-1">
                          <v-icon x-small>mdi-close</v-icon>
                        </v-btn>
                      </div>
                    </div>
                  </v-col>
                  
                  <!-- Gallery Images -->
                  <v-col cols="12" md="6">
                    <h4 class="text-subtitle-2 mb-2">معرض الصور</h4>
                    <div class="gallery-upload-container">
                      <div v-if="businessToEdit.gallery_pictures_urls && businessToEdit.gallery_pictures_urls.length" class="gallery-grid">
                        <div
                          v-for="(image, index) in businessToEdit.gallery_pictures_urls"
                          :key="index"
                          class="gallery-item"
                        >
                          <v-img
                            :src="image"
                            aspect-ratio="1"
                            class="rounded"
                            cover
                          ></v-img>
                          <v-btn
                            icon
                            x-small
                            color="error"
                            class="gallery-delete-btn"
                            @click="removeGalleryImage(index)"
                          >
                            <v-icon x-small>mdi-close</v-icon>
                          </v-btn>
                        </div>
                      </div>
                      <v-sheet
                        v-else
                        class="d-flex align-center justify-center rounded"
                        height="150"
                        width="100%"
                      >
                        <div class="text-center">
                          <v-icon size="48" color="grey darken-2">mdi-image-multiple</v-icon>
                          <div class="text-caption mt-2">لا توجد صور في المعرض</div>
                        </div>
                      </v-sheet>
                      
                      <div class="mt-3">
                        <label for="gallery-images-upload" class="upload-gallery-btn">
                          <v-icon left>mdi-image-plus</v-icon>
                          إضافة صور للمعرض
                        </label>
                        <input
                          id="gallery-images-upload"
                          type="file"
                          accept="image/*"
                          multiple
                          class="hidden-input"
                          @change="(e) => previewGalleryImages(e.target.files)"
                        />
                      </div>
                      
                    </div>
                  </v-col>
                </v-row>
              </v-col>
              
              <!-- Social Media Links -->
              <v-col cols="12">
                <h3 class="text-subtitle-1 mb-2">روابط وسائل التواصل الإجتماعي</h3>
                <v-divider class="mb-3"></v-divider>
                
                <v-row>
                  <v-col cols="12" sm="6">
                    <v-text-field
                      v-model="businessToEdit.facebook_link"
                      label="Facebook"
                      outlined
                      dense
                      prepend-icon="mdi-facebook"
                    ></v-text-field>
                  </v-col>
                  
                  <v-col cols="12" sm="6">
                    <v-text-field
                      v-model="businessToEdit.instagram_link"
                      label="Instagram"
                      outlined
                      dense
                      prepend-icon="mdi-instagram"
                    ></v-text-field>
                  </v-col>
                  
                  <v-col cols="12" sm="6">
                    <v-text-field
                      v-model="businessToEdit.twitter_link"
                      label="Twitter"
                      outlined
                      dense
                      prepend-icon="mdi-twitter"
                    ></v-text-field>
                  </v-col>
                  
                  
                </v-row>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey darken-1"
            text
            @click="closeEditDialog"
            :disabled="updating"
          >
            إلغاء
          </v-btn>
          <v-btn
            color="primary"
            :loading="updating"
            :disabled="updating || !editFormValid"
            @click="updateBusiness"
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
import { debounce } from 'lodash';

export default {
  name: 'AdminBusinesses',
  data() {
    return {
      drawer: true,
      miniVariant: false,
      loading: false,
      error: '',
      adminName: 'المدير',
      searchTerm: '',
      page: 1,
      perPage: 10,
      totalBusinesses: 0,
      totalPages: 0,
      businesses: [],
      categoryFilter: '',
      categoryOptions: [
        { text: 'الكل', value: '' }
      ],
      headers: [
        { text: 'الصورة', value: 'main_picture_url', align: 'center', sortable: false },
        { text: 'الاسم', value: 'name', align: 'start' },
        { text: 'التصنيف', value: 'category', align: 'center' },
        { text: 'البريد الإلكتروني', value: 'email' },
        { text: 'التقييم', value: 'rating', align: 'center' },
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
      
      // Business details dialog
      businessDialog: false,
      selectedBusiness: null,
      
      // Delete dialog
      deleteDialog: false,
      deleting: false,
      
      // Gallery dialog
      galleryDialog: false,
      selectedImage: '',
      
      // User dialog
      userDialog: false,
      selectedUser: null,
      
      // Snackbar
      snackbar: {
        show: false,
        color: '',
        text: '',
        timeout: 3000
      },
      
      // Edit dialog
      editDialog: false,
      businessToEdit: null,
      editFormValid: true,
      updating: false,
      mainImageFile: null,
      galleryImageFiles: [],
      galleryImagesToRemove: [],
      originalMainImageUrl: null,
    };
  },
  computed: {
    // Placeholder for any computed properties needed
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
    
    // Load categories for filter
    this.loadCategories();
    
    // Load initial businesses
    this.loadBusinesses(1);
    
    // Initialize debounced search
    this.debouncedSearch = debounce(() => {
      this.loadBusinesses(1);
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
    async loadCategories() {
      try {
        const response = await api.get('/business-categories');
        if (response.data.status === 'success') {
          const categories = response.data.categories;
          
          // Prepare options for the filter dropdown
          this.categoryOptions = [
            { text: 'كل التصنيفات', value: '' },
            ...categories.map(category => ({
              text: category.name_ar,
              value: category.id
            }))
          ];
        }
      } catch (error) {
        console.error('Error loading categories:', error);
        this.error = 'حدث خطأ أثناء تحميل التصنيفات';
      }
    },
    
    async loadBusinesses(page = this.page) {
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
        if (this.searchTerm) {
          params.search = this.searchTerm;
        }
        
        // Add category filter if present
        if (this.categoryFilter) {
          params.category_id = this.categoryFilter;
        }
        
        const response = await api.get('/admin/businesses', { params });
        
        if (response.data.status === 'success') {
          // Use businesses property from response
          this.businesses = response.data.businesses;
          this.totalBusinesses = response.data.pagination.total;
          this.totalPages = response.data.pagination.last_page;
        } else {
          this.error = 'فشل في تحميل الأعمال التجارية: ' + (response.data.message || 'خطأ غير معروف');
        }
      } catch (error) {
        console.error('Error loading businesses:', error);
        this.error = 'حدث خطأ أثناء تحميل الأعمال التجارية. يرجى المحاولة مرة أخرى.';
        
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
      
      // Load businesses with new options
      this.loadBusinesses();
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Intl.DateTimeFormat('ar-SA', options).format(date);
    },
    
    viewBusinessDetails(business) {
      // Load full business details from API
      this.loading = true;
      api.get(`/admin/businesses/${business.id}`)
        .then(response => {
          if (response.data.status === 'success') {
            // Ensure gallery_pictures_urls is defined
            const businessData = response.data.business;
            businessData.gallery_pictures_urls = businessData.gallery_pictures_urls || [];
            
            this.selectedBusiness = businessData;
            this.businessDialog = true;
            
            // Log to debug
            console.log('Business details loaded:', this.selectedBusiness);
            console.log('Gallery images:', this.selectedBusiness.gallery_pictures_urls);
          } else {
            this.error = 'فشل في تحميل تفاصيل العمل التجاري: ' + (response.data.message || 'خطأ غير معروف');
          }
        })
        .catch(error => {
          console.error('Error loading business details:', error);
          this.error = 'حدث خطأ أثناء تحميل تفاصيل العمل التجاري. يرجى المحاولة مرة أخرى.';
        })
        .finally(() => {
          this.loading = false;
        });
    },
    
    viewUserDetails(user) {
      // Set selected user and open dialog
      this.selectedUser = user;
      this.userDialog = true;
    },
    
    confirmDelete(business) {
      this.selectedBusiness = business;
      this.deleteDialog = true;
    },
    
    async deleteBusiness() {
      if (!this.selectedBusiness) return;
      
      this.deleting = true;
      
      try {
        const response = await api.delete(`/admin/businesses/${this.selectedBusiness.id}`);
        
        if (response.data.status === 'success') {
          // Show success message
          this.snackbar = {
            show: true,
            color: 'success',
            text: 'تم حذف العمل التجاري بنجاح',
            timeout: 3000
          };
          
          // Close the dialog
          this.deleteDialog = false;
          
          // Refresh businesses list
          this.loadBusinesses();
        }
      } catch (error) {
        console.error('Error deleting business:', error);
        this.error = error.response?.data?.message || 'حدث خطأ أثناء حذف العمل التجاري. يرجى المحاولة مرة أخرى.';
        
        // Close the dialog
        this.deleteDialog = false;
      } finally {
        this.deleting = false;
      }
    },
    
    openGalleryImage(image) {
      this.selectedImage = image;
      this.galleryDialog = true;
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
    
    editBusiness(business) {
      // Reset the gallery images to remove array
      this.galleryImagesToRemove = [];
      this.mainImageFile = null;
      this.galleryImageFiles = [];
      
      // Save the original main image URL
      this.originalMainImageUrl = business.main_picture_url;
      
      // Parse working_times to display it properly
      let workingTimes = '';
      try {
        if (business.working_times) {
          if (typeof business.working_times === 'string') {
            // Try to parse it if it's a JSON string
            try {
              const parsedTimes = JSON.parse(business.working_times);
              workingTimes = typeof parsedTimes === 'string' ? parsedTimes : JSON.stringify(parsedTimes);
            } catch (e) {
              // If it's not valid JSON, use it as is
              workingTimes = business.working_times;
            }
          } else {
            // If it's already an object, stringify it
            workingTimes = JSON.stringify(business.working_times);
          }
        }
      } catch (e) {
        console.error('Error parsing working times:', e);
        workingTimes = '';
      }

      // Make a full API call to get business details with images
      this.loading = true;
      api.get(`/admin/businesses/${business.id}`)
        .then(response => {
          if (response.data.status === 'success') {
            const fullBusiness = response.data.business;
            
            this.businessToEdit = { 
              ...fullBusiness,
              // Ensure social media links are properly initialized
              facebook_link: fullBusiness.facebook_link || '',
              instagram_link: fullBusiness.instagram_link || '',
              twitter_link: fullBusiness.twitter_link || '',
              working_times: workingTimes,
              website: fullBusiness.website || '',
              // Make sure gallery_pictures_urls is defined
              gallery_pictures_urls: fullBusiness.gallery_pictures_urls || [],
              // Use category_id directly
              category_id: fullBusiness.category ? fullBusiness.category.id : null
            };
            
            this.editDialog = true;
          } else {
            this.error = 'Failed to load business details';
          }
          this.loading = false;
        })
        .catch(error => {
          console.error('Error loading business details for edit:', error);
          this.error = 'Error loading business details for editing';
          this.loading = false;
        });
    },
    
    closeEditDialog() {
      this.editDialog = false;
      this.businessToEdit = null;
      this.editFormValid = true;
    },
    
    async updateBusiness() {
      if (!this.businessToEdit) return;
      
      this.updating = true;
      
      try {
        // Prepare working_times as a valid JSON string
        let workingTimesJson = this.businessToEdit.working_times;
        try {
          // If it's not already valid JSON, wrap it in quotes to make it a JSON string
          JSON.parse(workingTimesJson);
        } catch (e) {
          // Not valid JSON, so convert it to a JSON string
          workingTimesJson = JSON.stringify(this.businessToEdit.working_times);
        }
        
        // Create FormData for file uploads
        const formData = new FormData();
        
        // Add basic fields
        formData.append('name', this.businessToEdit.name);
        formData.append('category_id', this.businessToEdit.category_id);
        formData.append('description', this.businessToEdit.description);
        formData.append('email', this.businessToEdit.email);
        formData.append('phone', this.businessToEdit.phone);
        formData.append('website', this.businessToEdit.website || '');
        formData.append('working_times', workingTimesJson);
        formData.append('facebook_link', this.businessToEdit.facebook_link || '');
        formData.append('instagram_link', this.businessToEdit.instagram_link || '');
        formData.append('twitter_link', this.businessToEdit.twitter_link || '');
        
        // Add main image if changed
        if (this.mainImageFile) {
          formData.append('main_image', this.mainImageFile);
        }
        
        // Add gallery images if any
        if (this.galleryImageFiles && this.galleryImageFiles.length) {
          for (let i = 0; i < this.galleryImageFiles.length; i++) {
            formData.append('gallery_images[]', this.galleryImageFiles[i]);
          }
        }
        
        // Add gallery images to remove if any
        if (this.galleryImagesToRemove && this.galleryImagesToRemove.length) {
          console.log('Gallery images to remove:', this.galleryImagesToRemove);
          formData.append('gallery_images_to_remove', JSON.stringify(this.galleryImagesToRemove));
        }
        
        // Log form data for debugging
        for (let [key, value] of formData.entries()) {
          if (key !== 'main_image' && key !== 'gallery_images[]') {
            console.log(`${key}: ${value}`);
          } else {
            console.log(`${key}: [File data]`);
          }
        }
        
        const response = await api.post(
          `/admin/businesses/${this.businessToEdit.id}/update-with-images`,
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
        );
        
        if (response.data.status === 'success') {
          // Show success message
          this.snackbar = {
            show: true,
            color: 'success',
            text: 'تم تحديث بيانات العمل التجاري بنجاح',
            timeout: 3000
          };
          
          // Close the dialog
          this.editDialog = false;
          
          // Refresh businesses list
          this.loadBusinesses();
        }
      } catch (error) {
        console.error('Error updating business:', error);
        this.error = error.response?.data?.message || 'حدث خطأ أثناء تحديث بيانات العمل التجاري. يرجى المحاولة مرة أخرى.';
        
        // Close the dialog
        this.editDialog = false;
      } finally {
        this.updating = false;
      }
    },
    
    previewMainImage(file) {
      if (!file) return;
      
      this.mainImageFile = file;
      
      // Create a URL for the uploaded image
      this.businessToEdit.main_picture_url = URL.createObjectURL(file);
    },
    
    clearMainImage() {
      this.mainImageFile = null;
      // Restore the original image URL if it exists
      if (this.originalMainImageUrl) {
        this.businessToEdit.main_picture_url = this.originalMainImageUrl;
      } else {
        this.businessToEdit.main_picture_url = null;
      }
    },
    
    previewGalleryImages(files) {
      if (!files || !files.length) return;
      
      // Convert FileList to Array and store
      this.galleryImageFiles = Array.from(files);
      
      // Initialize gallery_pictures_urls if it doesn't exist
      if (!this.businessToEdit.gallery_pictures_urls) {
        this.businessToEdit.gallery_pictures_urls = [];
      }
      
      // Add temporary URLs for the new images
      for (let i = 0; i < files.length; i++) {
        this.businessToEdit.gallery_pictures_urls.push(URL.createObjectURL(files[i]));
      }
    },
    
    removeGalleryImage(index) {
      // Initialize gallery images to remove if it doesn't exist
      if (!this.galleryImagesToRemove) {
        this.galleryImagesToRemove = [];
      }
      
      // If this was an existing image, add its URL to the remove list
      if (this.businessToEdit.gallery_pictures_urls && this.businessToEdit.gallery_pictures_urls.length > index) {
        const imageUrl = this.businessToEdit.gallery_pictures_urls[index];
        // Only add to remove list if it's not a blob URL (new image)
        if (!imageUrl.startsWith('blob:')) {
          // Store the image URL for removal
          this.galleryImagesToRemove.push(imageUrl);
          console.log('Added to removal list:', imageUrl);
          
          // Also log all images in removal list
          console.log('Current removal list:', [...this.galleryImagesToRemove]);
        }
      }
      
      // Remove the image from the display array
      this.businessToEdit.gallery_pictures_urls.splice(index, 1);
    },
    
    removeNewGalleryImage(index) {
      if (this.galleryImageFiles && this.galleryImageFiles.length > index) {
        // Get the file that was removed
        const removedFile = this.galleryImageFiles[index];
        
        // Remove the file from galleryImageFiles
        this.galleryImageFiles.splice(index, 1);
        
        // Find and remove the corresponding blob URL from gallery_pictures_urls
        const blobIndex = this.businessToEdit.gallery_pictures_urls.findIndex(url => 
          url.startsWith('blob:') && url.includes(removedFile.name)
        );
        
        if (blobIndex !== -1) {
          this.businessToEdit.gallery_pictures_urls.splice(blobIndex, 1);
        }
      }
    },
    
    // Helper method to display working times in a readable format
    displayWorkingTimes(workingTimes) {
      if (!workingTimes) return 'غير متوفر';
      
      try {
        if (typeof workingTimes === 'string') {
          try {
            // Try to parse it as JSON
            const parsed = JSON.parse(workingTimes);
            return typeof parsed === 'string' ? parsed : JSON.stringify(parsed);
          } catch (e) {
            // If not valid JSON, return as is
            return workingTimes;
          }
        } else {
          // If it's an object, stringify it
          return JSON.stringify(workingTimes);
        }
      } catch (e) {
        console.error('Error formatting working times:', e);
        return 'غير متوفر';
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

.search-field {
  max-width: 300px;
  margin-left: auto;
}

/* Gallery image styles */
.position-relative {
  position: relative;
}

.gallery-delete-btn {
  position: absolute !important;
  top: 5px;
  right: 5px;
  background-color: rgba(255, 255, 255, 0.7) !important;
}

/* New styles for image upload */
.image-upload-container {
  position: relative;
  width: 100%;
  height: 200px;
}

.image-preview-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #ccc;
  border-radius: 4px;
  overflow: hidden;
}

.main-image-preview {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.image-preview-wrapper:hover .upload-overlay {
  opacity: 1;
}

.upload-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #fff;
  background: none;
  border: none;
  cursor: pointer;
  padding: 10px;
  transition: transform 0.2s ease;
}

.upload-btn:hover {
  transform: scale(1.1);
}

.upload-btn span {
  margin-top: 8px;
  font-size: 14px;
}

.hidden-input {
  display: none;
}

.selected-file-name {
  text-align: center;
  margin-top: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f0f8ff;
  padding: 4px 8px;
  border-radius: 4px;
}

.gallery-upload-container {
  width: 100%;
  min-height: 150px;
}

.gallery-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 16px;
}

.gallery-item {
  position: relative;
  width: calc(33.33% - 8px);
  aspect-ratio: 1;
  border-radius: 4px;
  overflow: hidden;
}

.gallery-delete-btn {
  position: absolute;
  top: 5px;
  right: 5px;
  background-color: rgba(255, 255, 255, 0.7) !important;
  height: 20px !important;
  width: 20px !important;
  min-width: 20px !important;
}

.upload-gallery-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  background-color: #1976d2;
  padding: 8px 16px;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.upload-gallery-btn:hover {
  background-color: #1565c0;
}

.selected-files {
  padding: 8px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  margin-top: 16px;
  background-color: #f5f5f5;
}
</style> 