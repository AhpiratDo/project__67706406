<template>
  <nav class="navbar navbar-expand-lg" style="background-color: #12daaeff;">
  <div class="container">
    <a class="navbar-brand" href="#">Navbar</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <!-- แสดงเฉพาะเมื่อเข้าสู่ระบบแล้ว -->
          <template v-if="isLoggedIn">  

        <li class="nav-item">
          <a class="nav-link" href="/m">Menu</a>
        </li>        
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Dropdown
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/product">Product</a></li>
            <li><a class="dropdown-item" href="/productedit">Productedit</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/employee">Employee</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="show">ShowProduct</a>
        </li>
                <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Listname
          </a>
          
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/coust">Coustomer</a></li>
            <li><a class="dropdown-item" href="/student">Student</a></li>
            <li><a class="dropdown-item" href="/coustomered">Coustomeredit</a></li>
            </ul>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="#" @click="logout">Logout</router-link>
            </li>

        </template>
              <!-- ยังไม่ได้เข้าสู่ระบบ Login!!!!! -->
          <template v-else>  
         <li class="nav-item">
          <a class="nav-link" href="/about">About</a>
        </li>
                <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/">Home</a>
        </li>

            </template>
                <li class="nav-item">
          <a class="nav-link" href="/add_coustomer">Register</a>
        </li>
               <li class="nav-item">
          <a class="nav-link" href="/login_customer">Login_customer</a>
        </li>
         
      </ul>
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>
  <router-view/>
</template>
<script>
export default {
  name: "Navbar",
  data() {
    return {
      isLoggedIn: false,
    };
  },
  mounted() {
    // ตรวจสอบสถานะเมื่อโหลดหน้า
    this.checkLogin();
  },
  methods: {
    checkLogin() {
      this.isLoggedIn = localStorage.getItem("customer_login") === "true";
    },
    logout() {
      if (confirm("ต้องการออกจากระบบหรือไม่?")) {
        // เคลียร์ข้อมูลทั้งหมดที่เกี่ยวข้องกับการล็อกอิน
        localStorage.removeItem("customer_login");
        localStorage.removeItem("username");
        localStorage.removeItem("token");
        this.isLoggedIn = false;

        // กลับไปหน้าเมนูหลัก
        this.$router.push("/");
      }
    },
  },
  watch: {
    // เมื่อเปลี่ยนเส้นทาง ให้ตรวจสอบสถานะการล็อกอินใหม่
    $route() {
      this.checkLogin();
    },
  },
};
</script>


<style scoped>
.navbar {
  background-color: #86bfe7ff !important;
}
.nav-link {
  color: white !important;
  font-weight: 500;
}
.nav-link:hover {
  text-decoration: underline;
}
</style>


