<template>
  <v-navigation-drawer
    v-model="drawerModel"
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
        @click.stop="toggleMiniVariant"
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
</template>

<script>
import api from "@/services/api";

export default {
  name: "AdminSidebar",
  props: {
    drawer: {
      type: Boolean,
      default: true
    },
    mini: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      miniVariant: this.mini,
      menuItems: [
        { title: 'لوحة التحكم', icon: 'mdi-view-dashboard', route: '/admin/dashboard', exact: true },
        { title: 'المستخدمين', icon: 'mdi-account-group', route: '/admin/users' },
        { title: 'الأعمال', icon: 'mdi-store', route: '/admin/businesses' },
        { title: 'التصنيفات', icon: 'mdi-shape', route: '/admin/categories' },
        { title: 'التقييمات', icon: 'mdi-comment-text', route: '/admin/feedbacks' },
        { title: 'محاور التقييم', icon: 'mdi-star', route: '/admin/rating-aspects' }
      ],
    };
  },
  computed: {
    drawerModel: {
      get() {
        return this.drawer;
      },
      set(value) {
        this.$emit('update:drawer', value);
      }
    }
  },
  methods: {
    toggleMiniVariant() {
      this.miniVariant = !this.miniVariant;
      this.$emit('update:mini', this.miniVariant);
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

.logout-item {
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