import { createApp } from 'vue'
import 'bootswatch/dist/darkly/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import App from './App.vue'
import router from '@/app/router'

createApp(App).use(router).mount('#app')
