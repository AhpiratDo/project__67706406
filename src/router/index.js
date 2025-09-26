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
  }




]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
