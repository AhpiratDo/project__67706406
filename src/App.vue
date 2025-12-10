<template>
  <div id="app">
    <!-- Navbar -->
    <nav v-if="isLoggedIn" class="cyber-navbar">
      <div class="navbar-content">
        <div class="navbar-brand">
          <span class="brand-icon">📁</span>
          <span class="brand-text">FILE MANAGER</span>
        </div>
        <div class="navbar-menu">
          <router-link to="/upload" class="nav-item">
            <span class="nav-icon">📤</span>
            <span>UPLOAD</span>
          </router-link>
          <router-link to="/files" class="nav-item">
            <span class="nav-icon">📋</span>
            <span>FILES</span>
          </router-link>
          <router-link to="/shared" class="nav-item">
            <span class="nav-icon">🔗</span>
            <span>DOWNLOAD</span>
          </router-link>
          <div class="nav-user">
            <span class="user-label">USER:</span>
            <span class="user-name">{{ username }}</span>
          </div>
          <button @click="logout" class="nav-logout">
            <span>LOGOUT</span>
          </button>
        </div>
      </div>
    </nav>

    <!-- Content -->
    <div class="main-content" :class="{ 'with-navbar': isLoggedIn }">
      <router-view />
    </div>
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
  watch: {
    '$route'() {
      this.checkAuth();
    }
  },
  methods: {
    checkAuth() {
      const user = localStorage.getItem('user');
      if (user) {
        try {
          const userData = JSON.parse(user);
          this.isLoggedIn = true;
          this.username = userData.username || 'User';
        } catch (e) {
          this.isLoggedIn = false;
        }
      } else {
        this.isLoggedIn = false;
      }
    },
    logout() {
      if (confirm('Are you sure you want to logout?')) {
        localStorage.removeItem('user');
        this.isLoggedIn = false;
        this.$router.push('/login');
      }
    }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  overflow-x: hidden;
  background: #0a0a0a;
}

#app {
  font-family: 'Orbitron', monospace;
  min-height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #0a0a0a 100%);
  background-attachment: fixed;
}

.cyber-navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  background: rgba(10, 10, 10, 0.95);
  border-bottom: 2px solid #00ffff;
  box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
  z-index: 1000;
  backdrop-filter: blur(10px);
}

.navbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.navbar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.brand-icon {
  font-size: 1.5rem;
}

.brand-text {
  font-size: 1.3rem;
  font-weight: 900;
  color: #00ffff;
  text-shadow: 0 0 10px #00ffff;
  letter-spacing: 0.2em;
}

.navbar-menu {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  background: transparent;
  border: 1px solid rgba(0, 255, 255, 0.3);
  color: rgba(0, 255, 255, 0.7);
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  transition: all 0.3s ease;
}

.nav-item:hover {
  background: rgba(0, 255, 255, 0.1);
  border-color: #00ffff;
  color: #00ffff;
  box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
}

.nav-item.router-link-active {
  background: rgba(0, 255, 255, 0.15);
  border-color: #00ffff;
  color: #00ffff;
  box-shadow: 0 0 15px rgba(0, 255, 255, 0.4);
}

.nav-icon {
  font-size: 1rem;
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  background: rgba(0, 255, 255, 0.05);
  border: 1px solid rgba(0, 255, 255, 0.2);
  font-size: 0.85rem;
}

.user-label {
  color: #666;
  letter-spacing: 0.1em;
}

.user-name {
  color: #00ffff;
  font-weight: 700;
}

.nav-logout {
  padding: 0.6rem 1.5rem;
  background: transparent;
  border: 2px solid #ff0064;
  color: #ff0064;
  font-family: 'Orbitron', monospace;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  cursor: pointer;
  transition: all 0.3s ease;
}

.nav-logout:hover {
  background: rgba(255, 0, 100, 0.1);
  box-shadow: 0 0 15px rgba(255, 0, 100, 0.5);
}

.main-content {
  min-height: 100vh;
  width: 100%;
  background: inherit;
}

.main-content.with-navbar {
  padding-top: 80px;
  min-height: calc(100vh - 80px);
}

/* Responsive */
@media (max-width: 1024px) {
  .navbar-content {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
  }

  .navbar-menu {
    flex-wrap: wrap;
    justify-content: center;
  }

  .main-content.with-navbar {
    padding-top: 160px;
  }
}

@media (max-width: 768px) {
  .nav-item span:not(.nav-icon) {
    display: none;
  }

  .nav-item {
    padding: 0.6rem;
  }

  .brand-text {
    font-size: 1rem;
  }
}
</style>