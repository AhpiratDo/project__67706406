<template>
  <div class="cyber-files-container">
    <div class="cyber-scan-line"></div>
    
    <div class="files-header">
      <div class="header-content">
        <div class="title-section">
          <h1 class="cyber-title">FILE SYSTEM</h1>
          <div class="file-count">[{{ files.length }} FILES DETECTED]</div>
        </div>
      </div>
    </div>

    <div class="files-content">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="cyber-loader">
          <div class="loader-ring"></div>
          <div class="loader-ring"></div>
          <div class="loader-ring"></div>
        </div>
        <div class="loading-text">SCANNING FILES...</div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <div class="error-icon">⚠</div>
        <div class="error-message">{{ error }}</div>
        <button @click="loadFiles" class="retry-button">RETRY</button>
      </div>

      <!-- No Files State -->
      <div v-else-if="files.length === 0" class="empty-state">
        <div class="empty-icon">📁</div>
        <div class="empty-text">NO FILES FOUND</div>
        <div class="empty-subtext">Upload your first file to get started</div>
      </div>

      <!-- Files Grid -->
      <div v-else class="files-grid">
        <div v-for="file in files" :key="file.id" class="file-card">
          <div class="file-header">
            <div class="file-icon">📄</div>
            <div class="file-id">#{{ file.id }}</div>
          </div>
          
          <div class="file-body">
            <div class="file-name">{{ file.filename }}</div>
            <div class="file-meta">
              <div class="meta-item">
                <span class="meta-label">TYPE:</span>
                <span class="meta-value">{{ file.filetype || 'UNKNOWN' }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-label">SIZE:</span>
                <span class="meta-value">{{ formatFileSize(file.filesize) }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-label">DATE:</span>
                <span class="meta-value">{{ formatDate(file.upload_date) }}</span>
              </div>
            </div>
          </div>

          <div class="file-actions">
            <button @click="downloadFile(file.id)" class="action-btn download" title="Download">
              <span class="btn-icon">⬇</span>
            </button>
            <button @click="createShareLink(file.id)" class="action-btn share" title="Share">
              <span class="btn-icon">🔗</span>
            </button>
            <button @click="deleteFile(file.id)" class="action-btn delete" title="Delete">
              <span class="btn-icon">🗑</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Share Modal -->
    <div v-if="showModal" class="modal-overlay" @click="showModal = false">
      <div class="share-modal" @click.stop>
        <div class="modal-header">
          <div class="modal-title">SHARE LINK GENERATED</div>
          <button class="modal-close" @click="showModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="share-link-container">
            <input type="text" :value="shareLink" readonly class="share-link-input" />
            <button @click="copyToClipboard" class="copy-btn">
              {{ copied ? '✓ COPIED' : 'COPY' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FileList',
  data() {
    return {
      files: [],
      loading: true,
      error: null,
      showModal: false,
      shareLink: '',
      copied: false
    };
  },
  mounted() {
    this.loadFiles();
  },
  methods: {
    async loadFiles() {
      this.loading = true;
      this.error = null;
      
      const userStr = localStorage.getItem('user');
      if (!userStr) {
        this.error = 'AUTHENTICATION REQUIRED';
        this.loading = false;
        setTimeout(() => this.$router.push('/login'), 2000);
        return;
      }

      try {
        const user = JSON.parse(userStr);
        const response = await fetch(`http://localhost/project__67706406/api_php/get_files.php?user_id=${user.id}`);
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const result = await response.json();
        
        if (result.success) {
          this.files = result.data || [];
        } else {
          this.error = result.message || 'FAILED TO LOAD FILES';
        }
      } catch (error) {
        this.error = 'SYSTEM ERROR: ' + error.message;
      } finally {
        this.loading = false;
      }
    },

    async downloadFile(fileId) {
      try {
        const response = await fetch(`http://localhost/project__67706406/api_php/download_file.php?file_id=${fileId}`);
        if (!response.ok) throw new Error('Download failed');

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'file';
        a.click();
        window.URL.revokeObjectURL(url);
      } catch (error) {
        alert('ERROR: ' + error.message);
      }
    },

    async deleteFile(fileId) {
      if (!confirm('DELETE THIS FILE?')) return;
      
      try {
        const response = await fetch('http://localhost/project__67706406/api_php/delete_file.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ file_id: fileId })
        });
        
        const result = await response.json();
        if (result.success) {
          this.files = this.files.filter(f => f.id !== fileId);
        } else {
          alert('DELETE FAILED: ' + result.message);
        }
      } catch (error) {
        alert('ERROR: ' + error.message);
      }
    },

    async createShareLink(fileId) {
      try {
        const response = await fetch('http://localhost/project__67706406/api_php/create_share_link.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ file_id: fileId })
        });
        
        const result = await response.json();
        if (result.success) {
          this.shareLink = result.share_link;
          this.showModal = true;
          this.copied = false;
        } else {
          alert('SHARE FAILED: ' + result.message);
        }
      } catch (error) {
        alert('ERROR: ' + error.message);
      }
    },

    copyToClipboard() {
      navigator.clipboard.writeText(this.shareLink);
      this.copied = true;
      setTimeout(() => { this.copied = false; }, 2000);
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

.cyber-files-container {
  min-height: 100vh;
  background: #0a0a0a;
  font-family: 'Orbitron', monospace;
  position: relative;
  padding: 2rem;
}

.cyber-scan-line {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, transparent, #00ffff, transparent);
  animation: scan 3s linear infinite;
  z-index: 100;
}

@keyframes scan {
  0% { transform: translateY(0); opacity: 1; }
  100% { transform: translateY(100vh); opacity: 0; }
}

.files-header {
  margin-bottom: 2rem;
}

.header-content {
  border: 2px solid #00ffff;
  padding: 1.5rem;
  background: rgba(0, 255, 255, 0.05);
  position: relative;
}

.header-content::before {
  content: '';
  position: absolute;
  top: -6px;
  left: 20px;
  width: 12px;
  height: 12px;
  background: #00ffff;
  clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
}

.title-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cyber-title {
  font-size: 2rem;
  font-weight: 900;
  color: #00ffff;
  text-shadow: 0 0 20px #00ffff;
  letter-spacing: 0.3em;
  margin: 0;
}

.file-count {
  color: #666;
  font-size: 0.9rem;
  letter-spacing: 0.1em;
}

.files-content {
  min-height: 60vh;
}

.loading-state,
.error-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
}

.cyber-loader {
  position: relative;
  width: 100px;
  height: 100px;
}

.loader-ring {
  position: absolute;
  border: 3px solid transparent;
  border-top-color: #00ffff;
  border-radius: 50%;
  animation: spin 1.5s linear infinite;
}

.loader-ring:nth-child(1) {
  width: 100px;
  height: 100px;
  animation-delay: 0s;
}

.loader-ring:nth-child(2) {
  width: 70px;
  height: 70px;
  top: 15px;
  left: 15px;
  animation-delay: 0.3s;
}

.loader-ring:nth-child(3) {
  width: 40px;
  height: 40px;
  top: 30px;
  left: 30px;
  animation-delay: 0.6s;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-text {
  margin-top: 2rem;
  color: #00ffff;
  font-size: 1rem;
  letter-spacing: 0.2em;
}

.error-state,
.empty-state {
  color: #666;
}

.error-icon,
.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.error-message,
.empty-text {
  font-size: 1.2rem;
  letter-spacing: 0.1em;
  margin-bottom: 0.5rem;
}

.empty-subtext {
  font-size: 0.9rem;
  color: #444;
}

.retry-button {
  margin-top: 1.5rem;
  padding: 0.8rem 2rem;
  background: transparent;
  border: 2px solid #ff0064;
  color: #ff0064;
  font-family: 'Orbitron', monospace;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.retry-button:hover {
  background: rgba(255, 0, 100, 0.1);
  box-shadow: 0 0 20px rgba(255, 0, 100, 0.5);
}

.files-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.file-card {
  background: rgba(0, 255, 255, 0.03);
  border: 1px solid rgba(0, 255, 255, 0.3);
  padding: 1.5rem;
  transition: all 0.3s ease;
  position: relative;
}

.file-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, transparent, #00ffff, transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.file-card:hover {
  background: rgba(0, 255, 255, 0.08);
  border-color: #00ffff;
  box-shadow: 0 0 20px rgba(0, 255, 255, 0.2);
  transform: translateY(-2px);
}

.file-card:hover::before {
  opacity: 1;
}

.file-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.file-icon {
  font-size: 2rem;
}

.file-id {
  color: #666;
  font-size: 0.75rem;
  letter-spacing: 0.1em;
}

.file-body {
  margin-bottom: 1rem;
}

.file-name {
  color: #00ffff;
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 1rem;
  word-break: break-word;
}

.file-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.meta-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
}

.meta-label {
  color: #666;
  letter-spacing: 0.1em;
}

.meta-value {
  color: #fff;
}

.file-actions {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}

.action-btn {
  padding: 0.6rem;
  background: transparent;
  border: 1px solid;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: 'Orbitron', monospace;
}

.action-btn.download {
  border-color: rgba(0, 255, 255, 0.5);
  color: #00ffff;
}

.action-btn.download:hover {
  background: rgba(0, 255, 255, 0.1);
  box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
}

.action-btn.share {
  border-color: rgba(255, 200, 0, 0.5);
  color: #ffc800;
}

.action-btn.share:hover {
  background: rgba(255, 200, 0, 0.1);
  box-shadow: 0 0 15px rgba(255, 200, 0, 0.3);
}

.action-btn.delete {
  border-color: rgba(255, 0, 100, 0.5);
  color: #ff0064;
}

.action-btn.delete:hover {
  background: rgba(255, 0, 100, 0.1);
  box-shadow: 0 0 15px rgba(255, 0, 100, 0.3);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.share-modal {
  width: 90%;
  max-width: 600px;
  background: #0a0a0a;
  border: 2px solid #ffc800;
  padding: 2rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-title {
  color: #ffc800;
  font-size: 1.2rem;
  font-weight: 700;
  letter-spacing: 0.2em;
}

.modal-close {
  background: transparent;
  border: none;
  color: #666;
  font-size: 1.5rem;
  cursor: pointer;
  transition: color 0.3s ease;
}

.modal-close:hover {
  color: #ffc800;
}

.share-link-container {
  display: flex;
  gap: 1rem;
}

.share-link-input {
  flex: 1;
  padding: 1rem;
  background: rgba(255, 200, 0, 0.05);
  border: 1px solid rgba(255, 200, 0, 0.3);
  color: #fff;
  font-family: 'Orbitron', monospace;
  font-size: 0.9rem;
  outline: none;
}

.copy-btn {
  padding: 1rem 2rem;
  background: transparent;
  border: 2px solid #ffc800;
  color: #ffc800;
  font-family: 'Orbitron', monospace;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.copy-btn:hover {
  background: rgba(255, 200, 0, 0.1);
  box-shadow: 0 0 20px rgba(255, 200, 0, 0.5);
}
</style>