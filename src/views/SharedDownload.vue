<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow-lg border-0">
          <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)">
            <h3 class="mb-0">Download from Share Link</h3>
          </div>
          <div class="card-body p-4">
            <div class="mb-4">
              <label class="form-label fw-bold">Paste share link</label>
              <div class="input-group input-group-lg">
                <input 
                  v-model="shareLink" 
                  type="text" 
                  class="form-control" 
                  placeholder="http://localhost:8080/shared/xxxxx"
                  @keyup.enter="checkShareLink"
                />
                <button 
                  @click="checkShareLink" 
                  class="btn btn-primary" 
                  :disabled="loading"
                >
                  {{ loading ? 'Checking...' : 'Check' }}
                </button>
              </div>
            </div>

            <!-- Error Display -->
            <div v-if="error" class="alert alert-danger">
              <strong>Error:</strong> {{ error }}
            </div>

            <!-- File Info Card -->
            <div v-if="fileInfo" class="card bg-light">
              <div class="card-body">
                <h5 class="card-title mb-3">{{ fileInfo.filename }}</h5>
                <hr />
                <div class="row mb-3">
                  <div class="col-md-6 mb-2">
                    <small class="text-muted">Type:</small>
                    <p class="mb-0"><strong>{{ fileInfo.filetype || 'Unknown' }}</strong></p>
                  </div>
                  <div class="col-md-6 mb-2">
                    <small class="text-muted">Size:</small>
                    <p class="mb-0"><strong>{{ formatFileSize(fileInfo.filesize) }}</strong></p>
                  </div>
                  <div class="col-md-12 mb-2">
                    <small class="text-muted">Uploaded:</small>
                    <p class="mb-0"><strong>{{ formatDate(fileInfo.upload_date) }}</strong></p>
                  </div>
                </div>
                <button 
                  @click="downloadFile" 
                  class="btn btn-success btn-lg w-100"
                  :disabled="downloading"
                >
                  <span v-if="downloading" class="spinner-border spinner-border-sm me-2"></span>
                  {{ downloading ? 'Downloading...' : 'Download File' }}
                </button>
              </div>
            </div>
          </div>
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
        this.error = 'Please enter a link';
        return;
      }

      this.loading = true;
      this.error = '';
      this.fileInfo = null;

      try {
        // ดึง share code จาก URL
        const shareCode = this.shareLink.includes('/') 
          ? this.shareLink.split('/').pop() 
          : this.shareLink;

        console.log('Checking share code:', shareCode);

        const response = await fetch(
          `http://localhost/project__67706406/api_php/get_shared_file.php?share_link=${shareCode}`
        );

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('Share link result:', result);

        if (result.success) {
          this.fileInfo = result.data;
        } else {
          this.error = result.message || 'File not found or link has expired';
        }
      } catch (error) {
        console.error('Error checking share link:', error);
        this.error = 'Cannot connect to server: ' + error.message;
      } finally {
        this.loading = false;
      }
    },

    async downloadFile() {
      if (!this.fileInfo) return;

      this.downloading = true;
      try {
        const shareCode = this.shareLink.includes('/') 
          ? this.shareLink.split('/').pop() 
          : this.shareLink;

        const response = await fetch(
          `http://localhost/project__67706406/api_php/download_shared.php?share_link=${shareCode}`
        );

        if (!response.ok) {
          throw new Error('ไม่สามารถดาวน์โหลดไฟล์ได้');
        }

        // ดึง filename จาก Content-Disposition header
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = this.fileInfo.filename || 'download';
        if (contentDisposition) {
          const matches = /filename="([^"]+)"/.exec(contentDisposition);
          if (matches && matches[1]) {
            filename = matches[1];
          }
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      } catch (error) {
        console.error('Error downloading file:', error);
        this.error = 'ไม่สามารถดาวน์โหลดไฟล์ได้: ' + error.message;
      } finally {
        this.downloading = false;
      }
    },

    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
      return (bytes / 1024 / 1024).toFixed(2) + ' MB';
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
};
</script>

<style scoped>
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
  border-width: 0.15em;
}
</style>