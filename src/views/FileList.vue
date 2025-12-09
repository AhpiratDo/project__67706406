<template>
  <div class="container mt-5">
    <div class="card shadow-lg border-0">
      <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%)">
        <h3 class="mb-0">My Files ({{ files.length }} files)</h3>
      </div>
      <div class="card-body p-0">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-3">Loading...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger m-4">
          <h5>เกิดข้อผิดพลาด</h5>
          <p>{{ error }}</p>
          <button @click="loadFiles" class="btn btn-primary btn-sm">ลองอีกครั้ง</button>
        </div>

        <!-- No Files State -->
        <div v-else-if="files.length === 0" class="text-center text-muted py-5">
          <p class="fs-5">ยังไม่มีไฟล์</p>
          <p class="text-muted">ลองอัปโหลดไฟล์ดูสิครับ</p>
        </div>

        <!-- Files Table -->
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th class="px-4">Filename</th>
                <th>Type</th>
                <th class="text-center">Size</th>
                <th class="text-center">Upload Date</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="file in files" :key="file.id">
                <td class="px-4">{{ file.filename }}</td>
                <td><span class="badge bg-secondary">{{ file.filetype || 'N/A' }}</span></td>
                <td class="text-center"><span class="badge bg-light text-dark">{{ formatFileSize(file.filesize) }}</span></td>
                <td class="text-center"><small>{{ formatDate(file.upload_date) }}</small></td>
                <td class="text-center">
                  <div class="btn-group">
                    <button @click="downloadFile(file.id)" class="btn btn-sm btn-outline-info">Download</button>
                    <button @click="createShareLink(file.id)" class="btn btn-sm btn-outline-warning">Share</button>
                    <button @click="deleteFile(file.id)" class="btn btn-sm btn-outline-danger">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Share Link Modal -->
    <div v-if="showModal" class="modal d-block" style="background-color: rgba(0,0,0,0.5)" @click="showModal = false">
      <div class="modal-dialog modal-dialog-centered" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Share Link</h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted mb-3">Copy this link to share</p>
            <div class="input-group">
              <input type="text" :value="shareLink" readonly class="form-control" />
              <button class="btn btn-primary" @click="copyToClipboard">{{ copied ? 'Copied!' : 'Copy' }}</button>
            </div>
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
    console.log('Component mounted - loading files...');
    this.loadFiles();
  },
  methods: {
    async loadFiles() {
      this.loading = true;
      this.error = null;
      
      const userStr = localStorage.getItem('user');
      console.log('📦 User from localStorage:', userStr);
      
      if (!userStr) {
        console.error('❌ No user found in localStorage');
        this.error = 'ไม่พบข้อมูลผู้ใช้ กรุณาเข้าสู่ระบบใหม่';
        this.loading = false;
        setTimeout(() => {
          this.$router.push('/login');
        }, 2000);
        return;
      }

      let user;
      try {
        user = JSON.parse(userStr);
        console.log('👤 Parsed user:', user);
        console.log('🆔 User ID:', user.id);
      } catch (e) {
        console.error('❌ Error parsing user data:', e);
        this.error = 'ข้อมูลผู้ใช้ไม่ถูกต้อง กรุณาเข้าสู่ระบบใหม่';
        this.loading = false;
        return;
      }

      const url = `http://localhost/project__67706406/api_php/get_files.php?user_id=${user.id}`;
      console.log('🌐 Fetching from:', url);

      try {
        const response = await fetch(url);
        console.log('📡 Response status:', response.status);
        
        if (!response.ok) {
          if (response.status === 404) {
            throw new Error(`ไม่พบไฟล์ API (404) - ตรวจสอบว่าไฟล์ get_files.php อยู่ที่ api_php/ หรือไม่`);
          }
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('📊 API result:', result);
        
        if (result.success) {
          this.files = result.data || [];
          console.log('✅ Files loaded:', this.files.length, 'files');
        } else {
          console.error('❌ API returned success: false', result);
          this.error = result.message || 'ไม่สามารถโหลดไฟล์ได้';
        }
      } catch (error) {
        console.error('❌ Error loading files:', error);
        this.error = `เกิดข้อผิดพลาด: ${error.message}`;
      } finally {
        this.loading = false;
      }
    },

    async downloadFile(fileId) {
      try {
        const response = await fetch(`http://localhost/project__67706406/api_php/download_file.php?file_id=${fileId}`);
        
        if (!response.ok) {
          throw new Error('ไม่สามารถดาวน์โหลดไฟล์ได้');
        }

        // ดึง filename จาก Content-Disposition header
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = 'download';
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
        alert('ไม่สามารถดาวน์โหลดไฟล์ได้: ' + error.message);
      }
    },

    async deleteFile(fileId) {
      if (!confirm('คุณต้องการลบไฟล์นี้ใช่ไหม?')) return;
      
      try {
        const response = await fetch('http://localhost/project__67706406/api_php/delete_file.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ file_id: fileId })
        });
        
        const result = await response.json();
        
        if (result.success) {
          this.files = this.files.filter(f => f.id !== fileId);
          alert('ลบไฟล์สำเร็จ');
        } else {
          alert('ไม่สามารถลบไฟล์ได้: ' + (result.message || 'Unknown error'));
        }
      } catch (error) {
        console.error('Error deleting file:', error);
        alert('เกิดข้อผิดพลาด: ' + error.message);
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
          alert('ไม่สามารถสร้างลิงก์แชร์ได้: ' + (result.message || 'Unknown error'));
        }
      } catch (error) {
        console.error('Error creating share link:', error);
        alert('เกิดข้อผิดพลาด: ' + error.message);
      }
    },

    copyToClipboard() {
      navigator.clipboard.writeText(this.shareLink);
      this.copied = true;
      setTimeout(() => { this.copied = false; }, 2000);
    },

    formatFileSize(bytes) {
      if (!bytes || bytes === 0) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
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
.modal {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>