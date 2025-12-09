<template>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการไฟล์</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background-color: rgba(0,0,0,0.3) !important;
        }
        
        .gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .gradient-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container-fluid">
            <span class="navbar-brand fs-4 fw-bold">📁 ระบบจัดการไฟล์</span>
            <div class="navbar-nav ms-auto">
                <button class="nav-link btn px-3 text-white" onclick="showPage('upload')">
                    📤 อัปโหลด
                </button>
                <button class="nav-link btn px-3 text-light" onclick="showPage('list')">
                    📁 ไฟล์ของฉัน
                </button>
                <button class="nav-link btn px-3 text-light" onclick="showPage('shared')">
                    🔗 ดาวน์โหลดจากลิงก์
                </button>
            </div>
        </div>
    </nav>

    <!-- หน้าอัปโหลดไฟล์ -->
    <div id="page-upload" class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header gradient-primary text-white py-3">
                        <h3 class="mb-0">📤 อัปโหลดไฟล์</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">📄 เลือกไฟล์</label>
                            <input type="file" id="fileInput" class="form-control form-control-lg">
                            <div id="fileInfo" class="mt-3"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">ชื่อไฟล์ที่จะบันทึก</label>
                            <input type="text" id="fileName" class="form-control" placeholder="ระบุชื่อไฟล์">
                        </div>

                        <button onclick="uploadFile()" class="btn btn-lg w-100 text-white gradient-primary">
                            📤 อัปโหลดไฟล์
                        </button>

                        <div id="uploadMessage" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- หน้าไฟล์ของฉัน -->
    <div id="page-list" class="container mt-5" style="display:none;">
        <div class="card shadow-lg border-0">
            <div class="card-header gradient-danger text-white py-3">
                <h3 class="mb-0">📁 ไฟล์ของฉัน <span id="fileCount"></span></h3>
            </div>
            <div class="card-body p-0">
                <div id="filesContainer"></div>
            </div>
        </div>
    </div>

    <!-- หน้าดาวน์โหลดจากลิงก์ -->
    <div id="page-shared" class="container mt-5" style="display:none;">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header gradient-info text-white py-3">
                        <h3 class="mb-0">🔗 ดาวน์โหลดไฟล์จากลิงก์แชร์</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">วางลิงก์แชร์ที่นี่</label>
                            <div class="input-group input-group-lg">
                                <input type="text" id="shareInput" class="form-control" placeholder="ใส่ลิงก์แชร์หรือรหัส">
                                <button class="btn btn-primary" onclick="checkShareLink()">ตรวจสอบ</button>
                            </div>
                        </div>
                        <div id="sharedFileInfo"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal แชร์ไฟล์ -->
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🔗 ลิงก์สำหรับแชร์ไฟล์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">คัดลอกลิงก์นี้เพื่อแชร์ให้ผู้อื่น</p>
                    <div class="input-group">
                        <input type="text" id="shareLinkText" class="form-control" readonly>
                        <button class="btn btn-primary" onclick="copyShareLink()">
                            📋 คัดลอก
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        // ตั้งค่า API URL (แก้ให้ตรงกับ Mac)
        const API_BASE_URL = 'http://localhost/project__67706406/api_php';
        const USER_ID = 1; // User ID ของคุณ

        // แสดง/ซ่อนหน้า
        function showPage(page) {
            document.getElementById('page-upload').style.display = 'none';
            document.getElementById('page-list').style.display = 'none';
            document.getElementById('page-shared').style.display = 'none';
            document.getElementById('page-' + page).style.display = 'block';

            if (page === 'list') {
                loadFiles();
            }
        }

        // แสดงข้อมูลไฟล์ที่เลือก
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('fileName').value = file.name;
                document.getElementById('fileInfo').innerHTML = `
                    <div class="p-3 bg-light rounded">
                        <small class="d-block">📦 ชื่อไฟล์: <strong>${file.name}</strong></small>
                        <small class="d-block">📊 ขนาด: <strong>${(file.size / 1024 / 1024).toFixed(2)} MB</strong></small>
                        <small class="d-block">📋 ประเภท: <strong>${file.type || 'ไม่ระบุ'}</strong></small>
                    </div>
                `;
            }
        });

        // อัปโหลดไฟล์
        async function uploadFile() {
            const fileInput = document.getElementById('fileInput');
            const fileName = document.getElementById('fileName').value;
            const file = fileInput.files[0];

            if (!file) {
                showMessage('uploadMessage', 'กรุณาเลือกไฟล์', 'danger');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('filename', fileName);
            formData.append('user_id', USER_ID);

            try {
                const response = await fetch(`${API_BASE_URL}/upload_file.php`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                showMessage('uploadMessage', result.message, result.success ? 'success' : 'danger');

                if (result.success) {
                    fileInput.value = '';
                    document.getElementById('fileName').value = '';
                    document.getElementById('fileInfo').innerHTML = '';
                }
            } catch (error) {
                showMessage('uploadMessage', 'เกิดข้อผิดพลาด: ' + error.message, 'danger');
            }
        }

        // โหลดรายการไฟล์
        async function loadFiles() {
            try {
                const response = await fetch(`${API_BASE_URL}/get_files.php?user_id=${USER_ID}`);
                const result = await response.json();

                if (result.success) {
                    const files = result.data;
                    document.getElementById('fileCount').textContent = `(${files.length} ไฟล์)`;

                    if (files.length === 0) {
                        document.getElementById('filesContainer').innerHTML = `
                            <div class="text-center text-muted py-5">
                                <p class="fs-5">ยังไม่มีไฟล์</p>
                                <small>เริ่มต้นโดยการอัปโหลดไฟล์ของคุณ</small>
                            </div>
                        `;
                    } else {
                        let html = '<div class="table-responsive"><table class="table table-hover mb-0">';
                        html += '<thead class="table-light"><tr>';
                        html += '<th class="px-4">ชื่อไฟล์</th><th>ประเภท</th><th class="text-center">ขนาด</th>';
                        html += '<th class="text-center">วันที่อัปโหลด</th><th class="text-center">จัดการ</th>';
                        html += '</tr></thead><tbody>';

                        files.forEach(file => {
                            html += `<tr>
                                <td class="px-4">📄 ${file.filename}</td>
                                <td><span class="badge bg-secondary">${file.filetype || 'N/A'}</span></td>
                                <td class="text-center"><span class="badge bg-light text-dark">${formatFileSize(file.filesize)}</span></td>
                                <td class="text-center"><small>${formatDate(file.upload_date)}</small></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="${API_BASE_URL}/uploads/${file.filepath}" download class="btn btn-sm btn-outline-info" title="ดาวน์โหลด">⬇️</a>
                                        <button onclick="createShareLink(${file.id})" class="btn btn-sm btn-outline-warning" title="แชร์">🔗</button>
                                        <button onclick="deleteFile(${file.id})" class="btn btn-sm btn-outline-danger" title="ลบ">🗑️</button>
                                    </div>
                                </td>
                            </tr>`;
                        });

                        html += '</tbody></table></div>';
                        document.getElementById('filesContainer').innerHTML = html;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // ลบไฟล์
        async function deleteFile(fileId) {
            if (!confirm('คุณต้องการลบไฟล์นี้ใช่หรือไม่?')) return;

            try {
                const response = await fetch(`${API_BASE_URL}/delete_file.php`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ file_id: fileId })
                });

                const result = await response.json();
                alert(result.message);
                if (result.success) loadFiles();
            } catch (error) {
                alert('เกิดข้อผิดพลาด: ' + error.message);
            }
        }

        // สร้างลิงก์แชร์
        async function createShareLink(fileId) {
            try {
                const response = await fetch(`${API_BASE_URL}/create_share_link.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ file_id: fileId })
                });

                const result = await response.json();
                if (result.success) {
                    document.getElementById('shareLinkText').value = result.share_link;
                    const modal = new bootstrap.Modal(document.getElementById('shareModal'));
                    modal.show();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('เกิดข้อผิดพลาด: ' + error.message);
            }
        }

        // คัดลอกลิงก์
        function copyShareLink() {
            const input = document.getElementById('shareLinkText');
            input.select();
            document.execCommand('copy');
            alert('คัดลอกลิงก์แล้ว! 📋');
        }

        // ตรวจสอบลิงก์แชร์
        async function checkShareLink() {
            const shareLink = document.getElementById('shareInput').value.trim();
            if (!shareLink) {
                showMessage('sharedFileInfo', 'กรุณาใส่ลิงก์แชร์', 'danger');
                return;
            }

            const shareCode = shareLink.includes('/') ? shareLink.split('/').pop() : shareLink;

            try {
                const response = await fetch(`${API_BASE_URL}/get_shared_file.php?share_link=${shareCode}`);
                const result = await response.json();

                if (result.success) {
                    const file = result.data;
                    document.getElementById('sharedFileInfo').innerHTML = `
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">📄 ${file.filename}</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">ประเภทไฟล์:</small>
                                        <p class="mb-0"><strong>${file.filetype || 'ไม่ระบุ'}</strong></p>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">ขนาดไฟล์:</small>
                                        <p class="mb-0"><strong>${formatFileSize(file.filesize)}</strong></p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <small class="text-muted">อัปโหลดเมื่อ:</small>
                                        <p class="mb-0"><strong>${formatDate(file.upload_date)}</strong></p>
                                    </div>
                                </div>
                                <a href="${API_BASE_URL}/uploads/${file.filepath}" download class="btn btn-success btn-lg w-100">
                                    ⬇️ ดาวน์โหลดไฟล์
                                </a>
                            </div>
                        </div>
                    `;
                } else {
                    document.getElementById('sharedFileInfo').innerHTML = `
                        <div class="alert alert-danger">${result.message}</div>
                    `;
                }
            } catch (error) {
                document.getElementById('sharedFileInfo').innerHTML = `
                    <div class="alert alert-danger">เกิดข้อผิดพลาด: ${error.message}</div>
                `;
            }
        }

        // ฟังก์ชันช่วย
        function showMessage(elementId, message, type) {
            document.getElementById(elementId).innerHTML = `
                <div class="alert alert-${type}">${message}</div>
            `;
        }

        function formatFileSize(bytes) {
            if (!bytes) return '0 B';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return (bytes / 1024 / 1024).toFixed(2) + ' MB';
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('th-TH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
</body>
</html></template>

<script>
// @ is an alias to /src
import HelloWorld from '@/components/HelloWorld.vue'

export default {
  name: 'HomeView',
  components: {
    HelloWorld
  }
}

</script>
