<template>
  <div class="cyber-shared-container">
    <div class="shared-wrapper">
      <div class="shared-box">
        <div class="cyber-header">
          <div class="glitch" data-text="DOWNLOAD">DOWNLOAD</div>
          <div class="cyber-subtitle">RETRIEVE SHARED FILE</div>
          <div class="cyber-line"></div>
        </div>

        <div class="link-input-section">
          <label class="cyber-label">
            <span class="label-text">SHARE LINK</span>
            <span class="label-hex">[PASTE]</span>
          </label>
          <div class="link-input-wrapper">
            <input
              v-model="shareLink"
              type="text"
              class="cyber-input"
              placeholder="http://localhost:8080/shared/xxxxx"
              @keyup.enter="checkShareLink"
            />
            <button
              @click="checkShareLink"
              class="check-btn"
              :disabled="loading"
            >
              <span v-if="loading" class="btn-loader"></span>
              <span v-else>SCAN</span>
            </button>
          </div>
        </div>

        <div v-if="error" class="cyber-alert danger">
          <div class="alert-icon">⚠</div>
          <div class="alert-text">{{ error }}</div>
        </div>

        <div v-if="fileInfo" class="file-info-panel">
          <div class="panel-header">
            <div class="status-indicator"></div>
            <span class="status-text">FILE DETECTED</span>
          </div>
          
          <div class="file-details-grid">
            <div class="detail-card">
              <div class="card-icon">📄</div>
              <div class="card-content">
                <div class="card-label">FILENAME</div>
                <div class="card-value">{{ fileInfo.filename }}</div>
              </div>
            </div>

            <div class="detail-card">
              <div class="card-icon">📦</div>
              <div class="card-content">
                <div class="card-label">SIZE</div>
                <div class="card-value">{{ formatFileSize(fileInfo.filesize) }}</div>
              </div>
            </div>

            <div class="detail-card">
              <div class="card-icon">🔖</div>
              <div class="card-content">
                <div class="card-label">TYPE</div>
                <div class="card-value">{{ fileInfo.filetype || 'UNKNOWN' }}</div>
              </div>
            </div>

            <div class="detail-card">
              <div class="card-icon">📅</div>
              <div class="card-content">
                <div class="card-label">UPLOADED</div>
                <div class="card-value">{{ formatDate(fileInfo.upload_date) }}</div>
              </div>
            </div>
          </div>

          <button
            @click="downloadFile"
            class="download-button"
            :disabled="downloading"
          >
            <span class="button-content">
              <span v-if="downloading">
                <span class="loader"></span>
                <span>DOWNLOADING...</span>
              </span>
              <span v-else>{{ '> DOWNLOAD FILE_' }}</span>
            </span>
            <div class="button-glow"></div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SharedDownload',
  data() {
    return {
      shareLink: '',
      fileInfo: null,
      loading: false,
      downloading: false,
      error: ''
    };
  },
  methods: {
    async checkShareLink() {
      if (!this.shareLink.trim()) {
        this.error = 'ERROR: LINK REQUIRED';
        return;
      }

      this.loading = true;
      this.error = '';
      this.fileInfo = null;

      try {
        const shareCode = this.shareLink.includes('/')
          ? this.shareLink.split('/').pop()
          : this.shareLink;

        const response = await fetch(
          `http://localhost/project__67706406/api_php/get_shared_file.php?share_link=${shareCode}`
        );

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('INVALID SERVER RESPONSE');
        }

        const result = await response.json();

        if (result.success) {
          this.fileInfo = result.data;
        } else {
          this.error = 'ERROR: ' + (result.message || 'FILE NOT FOUND');
        }
      } catch (error) {
        this.error = 'ERROR: ' + error.message;
      } finally {
        this.loading = false;
      }
    },

    async downloadFile() {
      if (!this.fileInfo) return;

      this.downloading = true;
      this.error = '';

      try {
        const shareCode = this.shareLink.includes('/')
          ? this.shareLink.split('/').pop()
          : this.shareLink;

        const response = await fetch(
          `http://localhost/project__67706406/api_php/download_shared.php?share_link=${shareCode}`
        );

        if (!response.ok) {
          throw new Error('DOWNLOAD FAILED');
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = this.fileInfo.filename || 'download';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      } catch (error) {
        this.error = 'ERROR: ' + error.message;
      } finally {
        this.downloading = false;
      }
    },

    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: '2-digit',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&display=swap');

.cyber-shared-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #0a0a0a 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Orbitron', monospace;
  padding: 2rem;
}

