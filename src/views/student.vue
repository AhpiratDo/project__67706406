<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายชื่อนักศึกษา</h2>
    
    <div class="mb-3">
      <a class="btn btn-primary" href="/add_student" role="button">Add+</a>
    </div>

    <!-- ตารางแสดงข้อมูลนักศึกษา -->
    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>รหัสนักศึกษา</th>
          <th>ชื่อ</th>
          <th>นามสกุล</th>
          <th>อีเมลนักศึกษา</th>
          <th>เบอร์โทรศัพท์</th>
          <th>แก้ไข</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students" :key="student.student_id">
          <td>{{ student.student_id }}</td>
          <td>{{ student.first_name || '-' }}</td>
          <td>{{ student.last_name || '-' }}</td>
          <td>{{ student.email || '-' }}</td>
          <td>{{ student.phone || '-' }}</td>
          <td>
            <!-- ปุ่มแก้ไข -->
            <button class="btn btn-warning btn-sm" @click="openEditModal(student)">
              <i class="fa-solid fa-pen-to-square"></i> แก้ไข
            </button> |      
            <!-- ปุ่มลบ -->
            <button class="btn btn-danger btn-sm" @click="deleteStudent(student.student_id)">
              <i class="fa-solid fa-trash"></i> ลบ
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Loading -->
    <div v-if="loading" class="text-center">
      <p>กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <!-- Empty State -->
    <div v-if="!loading && students.length === 0" class="text-center">
      <p>ไม่มีข้อมูลนักศึกษา</p>
    </div>    

    <!-- Modal แก้ไขข้อมูล -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">แก้ไขข้อมูลนักศึกษา</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updateStudent">
              <div class="mb-3">
                <label class="form-label">ชื่อ</label>
                <input v-model="editStudent.first_name" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">นามสกุล</label>
                <input v-model="editStudent.last_name" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">อีเมล</label>
                <input v-model="editStudent.email" type="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">เบอร์โทร</label>
                <input v-model="editStudent.phone" type="text" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-success">บันทึก</button>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { Modal } from "bootstrap";

export default {
  name: "StudentList",
  setup() {
    const students = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const editStudent = ref({});
    let editModal;

    // ฟังก์ชันดึงข้อมูลจาก API ด้วย GET
    const fetchStudents = async () => {
      try {
        const response = await fetch("http://localhost:8181/project__67706406/api_php/show_student.php", {
          method: "GET",
          headers: {
            "Content-Type": "application/json"
          }
        });

        if (!response.ok) {
          throw new Error("ไม่สามารถดึงข้อมูลได้");
        }

        const result = await response.json();
        if (result.success) {
          students.value = result.data;
        } else {
          error.value = result.message;
        }

      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    // ฟังก์ชันเปิด Modal แก้ไข
    const openEditModal = (student) => {
      editStudent.value = { ...student }; // คัดลอกข้อมูลนักศึกษาที่จะแก้ไข
      editModal.show();
    };

    // ฟังก์ชันอัปเดตข้อมูลนักศึกษา
    const updateStudent = async () => {
      try {
        const response = await fetch("http://localhost:8181/project__67706406/api_php/api_student.php", {
          method: "PUT",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(editStudent.value)
        });

        const result = await response.json();

        if (result.success) {
          // อัปเดตข้อมูลในตาราง
          const index = students.value.findIndex(s => s.student_id === editStudent.value.student_id);
          if (index !== -1) {
            students.value[index] = { ...editStudent.value };
          }
          editModal.hide();
          alert(result.message);
        } else {
          alert(result.message);
        }

      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    // ฟังก์ชันการลบข้อมูล
    const deleteStudent = async (id) => {
      if (!confirm("คุณต้องการลบข้อมูลนี้ใช่หรือไม่?")) return;

      try {
        const response = await fetch("http://localhost:8181/project__67706406/api_php/api_student.php", {
          method: "DELETE",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({ student_id: id })
        });

        const result = await response.json();

        if (result.success) {
          // ลบออกจาก students ทันที (ไม่ต้องโหลดใหม่)
          students.value = students.value.filter(s => s.student_id !== id);
          alert(result.message);
        } else {
          alert(result.message);
        }

      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    onMounted(() => {
      fetchStudents();
      const modalEl = document.getElementById("editModal");
      editModal = new Modal(modalEl);
    });

    return {
      students,
      loading,
      error,
      editStudent,
      openEditModal,
      updateStudent,
      deleteStudent
    };
  }
};
</script>