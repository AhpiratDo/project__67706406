<template>
  <div class="container mt-4 col-md-4 bg-body-secondary">
    <h2 class="text-center mb-3">ลงทะเบียน</h2>
    <form @submit.prevent="addstudent">
      <div class="mb-2">
        <input v-model="student.first_name" class="form-control" placeholder="ชื่อ" required />
      </div>
      <div class="mb-2">
        <input v-model="student.last_name" class="form-control" placeholder="นามสกุล" required />
      </div>
      <div class="mb-2">
        <input v-model="student.email" type="email" class="form-control" placeholder="อีเมล" required />
      </div>
      <div class="mb-2">
        <input v-model="student.phone" type="tel" class="form-control" placeholder="เบอร์โทรศัพท์" required />
      </div>
      <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary mb-4" :disabled="loading">
          {{ loading ? 'กำลังบันทึก...' : 'บันทึก' }}
        </button> &nbsp;
        <button type="button" class="btn btn-secondary mb-4" @click="resetForm">ยกเลิก</button>
      </div>
    </form>

    <div v-if="message" class="alert" :class="messageClass">
      {{ message }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      student: {
        first_name: "",
        last_name: "",
        email: "",
        phone: "",
      },
      message: "",
      loading: false,
      isSuccess: false
    };
  },
  computed: {
    messageClass() {
      return this.isSuccess ? 'alert-success' : 'alert-danger';
    }
  },
  methods: {
    async addstudent() {
      try {
        this.loading = true;
        this.message = "";
        
        // ตรวจสอบข้อมูลก่อนส่ง
        if (!this.student.first_name || !this.student.last_name || 
            !this.student.email || !this.student.phone) {
          this.message = "กรุณากรอกข้อมูลให้ครบถ้วน";
          this.isSuccess = false;
          return;
        }

        const res = await fetch("http://localhost:8181/project__67706406/api_php/add_student.php", {
          method: "POST",
          headers: { 
            "Content-Type": "application/json" 
          },
          body: JSON.stringify(this.student)
        });

        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }

        const data = await res.json();
        this.message = data.message || "ดำเนินการเสร็จสิ้น";
        this.isSuccess = data.success || false;

        if (data.success) {
          // เคลียร์ข้อมูลใน textbox หลังบันทึกสำเร็จ
          this.resetForm();
          
          // ส่งสัญญาณไปยัง parent component (ถ้ามี)
          this.$emit('studentAdded', data);
        }

      } catch (err) {
        console.error("Add student error:", err);
        this.message = "เกิดข้อผิดพลาด: " + err.message;
        this.isSuccess = false;
      } finally {
        this.loading = false;
      }
    },

    resetForm() {
      this.student = { 
        first_name: "", 
        last_name: "", 
        email: "", 
        phone: "" 
      };
      this.message = "";
      this.isSuccess = false;
    }
  }
}
</script>