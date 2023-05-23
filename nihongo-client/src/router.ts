import { createRouter, createWebHashHistory } from 'vue-router'
import BaseView from "./views/BaseView.vue"
import DemoView from "./views/DemoView.vue"
import WordView from "./views/ExamineKanaView.vue"
import SettingView from "./views/SettingView.vue"
import TopicView from "./views/TopicView.vue"
import LoginView from "./views/LoginView.vue"
import WordHistoryView from "./views/HistoryView.vue"
import StudyView from "./views/modules/StudyView.vue"
import MatchView from "./views/modules/MatchView.vue"

const router = createRouter({
    history: createWebHashHistory(),
    routes: [{
        path: '/login',
        component: LoginView,
    },{
        path: '/',
        component: BaseView,
        children:[{
            path: '/',
            name: 'home',
            component: DemoView
        },{
            path: '/setting',
            name: 'setting',
            component: SettingView
        },{
            path: '/word',
            name: 'word',
            component: WordView
        },{
            path: "/word/history",
            name: 'wordHistory',
            component: WordHistoryView
        },{
            path: '/topic',
            name: 'topic',
            component: TopicView
        },{
            path: '/study',
            name: 'study',
            component: StudyView
        },{
            path: '/match',
            name: 'match',
            component: MatchView
        }]
    },{
        path: '/about',
        name: 'about',
        component: DemoView
    },{
        path: '/login',
        name: 'login',
        component: DemoView
    },{
        path: '/:catchAll(.*)',
        redirect: '/',
    }]
})

export default router