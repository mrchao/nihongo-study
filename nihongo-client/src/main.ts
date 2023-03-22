import {createApp} from 'vue'
import '@varlet/ui/es/style'
import './assets/style.css'
import App from './App.vue'
import Varlet from '@varlet/ui'
import router from './router'
import pinia from "./stores"
import Vue3Storage, {StorageType} from "vue3-storage";
import 'animate.css';
import 'animate.css/animate.compat.css'

const app = createApp(App);

app.use(Varlet)
app.use(router)
app.use(pinia)

app.use(Vue3Storage, {
    namespace: "ja_",
    storage: StorageType.Local
})

app.mount('#app')
