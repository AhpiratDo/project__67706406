<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0">
          <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)">
            <h3 class="mb-0 text-center">Register Account</h3>
          </div>
          <div class="card-body p-4">
            <div class="mb-3">
              <label class="form-label fw-bold">Username</label>
              <input
                v-model="form.username"
                type="text"
                class="form-control"
                placeholder="Enter username"
                @keyup.enter="register"
              />
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="form-control"
                placeholder="Enter email"
                @keyup.enter="register"
              />
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Password</label>
              <input
                v-model="form.password"
                type="password"
                class="form-control"
                placeholder="Enter password (min 6 characters)"
                @keyup.enter="register"
              />
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Confirm Password</label>
              <input
                v-model="form.confirmPassword"
                type="password"
                class="form-control"
                placeholder="Confirm password"
                @keyup.enter="register"
              />
            </div>

            <button
              @click="register"
              class="btn btn-lg w-100 text-white mb-3"
              style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)"
              :disabled="loading"
            >
              {{ loading ? 'Registering...' : 'Register' }}
            </button>

            <div class="text-center">
              <small>Already have an account? <router-link to="/login" class="text-decoration-none">Login here</router-link></small>
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
  name: 'Register',
  data() {
    return {
      form: {
        username: '',
        email: '',
        password: '',
        confirmPassword: ''
      },
      message: '',
      messageType: '',
      loading: false
    };
  },
  methods: {
    async register() {
      // Validate
      if (!this.form.username || !this.form.email || !this.form.password) {
        this.message = 'Please fill in all fields';
        this.messageType = 'danger';
        return;
      }

      if (this.form.password !== this.form.confirmPassword) {
        this.message = 'Passwords do not match';
        this.messageType = 'danger';
        return;
      }

      if (this.form.password.length < 6) {
        this.message = 'Password must be at least 6 characters';
        this.messageType = 'danger';
        return;
      }

      this.loading = true;
      this.message = '';

      try {
        const response = await fetch('http://localhost/project__67706406/api_php/register.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            username: this.form.username,
            email: this.form.email,
            password: this.form.password
          })
        });

        const result = await response.json();
        this.message = result.message;
        this.messageType = result.success ? 'success' : 'danger';

        if (result.success) {
          setTimeout(() => {
            this.$router.push('/login');
          }, 1500);
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