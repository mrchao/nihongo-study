import {defineStore} from "pinia"
import axios from "axios"
import { WordType } from "../types"

interface StudyStore {
    lesson: number
    currWord?: WordType
    wordList: WordType[]
    audioPlaying: boolean
}

const apiGetCurrWord = (lesson: number) => {
    return axios.get<WordType[]>("/api/study/fetchByLesson", {
        params: {
            lesson: lesson
        }
    }).then( result => {
        const {data, status} = result
        return status === 200 ? data : undefined;
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
            apiGetCurrWord(this.lesson).then(result => {
                if(result) {
                    this.wordList = result
                    console.log(this.wordList)
                    this.next()
                }
            })
        },
        next() {
            this.currWord = this.wordList.shift()
            //console.log(this.currWord)
            //console.log(this.wordList)
            this.play()
        },
        ignore() {
            if(this.currWord) {
                // 把单词忽略掉，不学习
            }
        },
        forget() {
            if(this.currWord) {
                // 把单词放入队列尾部，稍后再次学习
                this.wordList.push(this.currWord)
            }
            this.next()
        },
        blurry() {
            if(this.currWord) {
                // 30分钟后需要再次复习的单词
            }
            this.next()
        },
        remember() {
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