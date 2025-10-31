import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import Showproduct from '@/views/showproduct.vue'
import Menu from "../components/menu.vue";


const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/m',
    name: 'menu',
    component: Menu
  },
    {
    path: '/show',
    name: 'show',
    component: () => import(/* webpackChunkName: "about" */ '../views/showproduct.vue')
  },
   {
    path: '/coust',
    name: 'coust',
    component: () => import(/* webpackChunkName: "about" */ '../views/Coustomer.vue')
  },
  {
    path: '/about',
    name: 'about',
    component: () => import(/* webpackChunkName: "about" */ '../views/AboutView.vue')
  },
  {
    path: '/add_coustomer',
    name: 'about',
    component: () => import(/* webpackChunkName: "about" */ '../views/Add_Coustomer.vue')
  },
  {
    path: '/product',
    name: 'product',
    component: () => import(/* webpackChunkName: "about" */ '../views/product.vue')
  },
  {
    path: '/add_product',
    name: 'add_product',
    component: () => import(/* webpackChunkName: "about" */ '../views/Add_product.vue')
  },
  {
    path: '/student',
    name: 'student',
    component: () => import(/* webpackChunkName: "about" */ '../views/student.vue')
  },
  {
    path: '/add_student',
    name: 'add_student',
    component: () => import(/* webpackChunkName: "about" */ '../views/add_student.vue')
  },
  {
    path: '/coustomered',
    name: 'coustomered',
    component: () => import(/* webpackChunkName: "about" */ '../views/Coustomeredit')
  },
  {
    path: '/productedit',
    name: 'productedit',
    component: () => import(/* webpackChunkName: "about" */ '../views/productedit')
  },
  {
    path: '/employee',
    name: 'employee',
    component: () => import(/* webpackChunkName: "about" */ '../views/employee')
  },
  {
    path: '/login_customer',
    name: 'login_customer',
    component: () => import(/* webpackChunkName: "about" */ '../views/login_customer.vue')
  }

]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})
// 🧠 Navigation Guard — ตรวจสอบการเข้าสู่ระบบ
router.beforeEach((to, from, next) => {
  const isLoggedIn = localStorage.getItem("customer_login") === "true";

  // ถ้าหน้านั้นต้องล็อกอินก่อน แต่ยังไม่ได้ล็อกอิน
  if (to.meta.requiresAuth && !isLoggedIn) {
    alert("⚠ กรุณาเข้าสู่ระบบก่อนใช้งานหน้านี้");
    next("/login_customer");
  }
  // ถ้าเข้าสู่ระบบแล้วแต่พยายามกลับไปหน้า login อีก → ส่งกลับหน้าแรก
  else if (to.path === "/login_customer" && isLoggedIn) {
    next("/");
  } 
  // อื่น ๆ ไปต่อได้ตามปกติ
  else {
    next();
  }
});

export default router
