<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายชื่อนักศึกษา</h2>
    
    <div class="mb-3">
      <a class="btn btn-primary" href="/add_student" role="button">Add+</a>
    </div>

    <!-- ตารางแสดงข้อมูลลูกค้า -->
    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>ชื่อ</th>
          <th>นามสกุล</th>
          <th>อีเมลนักศึกษา</th>
          <th>เบอร์โทรศัพท์</th>
          <th>ลบ</th>
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
            <button class="btn btn-danger btn-sm" @click="deleteStudent(student.student_id)" :disabled="loading">
              ลบ
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
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "StudentList",
  setup() {
    const students = ref([]);
    const loading = ref(false);
    const error = ref(null);

    // ฟังก์ชันดึงข้อมูลจาก API
    const fetchStudents = async () => {
      try {
        loading.value = true;
        error.value = null;
        
        const response = await fetch("http://localhost:8181/project__67706406/api_php/show_student.php", {
          method: "GET",
          headers: {
            "Content-Type": "application/json"
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log("API Response:", result); // Debug log
        
        if (result.success) {
          students.value = Array.isArray(result.data) ? result.data : [];
        } else {
          error.value = result.message || "ไม่สามารถดึงข้อมูลได้";
        }
      } catch (err) {
        console.error("Fetch error:", err);
        error.value = `เกิดข้อผิดพลาด: ${err.message}`;
      } finally {
        loading.value = false;
      }
    };

    // ฟังก์ชันลบข้อมูล
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
        console.log("Delete Response:", result); // Debug log

        if (result.success) {
          // ลบออกจาก array
          students.value = students.value.filter(student => student.student_id !== id);
          alert("ลบข้อมูลสำเร็จ");
        } else {
          alert(result.message || "ไม่สามารถลบข้อมูลได้");
        }
      } catch (err) {
        console.error("Delete error:", err);
        alert(`เกิดข้อผิดพลาด: ${err.message}`);
      }
    };

    // เรียกดึงข้อมูลเมื่อ component mount
    onMounted(() => {
      fetchStudents();
    });

    return {
      students,
      loading,
      error,
      deleteStudent,
      fetchStudents // เพิ่มเพื่อให้เรียกใช้จาก parent component ได้
    };
  }
};
</script>