<template>
  <div class="cyber-container">
    <div class="cyber-grid"></div>
    <div class="cyber-glow"></div>
    
    <div class="register-wrapper">
      <div class="register-box">
        <div class="cyber-header">
          <div class="glitch" data-text="REGISTER">REGISTER</div>
          <div class="cyber-subtitle">CREATE NEW ACCOUNT</div>
          <div class="cyber-line"></div>
        </div>

        <div class="register-form">
          <div class="form-group">
            <label class="cyber-label">
              <span class="label-text">USERNAME</span>
              <span class="label-hex">[0x01]</span>
            </label>
            <div class="input-wrapper">
              <input
                v-model="form.username"
                type="text"
                class="cyber-input"
                placeholder="Choose username"
                @keyup.enter="register"
              />
              <div class="input-border"></div>
            </div>
          </div>

          <div class="form-group">
            <label class="cyber-label">
              <span class="label-text">EMAIL</span>
              <span class="label-hex">[0x02]</span>
            </label>
            <div class="input-wrapper">
              <input
                v-model="form.email"
                type="email"
                class="cyber-input"
                placeholder="Enter email address"
                @keyup.enter="register"
              />
              <div class="input-border"></div>
            </div>
          </div>

          <div class="form-group">
            <label class="cyber-label">
              <span class="label-text">PASSWORD</span>
              <span class="label-hex">[0x03]</span>
            </label>
            <div class="input-wrapper">
              <input
                v-model="form.password"
                type="password"
                class="cyber-input"
                placeholder="Min 6 characters"
                @keyup.enter="register"
              />
              <div class="input-border"></div>
            </div>
          </div>

          <div class="form-group">
            <label class="cyber-label">
              <span class="label-text">CONFIRM PASSWORD</span>
              <span class="label-hex">[0x04]</span>
            </label>
            <div class="input-wrapper">
              <input
                v-model="form.confirmPassword"
                type="password"
                class="cyber-input"
                placeholder="Re-enter password"
                @keyup.enter="register"
              />
              <div class="input-border"></div>
            </div>
          </div>

          <button
            @click="register"
            class="cyber-button"
            :disabled="loading"
          >
            <span class="button-content">
              <span v-if="loading" class="loader"></span>
              <span v-else>{{ '> INITIALIZE_' }}</span>
            </span>
            <div class="button-glow"></div>
          </button>

          <div class="login-link">
            <span class="link-text">ALREADY_REGISTERED?</span>
            <router-link to="/login" class="cyber-link">
              [LOGIN]
            </router-link>
          </div>

          <div v-if="message" :class="['cyber-alert', messageType]">
            <div class="alert-icon">⚠</div>
            <div class="alert-text">{{ message }}</div>
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
      if (!this.form.username || !this.form.email || !this.form.password) {
        this.message = 'ERROR: ALL_FIELDS_REQUIRED';
        this.messageType = 'danger';
        return;
      }

      if (this.form.password !== this.form.confirmPassword) {
        this.message = 'ERROR: PASSWORDS_DO_NOT_MATCH';
        this.messageType = 'danger';
        return;
      }

      if (this.form.password.length < 6) {
        this.message = 'ERROR: PASSWORD_TOO_SHORT';
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
        this.message = result.success ? 'ACCOUNT_CREATED_SUCCESSFULLY' : 'ERROR: ' + result.message;
        this.messageType = result.success ? 'success' : 'danger';

        if (result.success) {
          setTimeout(() => {
            this.$router.push('/login');
          }, 1500);
        }
      } catch (error) {
        this.message = 'SYSTEM_ERROR: ' + error.message;
        this.messageType = 'danger';
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&display=swap');

.cyber-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #0a0a0a 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  font-family: 'Orbitron', monospace;
  padding: 2rem 0;
}

.cyber-grid {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    linear-gradient(rgba(255, 0, 100, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 0, 100, 0.03) 1px, transparent 1px);
  background-size: 50px 50px;
  animation: gridMove 20s linear infinite;
}

@keyframes gridMove {
  0% { transform: perspective(500px) rotateX(60deg) translateY(0); }
  100% { transform: perspective(500px) rotateX(60deg) translateY(50px); }
}

.cyber-glow {
  position: absolute;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(255, 0, 100, 0.1) 0%, transparent 70%);
  animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}

.register-wrapper {
  position: relative;
  z-index: 1;
}