.shared-wrapper {
  width: 100%;
  max-width: 700px;
}

.shared-box {
  background: rgba(10, 10, 10, 0.9);
  border: 2px solid #ffc800;
  padding: 3rem;
  box-shadow: 0 0 30px rgba(255, 200, 0, 0.3);
}

.cyber-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.glitch {
  font-size: 2.5rem;
  font-weight: 900;
  color: #ffc800;
  text-shadow: 0 0 20px #ffc800;
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
  background: linear-gradient(90deg, transparent, #ffc800, transparent);
  margin-top: 1rem;
}

.link-input-section {
  margin-bottom: 2rem;
}

.cyber-label {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.8rem;
  color: #ffc800;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.1em;
}

.label-hex {
  color: #666;
  font-size: 0.75rem;
}

.link-input-wrapper {
  display: flex;
  gap: 1rem;
}

.cyber-input {
  flex: 1;
  padding: 1rem;
  background: rgba(255, 200, 0, 0.03);
  border: 1px solid rgba(255, 200, 0, 0.3);
  color: #fff;
  font-family: 'Orbitron', monospace;
  transition: all 0.3s ease;
  outline: none;
}

.cyber-input:focus {
  background: rgba(255, 200, 0, 0.08);
  border-color: #ffc800;
  box-shadow: 0 0 15px rgba(255, 200, 0, 0.3);
}

.check-btn {
  padding: 1rem 2rem;
  background: transparent;
  border: 2px solid #ffc800;
  color: #ffc800;
  font-family: 'Orbitron', monospace;
  font-weight: 700;
  letter-spacing: 0.1em;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.check-btn:hover:not(:disabled) {
  background: rgba(255, 200, 0, 0.1);
  box-shadow: 0 0 20px rgba(255, 200, 0, 0.5);
}

.check-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-loader {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 200, 0, 0.3);
  border-top-color: #ffc800;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.cyber-alert {
  padding: 1rem;
  border: 1px solid;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 2rem;
}

.cyber-alert.danger {
  background: rgba(255, 0, 100, 0.1);
  border-color: #ff0064;
  color: #ff0064;
}

.alert-icon {
  font-size: 1.5rem;
}

.alert-text {
  font-size: 0.85rem;
  letter-spacing: 0.05em;
}

.file-info-panel {
  border: 1px solid rgba(255, 200, 0, 0.3);
  padding: 2rem;
  background: rgba(255, 200, 0, 0.02);
}

.panel-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(255, 200, 0, 0.2);
}

.status-indicator {
  width: 12px;
  height: 12px;
  background: #00ff00;
  border-radius: 50%;
  box-shadow: 0 0 15px #00ff00;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.status-text {
  color: #ffc800;
  font-size: 0.9rem;
  font-weight: 700;
  letter-spacing: 0.15em;
}

.file-details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.detail-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(255, 200, 0, 0.05);
  border: 1px solid rgba(255, 200, 0, 0.2);
  transition: all 0.3s ease;
}

.detail-card:hover {
  background: rgba(255, 200, 0, 0.1);
  border-color: rgba(255, 200, 0, 0.5);
}

.card-icon {
  font-size: 2rem;
}

.card-content {
  flex: 1;
}

.card-label {
  color: #666;
  font-size: 0.7rem;
  letter-spacing: 0.1em;
  margin-bottom: 0.3rem;
}

.card-value {
  color: #ffc800;
  font-size: 0.85rem;
  font-weight: 700;
  word-break: break-word;
}

.download-button {
  width: 100%;
  padding: 1.2rem;
  background: transparent;
  border: 2px solid #ffc800;
  color: #ffc800;
  font-family: 'Orbitron', monospace;
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: 0.2em;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

.download-button:hover:not(:disabled) {
  background: rgba(255, 200, 0, 0.1);
  box-shadow: 0 0 30px rgba(255, 200, 0, 0.5);
}

.download-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.button-content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.loader {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 200, 0, 0.3);
  border-top-color: #ffc800;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.button-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: radial-gradient(circle, rgba(255, 200, 0, 0.3), transparent);
  transform: translate(-50%, -50%);
  transition: width 0.5s ease, height 0.5s ease;
}

.download-button:hover:not(:disabled) .button-glow {
  width: 400px;
  height: 400px;
}

@media (max-width: 768px) {
  .file-details-grid {
    grid-template-columns: 1fr;
  }

  .link-input-wrapper {
    flex-direction: column;
  }

  .check-btn {
    width: 100%;
  }
}
</style>