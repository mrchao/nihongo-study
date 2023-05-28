import {defineStore} from "pinia"
import axios from "axios"
import { WordType } from "../types"
import {useUserStore} from "./user";


interface StudyStore {
    lesson: number
    currWord?: WordType
    wordList: WordType[]
    audioPlaying: boolean
}

enum WordStatus {
    ignore,
    forget,
    blurry,
    remember
}

const apiGetCurrWord = (lesson: number) => {
    const user = useUserStore()
    return axios.get<WordType[]>("/api/study/fetchByLesson", {
        params: {
            lesson: lesson,
            token: user.token
        }
    }).then( result => {
        const {data, status} = result
        return status === 200 ? data : undefined;
    })
}

const apiPostCurrWord = (status: WordStatus, wordId: number) => {
    const user = useUserStore()
    return axios.post("/api/study/status", {
        wordId: wordId,
        status: status,
        token: user.token
    }).then(result => {
        return result
    })
}

export const useStudyStore = defineStore('studyStore', {
    state: (): StudyStore => {
        return {
            lesson: 0,
            currWord: undefined,
            wordList: [],
            audioPlaying: false
        }
    },

    getters:{

    },

    actions: {
        get() {
            return apiGetCurrWord(this.lesson).then(result => {
                if(result) {
                    const wortListLength = result.length === 0
                    this.wordList = result
                    this.next()
                    return wortListLength
                }
                return false
            })
        },
        next() {
            this.currWord = this.wordList.shift()
            this.play()
        },
        ignore() {
            if(this.currWord) {
                // 把单词忽略掉，不学习
                apiPostCurrWord(WordStatus.ignore, this.currWord.id).then()
            }
            this.next()
        },
        forget() {
            if(this.currWord) {
                // 把单词放入队列尾部，稍后再次学习
                this.wordList.push(this.currWord)
                apiPostCurrWord(WordStatus.forget, this.currWord.id).then()
            }
            this.next()
        },
        blurry() {
            if(this.currWord) {
                // 30分钟后需要再次复习的单词
                apiPostCurrWord(WordStatus.blurry, this.currWord.id).then()
            }
            this.next()
        },
        remember() {
            if(this.currWord) {
                apiPostCurrWord(WordStatus.remember, this.currWord.id).then()
            }
            this.next()
        },
        play() {
            if(this.currWord) {
                this.audioPlaying = true
                const sound = new Audio(this.currWord.audio)
                sound.play().then()
                sound.addEventListener("ended", () => {
                    this.audioPlaying = false
                })
            }
        }
    }
})