import { createRouter, createWebHashHistory } from 'vue-router'
import HomeView from "./views/HomeView.vue"
import AboutView from "./views/AboutView.vue"
import LoginView from "./views/LoginView.vue"
import BaseView from "./views/BaseView.vue"
import KanjiView from "./views/Kanji/KanjiView.vue"

const router = createRouter({
    history: createWebHashHistory(),
    routes: [{
        path: '/',
        component: BaseView,
        children:[{
            path: '/',
            name: 'home',
            component: HomeView
        },{
            path: '/word',
            name: 'word',
            component: KanjiView
        }]
    },{
        path: '/about',
        name: 'about',
        component: AboutView
    },{
        path: '/login',
        name: 'login',
        component: LoginView
    },{
        path: '/:catchAll(.*)',
        redirect: '/',
    }]
})

export default router