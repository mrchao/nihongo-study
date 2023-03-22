import {defineStore} from "pinia"
import {UserType} from "../types"
import axios from "axios"
import {useStorage} from "vue3-storage"
const storage = useStorage()

interface UserStore {
    token: string
    nickName:string
    isLogin: boolean
}

const apiLogin = (loginData: UserType) => {
    return axios.post<UserStore>("/api/login", loginData).then(result => {
        const { data, status } = result;
        return status === 200 ? data : undefined
    })
}

export const useUserStore = defineStore('userStore', {
    state: (): UserStore => {
        return {
            token: "",
            nickName: "",
            isLogin: false
        }
    },
    getters: {

    },
    actions: {
        login(loginData: UserType): Promise<boolean> {
            return apiLogin(loginData).then(userData => {
                if(userData && userData.isLogin) {
                    storage.setStorageSync("user", userData, 7200 * 1000);
                    this.nickName = userData.nickName
                    this.isLogin = userData.isLogin
                    this.token = userData.token
                }
                return userData?.isLogin ?? false
            })
        },
        load() {
            const userData = storage.getStorageSync<UserStore>("user");
            if(userData) {
                this.nickName = userData.nickName
                this.isLogin = userData.isLogin
                this.token = userData.token
            }
            return userData?.isLogin ?? false
        }
    }
})