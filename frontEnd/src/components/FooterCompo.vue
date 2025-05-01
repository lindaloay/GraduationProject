<template>
  <v-container v-if="!isLoginPage && !isAdminRoute">
    <v-row align="center" justify="space-between">
      <!-- Logo -->
      <v-col cols="auto">
        <v-img width="220" height="50" src="photo/Logo/logoBizA.png" />
      </v-col>

      <v-col cols="auto">
        <v-btn icon color="#ffa726">
          <v-icon>mdi-twitter</v-icon>
        </v-btn>
        <v-btn icon color="#ffa726">
          <v-icon>mdi-instagram</v-icon>
        </v-btn>
        <v-btn icon color="#ffa726">
          <v-icon>mdi-facebook</v-icon>
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

export default {
  computed: {
    // Check if the current route is the login page
    isLoginPage() {
      return (
        this.$route.path === "/Login" ||
        this.$route.path === "/Signup" ||
        this.$route.path === "/Business2" ||
        this.$route.path === "/Business1" ||
        this.$route.path === "/ForgetPass"
      );
    },
    // Check if the current route is an admin route
    isAdminRoute() {
      return this.$route.path.startsWith('/admin');
    },
  },
};
</script>
