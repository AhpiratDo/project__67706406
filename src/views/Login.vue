<template>
  <div class="cyber-container">
    <div class="cyber-grid"></div>
    <div class="cyber-glow"></div>
    
    <div class="login-wrapper">
      <div class="login-box">
        <div class="cyber-header">
          <div class="glitch" data-text="LOGIN">LOGIN</div>
          <div class="cyber-line"></div>
        </div>

        <div class="login-form">
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
                placeholder="Enter username or email"
                @keyup.enter="login"
              />
              <div class="input-border"></div>
            </div>
          </div>

          <div class="form-group">
            <label class="cyber-label">
              <span class="label-text">PASSWORD</span>
              <span class="label-hex">[0x02]</span>
            </label>
            <div class="input-wrapper">
              <input
                v-model="form.password"
                type="password"
                class="cyber-input"
                placeholder="Enter password"
                @keyup.enter="login"
              />
              <div class="input-border"></div>
            </div>
          </div>

          <button
            @click="login"
            class="cyber-button"
            :disabled="loading"
          >
            <span class="button-content">
              <span v-if="loading" class="loader"></span>
              <span v-else>{{ '> CONNECT_' }}</span>
            </span>
            <div class="button-glow"></div>
          </button>

          <div class="register-link">
            <span class="link-text">NEW_USER?</span>
            <router-link to="/register" class="cyber-link">
              [REGISTER]
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
        this.message = 'AUTHENTICATION_ERROR: MISSING_CREDENTIALS';
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
        this.message = result.success ? 'ACCESS_GRANTED' : 'ACCESS_DENIED: ' + result.message;
        this.messageType = result.success ? 'success' : 'danger';

        if (result.success) {
          localStorage.setItem('user', JSON.stringify(result.user));
          setTimeout(() => {
            this.$router.push('/files');
          }, 1000);
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
}

.cyber-grid {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    linear-gradient(rgba(0, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0, 255, 255, 0.03) 1px, transparent 1px);
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
  background: radial-gradient(circle, rgba(0, 255, 255, 0.1) 0%, transparent 70%);
  animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}

.login-wrapper {
  position: relative;
  z-index: 1;
}

.login-box {
  width: 450px;
  background: rgba(10, 10, 10, 0.9);
  border: 2px solid #00ffff;
  border-radius: 0;
  padding: 3rem;
  box-shadow: 
    0 0 20px rgba(0, 255, 255, 0.3),
    inset 0 0 30px rgba(0, 255, 255, 0.05);
  position: relative;
}

.login-box::before,
.login-box::after {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  border: 2px solid #00ffff;
}

.login-box::before {
  top: -2px;
  left: -2px;
  border-right: none;
  border-bottom: none;
}

.login-box::after {
  bottom: -2px;
  right: -2px;
  border-left: none;
  border-top: none;
}

.cyber-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.glitch {
  font-size: 3rem;
  font-weight: 900;
  color: #00ffff;
  text-shadow: 
    0 0 10px #00ffff,
    0 0 20px #00ffff,
    0 0 30px #00ffff;
  letter-spacing: 0.3em;
  position: relative;
  animation: glitchText 3s infinite;
}

@keyframes glitchText {
  0%, 90%, 100% { transform: translate(0); }
  92% { transform: translate(-2px, 2px); }
  94% { transform: translate(2px, -2px); }
  96% { transform: translate(-2px, -2px); }
  98% { transform: translate(2px, 2px); }
}

.cyber-line {
  height: 2px;
  background: linear-gradient(90deg, transparent, #00ffff, transparent);
  margin-top: 1rem;
  animation: lineScan 2s ease-in-out infinite;
}

@keyframes lineScan {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 1; }
}

.form-group {
  margin-bottom: 1.5rem;
}

.cyber-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  color: #00ffff;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.1em;
}

.label-hex {
  color: #666;
  font-size: 0.75rem;
}

.input-wrapper {
  position: relative;
}

.cyber-input {
  width: 100%;
  padding: 0.9rem 1rem;
  background: rgba(0, 255, 255, 0.03);
  border: 1px solid rgba(0, 255, 255, 0.3);
  color: #fff;
  font-family: 'Orbitron', monospace;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  outline: none;
}

.cyber-input:focus {
  background: rgba(0, 255, 255, 0.08);
  border-color: #00ffff;
  box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
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
  background: #00ffff;
  transition: width 0.3s ease;
}

.cyber-input:focus + .input-border {
  width: 100%;
}

.cyber-button {
  width: 100%;
  padding: 1rem;
  background: transparent;
  border: 2px solid #00ffff;
  color: #00ffff;
  font-family: 'Orbitron', monospace;
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: 0.2em;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  margin-top: 2rem;
}

.cyber-button:hover:not(:disabled) {
  background: rgba(0, 255, 255, 0.1);
  box-shadow: 
    0 0 20px rgba(0, 255, 255, 0.5),
    inset 0 0 20px rgba(0, 255, 255, 0.1);
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
  background: radial-gradient(circle, rgba(0, 255, 255, 0.3), transparent);
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
  border: 2px solid rgba(0, 255, 255, 0.3);
  border-top-color: #00ffff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.register-link {
  text-align: center;
  margin-top: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.link-text {
  color: #666;
  font-size: 0.85rem;
  letter-spacing: 0.1em;
}

.cyber-link {
  color: #00ffff;
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
  background: #00ffff;
  transition: width 0.3s ease;
}

.cyber-link:hover {
  text-shadow: 0 0 10px #00ffff;
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
  font-size: 0.85rem;
  letter-spacing: 0.05em;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
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