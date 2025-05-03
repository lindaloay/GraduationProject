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
      
      <template v-slot:append>
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

    <!-- Main Content -->
    <v-main class="admin-main-content">
      <v-container>
        <v-row>
          <v-col cols="12">
            <v-card>
              <v-card-title class="d-flex justify-space-between align-center">
                <span>إدارة محاور التقييم</span>
                <v-btn color="primary" @click="openAddDialog" :disabled="!selectedCategory">
                  إضافة محور جديد
                </v-btn>
              </v-card-title>

              <v-card-text>
                <!-- Categories Selection -->
                <v-row class="mb-6">
                  <v-col cols="12">
                    <v-select
                      v-model="selectedCategory"
                      :items="categories"
                      item-text="name"
                      item-value="id"
                      label="اختر تصنيف العمل"
                      outlined
                      clearable
                      @change="fetchAspects"
                    ></v-select>
                  </v-col>
                </v-row>

                <!-- Rating Aspects Table -->
                <v-data-table
                  v-if="selectedCategory"
                  :headers="headers"
                  :items="aspects"
                  :loading="loading"
                  :no-data-text="'لا توجد محاور تقييم لهذا التصنيف'"
                >
                  <template v-slot:item.actions="{ item }">
                    <v-icon small class="mr-2" @click="editAspect(item)">
                      mdi-pencil
                    </v-icon>
                    <v-icon small @click="deleteAspect(item)">
                      mdi-delete
                    </v-icon>
                  </template>
                </v-data-table>

                <v-alert
                  v-else
                  type="info"
                  class="mt-4"
                >
                  يرجى اختيار تصنيف العمل لعرض محاور التقييم الخاصة به
                </v-alert>
              </v-card-text>
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

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="500px">
      <v-card>
        <v-card-title>
          <span class="headline">{{ formTitle }}</span>
        </v-card-title>

        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="editedItem.name"
                  label="اسم المحور"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="editedItem.description"
                  label="الوصف"
                  required
                ></v-textarea>
              </v-col>
              <v-col cols="12">
                <v-select
                  v-model="editedItem.category_id"
                  :items="categories"
                  item-text="name"
                  item-value="id"
                  label="تصنيف العمل"
                  required
                  :disabled="!!editedItem.id"
                ></v-select>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="closeDialog">
            إلغاء
          </v-btn>
          <v-btn color="blue darken-1" text @click="saveAspect"> حفظ </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card>
        <v-card-title class="headline error--text">
          <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
          تأكيد الحذف
        </v-card-title>
        
        <v-card-text class="pt-4">
          <div class="text-body-1">
            هل أنت متأكد من حذف محور التقييم "<strong>{{ editedItem.name }}</strong>"؟
          </div>
          <div class="text-caption mt-2 grey--text">
            ملاحظة: لا يمكن التراجع عن هذا الإجراء بعد الحذف.
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey darken-1"
            text
            @click="closeDeleteDialog"
          >
            إلغاء
          </v-btn>
          <v-btn
            color="error"
            text
            @click="confirmDelete"
          >
            <v-icon left>mdi-delete</v-icon>
            حذف
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>

<script>
import api from "@/services/api";

export default {
  name: "RatingAspects",
  data() {
    return {
      drawer: true,
      miniVariant: false,
      adminName: 'المدير',
      menuItems: [
        { title: 'لوحة التحكم', icon: 'mdi-view-dashboard', route: '/admin/dashboard', exact: true },
        { title: 'المستخدمين', icon: 'mdi-account-group', route: '/admin/users' },
        { title: 'الأعمال', icon: 'mdi-store', route: '/admin/businesses' },
        { title: 'التصنيفات', icon: 'mdi-shape', route: '/admin/categories' },
        { title: 'التقييمات', icon: 'mdi-comment-text', route: '/admin/feedbacks' },
        { title: 'محاور التقييم', icon: 'mdi-star', route: '/admin/rating-aspects' }
      ],
      loading: false,
      dialog: false,
      deleteDialog: false,
      categories: [],
      selectedCategory: null,
      aspects: [],
      editedItem: {
        id: null,
        name: "",
        description: "",
        category_id: null,
      },
      defaultItem: {
        id: null,
        name: "",
        description: "",
        category_id: null,
      },
      headers: [
        { text: "الاسم", value: "name" },
        { text: "الوصف", value: "description" },
        { text: "الإجراءات", value: "actions", sortable: false },
      ],
    };
  },
  computed: {
    formTitle() {
      return this.editedItem.id === null
        ? "محور تقييم جديد"
        : "تعديل محور التقييم";
    },
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

    this.fetchCategories();
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
    async fetchCategories() {
      try {
        const response = await api.get("/categories");
        this.categories = response.data.categories;
      } catch (error) {
        console.error("Error fetching categories:", error);
      }
    },
    async fetchAspects() {
      if (!this.selectedCategory) {
        this.aspects = [];
        return;
      }

      this.loading = true;
      try {
        const response = await api.get(`/rating-aspects/category/${this.selectedCategory}`);
        this.aspects = response.data;
      } catch (error) {
        console.error("Error fetching aspects:", error);
      } finally {
        this.loading = false;
      }
    },
    openAddDialog() {
      this.editedItem = { 
        ...this.defaultItem,
        category_id: this.selectedCategory 
      };
      this.dialog = true;
    },
    editAspect(item) {
      this.editedItem = { ...item };
      this.dialog = true;
    },
    async saveAspect() {
      try {
        if (this.editedItem.id) {
          await api.put(
            `/admin/rating-aspects/${this.editedItem.id}`,
            this.editedItem
          );
        } else {
          await api.post("/admin/rating-aspects", this.editedItem);
        }
        this.fetchAspects();
        this.closeDialog();
      } catch (error) {
        console.error("Error saving aspect:", error);
      }
    },
    deleteAspect(item) {
      this.editedItem = { ...item };
      this.deleteDialog = true;
    },
    async confirmDelete() {
      try {
        await api.delete(`/admin/rating-aspects/${this.editedItem.id}`);
        this.fetchAspects();
        this.closeDeleteDialog();
      } catch (error) {
        console.error("Error deleting aspect:", error);
      }
    },
    closeDialog() {
      this.dialog = false;
      this.editedItem = { ...this.defaultItem };
    },
    closeDeleteDialog() {
      this.deleteDialog = false;
      this.editedItem = { ...this.defaultItem };
    },
  },
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
</style>
