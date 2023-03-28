import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import Vue3Storage, {StorageType} from "vue3-storage"

const app = createApp(App);

import naive from 'naive-ui'
app.use(naive)

import router from './router'
app.use(router)

app.use(Vue3Storage, {
    namespace: "ja_",
    storage: StorageType.Local
})

app.mount('#app')
