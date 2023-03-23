import {AnswerResultExtType, AnswerResultType} from "../types";
import axios from "axios";
import {useUserStore} from "./user";
import {defineStore} from "pinia";
import {useStorage} from "vue3-storage";
import stores from "./index";

const storage = useStorage()

interface HistoryType {
    label: string
    value: AnswerResultExtType[]
}

interface AnswerHistoryStore {
    historyList: HistoryType[]
}

const apiAnsweredHistoryList = () => {
    const userStore = useUserStore()
    if(!userStore.isLogin) {
        userStore.load()
    }
    return axios.get<HistoryType[]>("/api/historyList", {
        headers: {
            token: userStore.token
        }
    })
}

export const useAnswerHistoryStore = defineStore('answerHistoryStore', {
    state: (): AnswerHistoryStore => {
        return {
            historyList: []
        }
    },
    actions: {
        async load() {
            let historyList = storage.getStorageSync<HistoryType[]>("history")
            if(!historyList) {
                historyList =  await this.getAnsweredHistoryList()
                storage.setStorageSync("history", historyList, 7200 * 1000)
            }
            this.historyList = historyList
        },
        getAnsweredHistoryList() {
            return apiAnsweredHistoryList().then(result => {
                const {status, data} = result
                if(status === 200) {
                    return data
                }
                return []
            })
        },
        reset() {
            storage.removeStorageSync("history");
            this.load().then()
        }
    }
})