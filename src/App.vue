<template>
  <div id="app" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <!-- Navbar -->
    <nav v-if="isLoggedIn" class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: rgba(0,0,0,0.3)">
      <div class="container-fluid">
        <span class="navbar-brand fs-4 fw-bold">File Manager</span>
        <div class="navbar-nav ms-auto">
          <router-link to="/upload" class="nav-link px-3 text-light">Upload</router-link>
          <router-link to="/files" class="nav-link px-3 text-light">My Files</router-link>
          <router-link to="/shared" class="nav-link px-3 text-light">Download Link</router-link>
          <span class="nav-link px-3 text-white">Hello, {{ username }}</span>
          <button @click="logout" class="nav-link btn px-3 text-light">Logout</button>
        </div>
      </div>
    </nav>

    <!-- Content -->
    <router-view />

    <!-- Footer -->
    <footer class="text-center text-white py-3 mt-5" style="background-color: rgba(0,0,0,0.2)">
      <small>File Management System - Project</small>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      isLoggedIn: false,
      username: ''
    };
  },
  mounted() {
    this.checkAuth();
  },
  methods: {
    checkAuth() {
      const user = localStorage.getItem('user');
      if (user) {
        const userData = JSON.parse(user);
        this.isLoggedIn = true;
        this.username = userData.username;
      }
    },
    logout() {
      if (confirm('Are you sure you want to logout?')) {
        localStorage.removeItem('user');
        this.$router.push('/login');
        window.location.reload();
      }
    }
  }
}
</script>

<style>
.router-link-active {
  font-weight: bold;
  color: white !important;
}
</style>