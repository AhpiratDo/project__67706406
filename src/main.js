import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// เพิ่มบรรทัดนี้
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

createApp(App).use(router).mount('#app')