<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-lg border-0">
          <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%)">
            <h3 class="mb-0 text-center">Login</h3>
          </div>
          <div class="card-body p-4">
            <div class="mb-3">
              <label class="form-label fw-bold">Username or Email</label>
              <input
                v-model="form.username"
                type="text"
                class="form-control"
                placeholder="Enter username or email"
                @keyup.enter="login"
              />
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Password</label>
              <input
                v-model="form.password"
                type="password"
                class="form-control"
                placeholder="Enter password"
                @keyup.enter="login"
              />
            </div>

            <button
              @click="login"
              class="btn btn-lg w-100 text-white mb-3"
              style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%)"
              :disabled="loading"
            >
              {{ loading ? 'Logging in...' : 'Login' }}
            </button>

            <div class="text-center">
              <small>Don't have an account? <router-link to="/register" class="text-decoration-none">Register here</router-link></small>
            </div>

            <div v-if="message" :class="`alert alert-${messageType} mt-3`">
              {{ message }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Login',
  data() {
    return {
      form: {
        username: '',
        password: ''
      },
      message: '',
      messageType: '',
      loading: false
    };
  },
  methods: {
    async login() {
      if (!this.form.username || !this.form.password) {
        this.message = 'Please fill in all fields';
        this.messageType = 'danger';
        return;
      }

      this.loading = true;
      this.message = '';

      try {
        const response = await fetch('http://localhost/project__67706406/api_php/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form)
        });

        const result = await response.json();
        this.message = result.message;
        this.messageType = result.success ? 'success' : 'danger';

        if (result.success) {
          // Save user to localStorage
          localStorage.setItem('user', JSON.stringify(result.user));
          
          // Redirect to files page
          setTimeout(() => {
            this.$router.push('/files');
            window.location.reload(); // Reload to update navbar
          }, 1000);
        }
      } catch (error) {
        this.message = 'Error: ' + error.message;
        this.messageType = 'danger';
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>