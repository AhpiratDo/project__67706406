<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow-lg border-0">
          <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)">
            <h3 class="mb-0">Upload File</h3>
          </div>
          <div class="card-body p-4">
            <!-- เลือกไฟล์ -->
            <div class="mb-4">
              <label class="form-label fw-bold">Select File</label>
              <input
                type="file"
                @change="handleFileChange"
                ref="fileInput"
                class="form-control form-control-lg"
              />
              
              <!-- แสดงข้อมูลไฟล์ -->
              <div v-if="selectedFile" class="mt-3 p-3 bg-light rounded">
                <small class="d-block">Filename: <strong>{{ selectedFile.name }}</strong></small>
                <small class="d-block">Size: <strong>{{ formatFileSize(selectedFile.size) }}</strong></small>
                <small class="d-block">Type: <strong>{{ selectedFile.type || 'Unknown' }}</strong></small>
              </div>
            </div>

            <!-- ชื่อไฟล์ -->
            <div class="mb-4">
              <label class="form-label fw-bold">Filename to save</label>
              <input
                v-model="filename"
                type="text"
                class="form-control"
                placeholder="Enter filename"
              />
            </div>

            <!-- ปุ่มอัปโหลด -->
            <button
              @click="uploadFile"
              class="btn btn-lg w-100 text-white"
              style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)"
              :disabled="uploading || !selectedFile"
            >
              <span v-if="uploading" class="spinner-border spinner-border-sm me-2"></span>
              {{ uploading ? 'Uploading...' : 'Upload File' }}
            </button>

            <!-- แสดงผลลัพธ์ -->
            <div v-if="message" :class="`alert alert-${messageType} mt-4 mb-0`">
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
  name: 'UploadFile',
  data() {
    return {
      selectedFile: null,
      filename: '',
      message: '',
      messageType: '',
      uploading: false
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

    async uploadFile() {
      if (!this.selectedFile) {
        this.message = 'Please select a file';
        this.messageType = 'danger';
        return;
      }

      const userStr = localStorage.getItem('user');
      if (!userStr) {
        this.message = 'Please login first';
        this.messageType = 'danger';
        setTimeout(() => {
          this.$router.push('/login');
        }, 1500);
        return;
      }

      const user = JSON.parse(userStr);

      this.uploading = true;
      this.message = '';

      const formData = new FormData();
      formData.append('file', this.selectedFile);
      formData.append('filename', this.filename);
      formData.append('user_id', user.id);

      try {
        const response = await fetch('http://localhost/project__67706406/api_php/upload_file.php', {
          method: 'POST',
          body: formData
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('Server did not return JSON response');
        }

        const result = await response.json();
        
        this.message = result.message || 'Unknown error occurred';
        this.messageType = result.success ? 'success' : 'danger';

        if (result.success) {
          setTimeout(() => {
            this.selectedFile = null;
            this.filename = '';
            if (this.$refs.fileInput) {
              this.$refs.fileInput.value = '';
            }
            this.message = '';
          }, 2000);
        }
      } catch (error) {
        console.error('Upload error:', error);
        this.message = 'Error: Cannot connect to server. Please check if server is running.';
        this.messageType = 'danger';
      } finally {
        this.uploading = false;
      }
    },

    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
      return (bytes / 1024 / 1024).toFixed(2) + ' MB';
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