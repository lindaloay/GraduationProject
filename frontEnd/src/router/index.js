import Vue from "vue";
import VueRouter from "vue-router";
import HomeView from "@/views/HomeView.vue";
import RestaurantPage from "@/views/RestaurantPage.vue";
import CompanyPage from "@/views/CompanyPage.vue";
import HotelPage from "@/views/HotelPage.vue";
import ProductPage from "@/views/ProductPage.vue";
import SearchPage from "@/views/SearchPage.vue";
import LoginPage from "@/views/LoginPage.vue";
import SignupPage from "@/views/SignupPage.vue";
import BusinessInfo1 from "@/views/BusinessInfo1.vue";
import BusinessInfo2 from "@/views/BusinessInfo2.vue";
import ForgetPass from "@/views/ForgetPass.vue";
import SportPage from "@/views/SportPage.vue";
import UserProfile from "@/views/UserProfile.vue";
import UserBizProfile from "@/views/UserBizProfile.vue";
import AdminLogin from "@/views/admin/Login.vue";
import AdminDashboard from "@/views/admin/Dashboard.vue";
import AdminUsers from "@/views/admin/Users.vue";
import AdminCategories from "@/views/admin/Categories.vue";
import AdminFeedbacks from "@/views/admin/Feedbacks.vue";
import AdminRatingAspects from "@/views/admin/RatingAspects.vue";
import { globalState } from "@/state";
import api from "@/services/api";
import Businesses from "@/views/admin/Businesses.vue";

Vue.use(VueRouter);

const routes = [
  {
    path: "/",
    name: "HomeView",
    component: HomeView,
  },
  {
    path: "/Restaurant",
    name: "RestaurantPage",
    component: RestaurantPage,
  },
  {
    path: "/Company",
    name: "CompanyPage",
    component: CompanyPage,
  },
  {
    path: "/Hotel",
    name: "HotelPage",
    component: HotelPage,
  },
  {
    path: "/Product/:businessId",
    name: "ProductPage",
    component: ProductPage,
    props: true
  },
  {
    path: "/Search",
    name: "SearchPage",
    component: SearchPage,
  },
  {
    path: "/Login",
    name: "LoginPage",
    component: LoginPage,
  },
  {
    path: "/Signup",
    name: "SignupPage",
    component: SignupPage,
  },
  {
    path: "/Business1",
    name: "BusinessInfo1",
    component: BusinessInfo1,
  },
  {
    path: "/Business2",
    name: "BusinessInfo2",
    component: BusinessInfo2,
  },
  {
    path: "/ForgetPass",
    name: "ForgetPass",
    component: ForgetPass,
  },
  {
    path: "/Sport",
    name: "SportPage",
    component: SportPage,
  },
  {
    path: "/UserProfile",
    name: "UserProfile",
    component: UserProfile,
  },
  {
    path: "/UserBizProfile",
    name: "UserBizProfile",
    component: UserBizProfile,
  },
  // Admin Routes
  {
    path: "/admin/login",
    name: "AdminLogin",
    component: AdminLogin,
    meta: {
      requiresAdmin: false,
      adminLayout: true
    }
  },
  {
    path: "/admin/dashboard",
    name: "AdminDashboard",
    component: AdminDashboard,
    meta: {
      requiresAdmin: true,
      adminLayout: true
    }
  },
  {
    path: "/admin/users",
    name: "AdminUsers",
    component: AdminUsers,
    meta: {
      requiresAdmin: true,
      adminLayout: true
    }
  },
  {
    path: "/admin/categories",
    name: "AdminCategories",
    component: AdminCategories,
    meta: {
      requiresAdmin: true,
      adminLayout: true
    }
  },
  {
    path: "/admin/businesses",
    name: "AdminBusinesses",
    component: Businesses,
    meta: {
      requiresAdmin: true,
      adminLayout: true
    }
  },
  {
    path: "/admin/feedbacks",
    name: "AdminFeedbacks",
    component: AdminFeedbacks,
    meta: {
      requiresAdmin: true,
      adminLayout: true
    }
  },
  {
    path: "/admin/rating-aspects",
    name: "AdminRatingAspects",
    component: AdminRatingAspects,
    meta: { requiresAdmin: true, adminLayout: true }
  }
];

const router = new VueRouter({
  mode: "history",
  base: process.env.BASE_URL,
  routes,
});

router.beforeEach(async (to, from, next) => {
  // Check for admin routes
  if (to.matched.some(record => record.meta.requiresAdmin)) {
    const adminToken = localStorage.getItem('admin_token');

    if (!adminToken) {
      next({ path: '/admin/login' });
      return;
    }

    // Set auth header for admin routes
    api.defaults.headers.common['Authorization'] = `Bearer ${adminToken}`;
  }

  if (to.path === '/Business2' || to.path === '/Business1') {
    // Fetch Current User Data
    try {
      const response = await api.get('user/profile');
      console.log('Got User Data:', response.data.user.is_business_owner === 0);
      if (response.data.user.is_business_owner === 0) {
        router.push('/');
        return;
      }
    } catch (error) {
      console.error('Error fetching user data:', error);
      globalState.isLoggedIn = false;
      globalState.userData = null;
    }
  }

  // Check if route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!globalState.isLoggedIn) {
      // Only redirect to login if not already on login page
      if (to.path !== '/login') {
        next({
          path: '/login',
          query: { redirect: to.fullPath }
        });
        return;
      }
    }

    // Check if route requires business role
    if (to.matched.some(record => record.meta.requiresBusiness)) {
      try {
        // Check if user has a business profile
        const response = await api.get('business/details');
        const hasBusiness = response.data && response.data.success && response.data.business;

        // If route requires no business but user has one, redirect to dashboard
        if (to.matched.some(record => record.meta.requiresNoBusiness) && hasBusiness) {
          next('/business/dashboard');
          return;
        }

        // If route requires business but user doesn't have one, redirect to Business1
        if (!to.matched.some(record => record.meta.requiresNoBusiness) && !hasBusiness) {
          next('/Business1');
          return;
        }
      } catch (error) {
        console.error('Error checking business status:', error);
        next('/Business1');
        return;
      }
    }
  }

  // Check if route requires guest (not logged in)
  if (to.matched.some(record => record.meta.requiresGuest)) {
    if (globalState.isLoggedIn) {
      // Redirect to home if already logged in
      next('/');
      return;
    }
  }

  next();
});

export default router;

// Add navigation guard to handle admin mode class
router.afterEach((to, from) => {
  const isAdminRoute = to.path.startsWith('/admin');

  // If navigating to admin route, add the admin-mode class
  if (isAdminRoute) {
    document.body.classList.add('admin-mode');

    // Hide any header that might be visible
    const header = document.querySelector('.transparent-header');
    if (header) header.style.display = 'none';

    // Hide any footer that might be visible
    const footerElements = document.querySelectorAll('footer');
    footerElements.forEach(footer => {
      if (footer) footer.style.display = 'none';
    });
  }
  // If navigating from admin route to non-admin route, remove the admin-mode class
  else if (from.path.startsWith('/admin')) {
    document.body.classList.remove('admin-mode');

    // Show any header that might be hidden
    const header = document.querySelector('.transparent-header');
    if (header) header.style.display = '';

    // Show any footer that might be hidden
    const footerElements = document.querySelectorAll('footer');
    footerElements.forEach(footer => {
      if (footer) footer.style.display = '';
    });
  }
});
