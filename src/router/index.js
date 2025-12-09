import { createRouter, createWebHistory } from 'vue-router'
import Register from '../views/Register.vue'
import Login from '../views/Login.vue'
import UploadFile from '../views/UploadFile.vue'
import FileList from '../views/FileList.vue'
import SharedDownload from '../views/SharedDownload.vue'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/upload',
    name: 'Upload',
    component: UploadFile,
    meta: { requiresAuth: true }
  },
  {
    path: '/files',
    name: 'Files',
    component: FileList,
    meta: { requiresAuth: true }
  },
  {
    path: '/shared',
    name: 'Shared',
    component: SharedDownload,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const user = localStorage.getItem('user')
  
  if (to.meta.requiresAuth && !user) {
    next('/login')
  } else if ((to.path === '/login' || to.path === '/register') && user) {
    next('/files')
  } else {
    next()
  }
})

export default router