.register-box {
  width: 500px;
  background: rgba(10, 10, 10, 0.9);
  border: 2px solid #ff0064;
  padding: 3rem;
  box-shadow: 
    0 0 20px rgba(255, 0, 100, 0.3),
    inset 0 0 30px rgba(255, 0, 100, 0.05);
  position: relative;
}

.register-box::before,
.register-box::after {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  border: 2px solid #ff0064;
}

.register-box::before {
  top: -2px;
  left: -2px;
  border-right: none;
  border-bottom: none;
}

.register-box::after {
  bottom: -2px;
  right: -2px;
  border-left: none;
  border-top: none;
}

.cyber-header {
  text-align: center;
  margin-bottom: 2rem;
}

.glitch {
  font-size: 2.5rem;
  font-weight: 900;
  color: #ff0064;
  text-shadow: 
    0 0 10px #ff0064,
    0 0 20px #ff0064;
  letter-spacing: 0.3em;
}

.cyber-subtitle {
  color: #666;
  font-size: 0.75rem;
  letter-spacing: 0.2em;
  margin-top: 0.5rem;
}

.cyber-line {
  height: 2px;
  background: linear-gradient(90deg, transparent, #ff0064, transparent);
  margin-top: 1rem;
}

.form-group {
  margin-bottom: 1.3rem;
}

.cyber-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  color: #ff0064;
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.1em;
}

.label-hex {
  color: #666;
  font-size: 0.7rem;
}

.input-wrapper {
  position: relative;
}

.cyber-input {
  width: 100%;
  padding: 0.8rem 1rem;
  background: rgba(255, 0, 100, 0.03);
  border: 1px solid rgba(255, 0, 100, 0.3);
  color: #fff;
  font-family: 'Orbitron', monospace;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  outline: none;
}

.cyber-input:focus {
  background: rgba(255, 0, 100, 0.08);
  border-color: #ff0064;
  box-shadow: 0 0 15px rgba(255, 0, 100, 0.3);
}

.cyber-input::placeholder {
  color: rgba(255, 255, 255, 0.3);
}

.input-border {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: #ff0064;
  transition: width 0.3s ease;
}

.cyber-input:focus + .input-border {
  width: 100%;
}

.cyber-button {
  width: 100%;
  padding: 1rem;
  background: transparent;
  border: 2px solid #ff0064;
  color: #ff0064;
  font-family: 'Orbitron', monospace;
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: 0.2em;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  margin-top: 1.5rem;
}

.cyber-button:hover:not(:disabled) {
  background: rgba(255, 0, 100, 0.1);
  box-shadow: 
    0 0 20px rgba(255, 0, 100, 0.5),
    inset 0 0 20px rgba(255, 0, 100, 0.1);
}

.cyber-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.button-content {
  position: relative;
  z-index: 1;
}

.button-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: radial-gradient(circle, rgba(255, 0, 100, 0.3), transparent);
  transform: translate(-50%, -50%);
  transition: width 0.5s ease, height 0.5s ease;
}

.cyber-button:hover:not(:disabled) .button-glow {
  width: 300px;
  height: 300px;
}

.loader {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 0, 100, 0.3);
  border-top-color: #ff0064;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.login-link {
  text-align: center;
  margin-top: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.link-text {
  color: #666;
  font-size: 0.8rem;
  letter-spacing: 0.1em;
}

.cyber-link {
  color: #ff0064;
  text-decoration: none;
  font-weight: 700;
  letter-spacing: 0.1em;
  position: relative;
  transition: all 0.3s ease;
}

.cyber-link::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: #ff0064;
  transition: width 0.3s ease;
}

.cyber-link:hover {
  text-shadow: 0 0 10px #ff0064;
}

.cyber-link:hover::after {
  width: 100%;
}

.cyber-alert {
  margin-top: 1.5rem;
  padding: 1rem;
  border: 1px solid;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.8rem;
  letter-spacing: 0.05em;
}

.cyber-alert.success {
  background: rgba(0, 255, 0, 0.1);
  border-color: #00ff00;
  color: #00ff00;
}

.cyber-alert.danger {
  background: rgba(255, 0, 100, 0.1);
  border-color: #ff0064;
  color: #ff0064;
}

.alert-icon {
  font-size: 1.2rem;
  animation: blink 1s infinite;
}

@keyframes blink {
  0%, 50%, 100% { opacity: 1; }
  25%, 75% { opacity: 0.3; }
}
</style>