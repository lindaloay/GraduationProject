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
      <v-toolbar-title class="font-weight-bold">إدارة تصنيفات الأعمال</v-toolbar-title>
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
      
        <!-- Categories Management Card -->
        <v-card class="mb-4">
          <v-card-title>
            <div class="d-flex flex-wrap align-center w-100">
              <h2 class="text-h5 font-weight-bold mr-4">إدارة التصنيفات</h2>
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
              ></v-text-field>
              
              <v-btn
                color="primary"
                dark
                class="ml-2"
                @click="openCreateDialog"
              >
                <v-icon left>mdi-plus</v-icon>
                إضافة تصنيف جديد
              </v-btn>
            </div>
          </v-card-title>
          
          <v-divider></v-divider>
          
          <v-data-table
            :headers="headers"
            :items="filteredCategories"
            :loading="loading"
            loading-text="جاري تحميل التصنيفات..."
            no-data-text="لا يوجد تصنيفات متاحة"
            :footer-props="{
              'items-per-page-text': 'التصنيفات في كل صفحة',
              'items-per-page-options': [5, 10, 15, 20, 50],
            }"
            :items-per-page="10"
            class="elevation-0"
          >
            <!-- Status column -->
            <template #[`item.is_active`]="{ item }">
              <v-chip
                small
                :color="item.is_active ? 'success' : 'error'"
                text-color="white"
              >
                {{ item.is_active ? 'مفعل' : 'غير مفعل' }}
              </v-chip>
            </template>
            
            <!-- Icon column -->
            <template #[`item.icon`]="{ item }">
              <v-icon v-if="item.icon">{{ item.icon }}</v-icon>
              <span v-else class="grey--text">لا يوجد</span>
            </template>
            
            <!-- Created at column -->
            <template #[`item.created_at`]="{ item }">
              <span>{{ formatDate(item.created_at) }}</span>
            </template>
            
            <!-- Actions column -->
            <template #[`item.actions`]="{ item }">
              <v-btn
                icon
                small
                color="primary"
                class="mr-1"
                @click="editCategory(item)"
                title="تعديل"
              >
                <v-icon small>mdi-pencil</v-icon>
              </v-btn>
              
              <v-btn
                icon
                small
                :color="item.is_active ? 'error' : 'success'"
                class="mr-1"
                @click="toggleStatus(item)"
                :title="item.is_active ? 'تعطيل' : 'تفعيل'"
              >
                <v-icon small>{{ item.is_active ? 'mdi-close' : 'mdi-check' }}</v-icon>
              </v-btn>
              
              <v-btn
              v-if="item.id > 4"
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
    
    <!-- Create/Edit Category Dialog -->
    <v-dialog v-model="categoryDialog" max-width="600">
      <v-card>
        <v-card-title class="headline primary white--text">
          {{ editMode ? 'تعديل تصنيف' : 'إضافة تصنيف جديد' }}
          <v-spacer></v-spacer>
          <v-btn icon dark @click="categoryDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pt-4">
          <v-form ref="categoryForm" v-model="formValid" @submit.prevent="saveCategory">
            <v-container>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedCategory.name"
                    label="الاسم (الإنجليزية)"
                    required
                    outlined
                    :rules="[v => !!v || 'الاسم مطلوب']"
                    dir="ltr"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedCategory.name_ar"
                    label="الاسم (العربية)"
                    required
                    outlined
                    :rules="[v => !!v || 'الاسم بالعربية مطلوب']"
                    dir="rtl"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12">
                  <v-textarea
                    v-model="editedCategory.description"
                    label="الوصف"
                    outlined
                    rows="3"
                    dir="rtl"
                  ></v-textarea>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedCategory.icon"
                    label="أيقونة (Material Design Icons)"
                    outlined
                    placeholder="مثال: mdi-store"
                    dir="ltr"
                    prepend-inner-icon="mdi-package-variant"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-switch
                    v-model="editedCategory.is_active"
                    label="مفعل"
                    color="success"
                    hide-details
                    class="mt-4"
                  ></v-switch>
                </v-col>
                
                <v-col cols="12" v-if="editedCategory.icon">
                  <div class="text-center">
                    <p class="mb-2">معاينة الأيقونة:</p>
                    <v-icon size="36" color="primary">{{ editedCategory.icon }}</v-icon>
                  </div>
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
            @click="categoryDialog = false"
          >
            إلغاء
          </v-btn>
          <v-btn
            color="primary"
            :loading="saving"
            :disabled="!formValid || saving"
            @click="saveCategory"
          >
            {{ editMode ? 'حفظ التغييرات' : 'إضافة التصنيف' }}
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
          <p>هل أنت متأكد من رغبتك في حذف التصنيف "<strong>{{ selectedCategory ? selectedCategory.name_ar : '' }}</strong>"؟</p>
          <p class="mb-0 text-body-2 warning--text" v-if="selectedCategory && selectedCategory.businesses_count > 0">
            <v-icon small color="warning">mdi-alert</v-icon>
            هذا التصنيف مرتبط بـ {{ selectedCategory.businesses_count }} عمل تجاري. حذفه قد يؤثر على هذه الأعمال.
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
            @click="deleteCategory"
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

