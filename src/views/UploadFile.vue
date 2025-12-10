<template>
  <div class="cyber-upload-container">
    <div class="upload-wrapper">
      <div class="upload-box">
        <div class="cyber-header">
          <div class="glitch" data-text="UPLOAD">UPLOAD</div>
          <div class="cyber-subtitle">TRANSFER FILES TO SYSTEM</div>
          <div class="cyber-line"></div>
        </div>

        <div class="upload-area" :class="{ 'drag-over': isDragging }"
             @dragover.prevent="isDragging = true"
             @dragleave.prevent="isDragging = false"
             @drop.prevent="handleDrop">
          <div v-if="!selectedFile" class="upload-prompt">
            <div class="upload-icon">📁</div>
            <div class="upload-text">DRAG & DROP FILE HERE</div>
            <div class="upload-or">OR</div>
            <label class="file-select-btn">
              <input type="file" @change="handleFileChange" ref="fileInput" style="display: none" />
              <span>SELECT FILE</span>
            </label>
          </div>

          <div v-else class="file-preview">
            <div class="preview-header">
              <div class="file-type-icon">📄</div>
              <button @click="clearFile" class="clear-btn">✕</button>
            </div>
            <div class="file-details">
              <div class="detail-row">
                <span class="detail-label">NAME:</span>
                <span class="detail-value">{{ selectedFile.name }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">SIZE:</span>
                <span class="detail-value">{{ formatFileSize(selectedFile.size) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">TYPE:</span>
                <span class="detail-value">{{ selectedFile.type || 'UNKNOWN' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="cyber-label">
            <span class="label-text">FILENAME</span>
            <span class="label-hex">[OPTIONAL]</span>
          </label>
          <div class="input-wrapper">
            <input
              v-model="filename"
              type="text"
              class="cyber-input"
              placeholder="Custom filename..."
              :disabled="!selectedFile"
            />
            <div class="input-border"></div>
          </div>
        </div>

        <button
          @click="uploadFile"
          class="cyber-button"
          :disabled="uploading || !selectedFile"
        >
          <span class="button-content">
            <span v-if="uploading" class="upload-progress">
              <span class="loader"></span>
              <span class="progress-text">{{ uploadProgress }}%</span>
            </span>
            <span v-else>{{ '> INITIATE UPLOAD_' }}</span>
          </span>
          <div class="button-glow"></div>
        </button>

        <div v-if="message" :class="['cyber-alert', messageType]">
          <div class="alert-icon">{{ messageType === 'success' ? '✓' : '⚠' }}</div>
          <div class="alert-text">{{ message }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UploadFile',
  data() {
    return {
      selectedFile: null,
      filename: '',
      message: '',
      messageType: '',
      uploading: false,
      uploadProgress: 0,
      isDragging: false
    };
  },
  methods: {
    handleFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.selectedFile = file;
        this.filename = file.name;
        this.message = '';
      }
    },

    handleDrop(event) {
      this.isDragging = false;
      const file = event.dataTransfer.files[0];
      if (file) {
        this.selectedFile = file;
        this.filename = file.name;
        this.message = '';
      }
    },

    clearFile() {
      this.selectedFile = null;
      this.filename = '';
      this.message = '';
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },

    async uploadFile() {
      if (!this.selectedFile) {
        this.message = 'ERROR: NO FILE SELECTED';
        this.messageType = 'danger';
        return;
      }

      const userStr = localStorage.getItem('user');
      if (!userStr) {
        this.message = 'ERROR: AUTHENTICATION REQUIRED';
        this.messageType = 'danger';
        setTimeout(() => this.$router.push('/login'), 1500);
        return;
      }

      const user = JSON.parse(userStr);

      this.uploading = true;
      this.uploadProgress = 0;
      this.message = '';

      const formData = new FormData();
      formData.append('file', this.selectedFile);
      formData.append('filename', this.filename);
      formData.append('user_id', user.id);

      try {
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', (e) => {
          if (e.lengthComputable) {
            this.uploadProgress = Math.round((e.loaded / e.total) * 100);
          }
        });

        xhr.addEventListener('load', () => {
          if (xhr.status === 200) {
            try {
              const result = JSON.parse(xhr.responseText);
              this.message = result.success ? 'UPLOAD SUCCESSFUL' : 'UPLOAD FAILED: ' + result.message;
              this.messageType = result.success ? 'success' : 'danger';

              if (result.success) {
                setTimeout(() => {
                  this.clearFile();
                  this.message = '';
                }, 2000);
              }
            } catch (e) {
              this.message = 'ERROR: INVALID RESPONSE';
              this.messageType = 'danger';
            }
          } else {
            this.message = `ERROR: SERVER RETURNED ${xhr.status}`;
            this.messageType = 'danger';
          }
          this.uploading = false;
        });

        xhr.addEventListener('error', () => {
          this.message = 'ERROR: NETWORK FAILURE';
          this.messageType = 'danger';
          this.uploading = false;
        });

        xhr.open('POST', 'http://localhost/project__67706406/api_php/upload_file.php');
        xhr.send(formData);

      } catch (error) {
        this.message = 'ERROR: ' + error.message;
        this.messageType = 'danger';
        this.uploading = false;
      }
    },

    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&display=swap');

.cyber-upload-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #0a0a0a 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Orbitron', monospace;
  padding: 2rem;
}

.upload-wrapper {
  width: 100%;
  max-width: 600px;
}

.upload-box {
  background: rgba(10, 10, 10, 0.9);
  border: 2px solid #00ffff;
  padding: 3rem;
  box-shadow: 0 0 30px rgba(0, 255, 255, 0.3);
  position: relative;
}

.cyber-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.glitch {
  font-size: 2.5rem;
  font-weight: 900;
  color: #00ffff;
  text-shadow: 0 0 20px #00ffff;
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
  background: linear-gradient(90deg, transparent, #00ffff, transparent);
  margin-top: 1rem;
}

.upload-area {
  border: 2px dashed rgba(0, 255, 255, 0.3);
  padding: 3rem 2rem;
  text-align: center;
  transition: all 0.3s ease;
  margin-bottom: 2rem;
  background: rgba(0, 255, 255, 0.02);
}

.upload-area.drag-over {
  border-color: #00ffff;
  background: rgba(0, 255, 255, 0.1);
  box-shadow: inset 0 0 30px rgba(0, 255, 255, 0.2);
}

.upload-prompt {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.upload-icon {
  font-size: 4rem;
  opacity: 0.5;
}

.upload-text {
  color: #00ffff;
  font-size: 1rem;
  letter-spacing: 0.1em;
}

.upload-or {
  color: #666;
  font-size: 0.9rem;
  margin: 0.5rem 0;
}

.file-select-btn {
  padding: 0.8rem 2rem;
  background: transparent;
  border: 2px solid #00ffff;
  color: #00ffff;
  font-family: 'Orbitron', monospace;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  letter-spacing: 0.1em;
}

.file-select-btn:hover {
  background: rgba(0, 255, 255, 0.1);
  box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
}

.file-preview {
  text-align: left;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.file-type-icon {
  font-size: 3rem;
}

.clear-btn {
  background: transparent;
  border: 1px solid #ff0064;
  color: #ff0064;
  font-size: 1.2rem;
  width: 35px;
  height: 35px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.clear-btn:hover {
  background: rgba(255, 0, 100, 0.1);
  box-shadow: 0 0 15px rgba(255, 0, 100, 0.5);
}

.file-details {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(0, 255, 255, 0.1);
}

.detail-label {
  color: #666;
  font-size: 0.85rem;
  letter-spacing: 0.1em;
}

.detail-value {
  color: #00ffff;
  font-size: 0.85rem;
  word-break: break-all;
}

.form-group {
  margin-bottom: 2rem;
}

.cyber-label {
  display: flex;
  justify-content: space-between;
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
  transition: all 0.3s ease;
  outline: none;
}

.cyber-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.cyber-input:focus {
  background: rgba(0, 255, 255, 0.08);
  border-color: #00ffff;
  box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
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
}

.cyber-button:hover:not(:disabled) {
  background: rgba(0, 255, 255, 0.1);
  box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
}

.cyber-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.button-content {
  position: relative;
  z-index: 1;
}

.upload-progress {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
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

.progress-text {
  font-size: 0.9rem;
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
  font-size: 1.5rem;
}
</style>