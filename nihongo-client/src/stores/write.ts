import {defineStore} from "pinia"
import axios from "axios"
import {WordType} from "../types";
import {shuffle} from "../utils";

interface WordResultType {
    kanji: string
    kana: string
    desc: string
    status?: number
}

interface WriteStore {
    lesson: number
    showSetting: boolean
    wordList: WordType[]
    sound: HTMLAudioElement
    inputValue: string
    currWord?: WordType
    status: number
    // 0 未开始 1 已开始  2 结束
    resultList: WordResultType[]
    total: number
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

export const useWriteStore = defineStore('writeStore', {

    state: (): WriteStore => {
        return {
            total: 20,
            lesson: 0,
            showSetting: false,
            wordList: [],
            sound: new Audio(),
            inputValue: "",
            currWord: undefined,
            status: 0,
            resultList: []
        }
    },

    getters:{

    },

    actions: {
        get() {
            apiGetCurrWord(this.lesson).then(result => {
                if(result) {
                    // this.wordList = result
                    this.wordList = shuffle(result, this.total)
                    this.status = 0
                    this.resultList = []
                }
            })
        },
        play() {
            if(this.currWord) {
                this.sound.src = this.currWord.audio
                this.sound.play().then()
            }
        },
        next() {

            if(this.status === 1) {
                const resultWord: WordResultType = {
                    kana: this.currWord?.kana ?? '',
                    kanji: this.currWord?.kanji ?? '',
                    desc: this.currWord?.desc ?? ''
                }

                if (this.inputValue === "") {
                    resultWord.status = 0
                    this.resultList.push(resultWord)
                } else {
                    if (this.inputValue !== this.currWord?.kana
                        || this.inputValue !== this.currWord?.kanji) {
                        resultWord.status = 1
                        this.resultList.push(resultWord)
                    }
                }
            }

            if(this.status === 0) {
                this.status = 1
            }

            this.inputValue = ""

            if(this.wordList.length === 0) {
                this.status = 2
                return
            }

            this.currWord = this.wordList.shift()
            this.play()
        }
    }

})