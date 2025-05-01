<template>
  <v-container class="transparent-header" v-if="!isAdminRoute">
    <v-row align="center" justify="space-between">
      <!-- Logo -->
      <v-col cols="auto">
        <a href="/">
          <v-img width="220" height="50" src="photo/Logo/logoBizA.png" />
        </a>
      </v-col>

      <!-- Navigation Links for Large Screens -->
      <v-col cols="auto" class="d-none d-md-flex">
        <v-btn
          v-for="(item, index) in menuItems"
          :key="index"
          text
          class="text-orange--text font-weight-bold"
          :to="item.link"
        >
          {{ item.label }}
        </v-btn>
      </v-col>

      <!-- Actions -->
      <v-col cols="auto" class="d-none d-md-flex align-center">
        <v-btn
          icon
          color="#e28c0d"
          to="/Search"
          class="ml-4 buttons text-orange--text"
          style="background-color: transparent"
        >
          <v-icon>mdi-magnify</v-icon>
        </v-btn>
        <v-btn
          v-if="!isLoggedIn"
          outlined
          color="#e28c0d"
          class="ml-4 buttons text-orange--text"
          to="/Login"
        >
          تسجيل دخول
        </v-btn>
        <v-menu v-else offset-y>
          <template v-slot:activator="{ on, attrs }">
            <v-btn text style="color: #e28c0d" v-bind="attrs" v-on="on">
              {{ userName }}
              <v-icon right>mdi-chevron-down</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item
              @click="checkBusinessAndNavigate"
            >
              <v-list-item-title>الملف الشخصي</v-list-item-title>
            </v-list-item>
            <v-list-item @click="logout">
              <v-list-item-title>تسجيل خروج</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-col>

      <!-- Hamburger Menu for Small Screens -->
      <v-col cols="auto" color="#e28c0d" class="d-flex d-md-none align-center">
        <v-menu>
          <template v-slot:activator="{ on, attrs }">
            <v-btn icon v-bind="attrs" v-on="on">
              <v-icon>mdi-menu</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item
              v-for="(item, index) in menuItems"
              :key="index"
              :to="item.link"
            >
              <v-list-item-title>{{ item.label }}</v-list-item-title>
            </v-list-item>
            <v-divider></v-divider>
            <v-list-item>
              <v-btn icon color="#e28c0d">
                <v-icon>mdi-magnify</v-icon>
              </v-btn>
              <v-btn
                v-if="!isLoggedIn"
                outlined
                color="#e28c0d"
                class="ml-4 buttons text-orange--text"
                to="/Login"
              >
                تسجيل دخول
              </v-btn>
              <v-menu v-else offset-y>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn text style="color: #e28c0d" v-bind="attrs" v-on="on">
                    {{ userName }}
                    <v-icon right>mdi-chevron-down</v-icon>
                  </v-btn>
                </template>
                <v-list>
                  <v-list-item
                    @click="checkBusinessAndNavigate"
                  >
                    <v-list-item-title>الملف الشخصي</v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="logout">
                    <v-list-item-title>تسجيل خروج</v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import router from "@/router";
import { globalState } from "../state";
import api from "@/services/api";

export default {
  name: "HeaderNavigation",
  computed: {
    isLoggedIn() {
      return globalState.isLoggedIn;
    },
    userName() {
      return globalState.user?.name || "";
    },
    isBusinessUser() {
      return globalState.user?.is_business_owner === 1;
    },
    isAdminRoute() {
      return router.currentRoute.path.startsWith('/admin');
    },
  },
  methods: {
    async checkBusinessAndNavigate() {
      if (this.isBusinessUser) {
        try {
          const response = await api.get('/business/details');
          console.log("Business Details:", response.data);
          if (!response.data.data.status === 'success') {
            // If no business exists, redirect to Business1
            if(router.currentRoute.path != '/Business1') {
            this.$router.push('/Business1');
          }
          } else {
            // If business exists, go to business profile
            if (router.currentRoute.path != '/userbizprofile') {
              this.$router.push('/userbizprofile');
            }
          }
        } catch (error) {
          console.error('Error checking business:', error);
          if(router.currentRoute.path != '/Business1') {
            this.$router.push('/Business1');
          }
        }
      } else {
        if (router.currentRoute.path != '/userprofile') {
          this.$router.push('/userprofile');
        }
      }
    },
    logout() {
      globalState.logout();
      
      if(router.currentRoute.path != '/') {
        this.$router.push("/");
      }
    },
  },
  data() {
    return {
      menuItems: [
        { label: "الرئيسية", link: "/" },
        { label: "الشركات", link: "/Company" },
        { label: "المطاعم", link: "/Restaurant" },
        { label: "الفنادق", link: "/Hotel" },
        { label: "صالات الرياضة", link: "/Sport" },
      ],
      loginText: "تسجيل دخول",
      languageText: "English",
    };
  },
  async created() {
    // Fetch user profile when component is created if user is logged in
    if (localStorage.getItem("token")) {
      await globalState.fetchUserProfile();
    }
  },
};
</script>

<style scoped>
.transparent-header {
  background-color: transparent !important;
  position: absolute;
  left: 0;
  right: 0;
  width: 100%;
  z-index: 10;
  box-shadow: none;
  padding-top: 15px !important;
}

.text-orange--text {
  color: #e28c0d !important;
  font-family: "ExpoLight";
  font-weight: bold;
}

.buttons {
  border-radius: 12px !important;
  background-color: white;
  border-radius: 4px !important;
}

/* Style for dropdown menu */
.v-menu__content {
  border-radius: 8px !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.v-list-item {
  min-height: 40px !important;
}

.v-list-item-title {
  font-size: 14px !important;
  color: #333 !important;
}

.v-list-item:hover {
  background-color: #f5f5f5 !important;
}
</style>