export default {
  name: 'AdminCategories',
  data() {
    return {
      drawer: true,
      miniVariant: false,
      loading: false,
      error: '',
      adminName: 'المدير',
      search: '',
      categories: [],
      headers: [
        { text: 'الاسم (العربية)', value: 'name_ar', align: 'start' },
        { text: 'الاسم (الإنجليزية)', value: 'name' },
        { text: 'الوصف', value: 'description' },
        { text: 'الأيقونة', value: 'icon', align: 'center', sortable: false },
        { text: 'الحالة', value: 'is_active', align: 'center' },
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
      categoryDialog: false,
      editMode: false,
      formValid: true,
      saving: false,
      editedCategory: {
        name: '',
        name_ar: '',
        description: '',
        icon: '',
        is_active: true
      },
      defaultCategory: {
        name: '',
        name_ar: '',
        description: '',
        icon: '',
        is_active: true
      },
      deleteDialog: false,
      deleting: false,
      selectedCategory: null,
      snackbar: {
        show: false,
        color: '',
        text: '',
        timeout: 3000
      }
    };
  },
  computed: {
    filteredCategories() {
      if (!this.search) return this.categories;
      
      const searchText = this.search.toLowerCase();
      return this.categories.filter(category => {
        return category.name.toLowerCase().includes(searchText) ||
               category.name_ar.toLowerCase().includes(searchText) ||
               (category.description && category.description.toLowerCase().includes(searchText));
      });
    }
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
    
    // Load categories
    this.loadCategories();
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
      this.loading = true;
      this.error = '';
      
      try {
        // Get all categories, including inactive ones
        const response = await api.get('/categories?include_inactive=true');
        
        if (response.data.status === 'success') {
          this.categories = response.data.categories.map(category => {
            return {
              ...category,
              businesses_count: category.businesses_count || 0
            };
          });
        } else {
          this.error = 'فشل في تحميل التصنيفات: ' + (response.data.message || 'خطأ غير معروف');
        }
      } catch (error) {
        console.error('Error loading categories:', error);
        this.error = 'حدث خطأ أثناء تحميل التصنيفات. يرجى المحاولة مرة أخرى.';
        
        if (error.response && error.response.status === 401) {
          // Token expired or invalid
          this.logout();
        }
      } finally {
        this.loading = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Intl.DateTimeFormat('ar-SA', options).format(date);
    },
    
    openCreateDialog() {
      this.editMode = false;
      this.editedCategory = Object.assign({}, this.defaultCategory);
      this.categoryDialog = true;
      
      this.$nextTick(() => {
        if (this.$refs.categoryForm) {
          this.$refs.categoryForm.resetValidation();
        }
      });
    },
    
    editCategory(category) {
      this.editMode = true;
      this.editedCategory = Object.assign({}, category);
      this.categoryDialog = true;
      
      this.$nextTick(() => {
        if (this.$refs.categoryForm) {
          this.$refs.categoryForm.resetValidation();
        }
      });
    },
    
    async saveCategory() {
      if (!this.$refs.categoryForm.validate()) return;
      
      this.saving = true;
      
      try {
        let response;
        
        if (this.editMode) {
          // Update existing category
          response = await api.put(`/admin/categories/${this.editedCategory.id}`, this.editedCategory);
          
          if (response.data.status === 'success') {
            // Update category in the local array
            const index = this.categories.findIndex(c => c.id === this.editedCategory.id);
            if (index !== -1) {
              this.categories.splice(index, 1, response.data.category);
            }
            
            this.snackbar = {
              show: true,
              color: 'success',
              text: 'تم تحديث التصنيف بنجاح',
              timeout: 3000
            };
          }
        } else {
          // Create new category
          response = await api.post('/admin/categories', this.editedCategory);
          
          if (response.data.status === 'success') {
            // Add new category to the array
            this.categories.push(response.data.category);
            
            this.snackbar = {
              show: true,
              color: 'success',
              text: 'تم إضافة التصنيف بنجاح',
              timeout: 3000
            };
          }
        }
        
        // Close the dialog
        this.categoryDialog = false;
      } catch (error) {
        console.error('Error saving category:', error);
        this.error = error.response?.data?.message || 'حدث خطأ أثناء حفظ التصنيف. يرجى المحاولة مرة أخرى.';
        
        if (error.response?.data?.errors) {
          // Show first validation error
          const firstError = Object.values(error.response.data.errors)[0];
          this.error = firstError[0] || this.error;
        }
      } finally {
        this.saving = false;
      }
    },
    
    confirmDelete(category) {
      this.selectedCategory = category;
      this.deleteDialog = true;
    },
    
    async deleteCategory() {
      if (!this.selectedCategory) return;
      
      this.deleting = true;
      
      try {
        const response = await api.delete(`/admin/categories/${this.selectedCategory.id}`);
        
        if (response.data.status === 'success') {
          // Remove category from the array
          const index = this.categories.findIndex(c => c.id === this.selectedCategory.id);
          if (index !== -1) {
            this.categories.splice(index, 1);
          }
          
          this.snackbar = {
            show: true,
            color: 'success',
            text: 'تم حذف التصنيف بنجاح',
            timeout: 3000
          };
          
          // Close the dialog
          this.deleteDialog = false;
        }
      } catch (error) {
        console.error('Error deleting category:', error);
        
        if (error.response && error.response.status === 400) {
          // Category has businesses
          this.error = error.response.data.message || 'لا يمكن حذف التصنيف لأنه مرتبط بأعمال تجارية';
        } else {
          this.error = error.response?.data?.message || 'حدث خطأ أثناء حذف التصنيف. يرجى المحاولة مرة أخرى.';
        }
        
        // Close the dialog
        this.deleteDialog = false;
      } finally {
        this.deleting = false;
      }
    },
    
    async toggleStatus(category) {
      try {
        this.loading = true;
        
        const updatedCategory = {
          ...category,
          is_active: !category.is_active
        };
        
        const response = await api.put(`/admin/categories/${category.id}`, updatedCategory);
        
        if (response.data.status === 'success') {
          // Update category in the local array
          const index = this.categories.findIndex(c => c.id === category.id);
          if (index !== -1) {
            this.categories.splice(index, 1, response.data.category);
          }
          
          this.snackbar = {
            show: true,
            color: 'success',
            text: category.is_active ? 'تم تعطيل التصنيف بنجاح' : 'تم تفعيل التصنيف بنجاح',
            timeout: 3000
          };
        }
      } catch (error) {
        console.error('Error toggling category status:', error);
        this.error = error.response?.data?.message || 'حدث خطأ أثناء تغيير حالة التصنيف. يرجى المحاولة مرة أخرى.';
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

/* Truncate long descriptions in table */
.v-data-table td:nth-child(3) {
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style> 