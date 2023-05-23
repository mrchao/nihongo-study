import {defineStore} from "pinia"
import axios from "axios"
import { WordType } from "../types"
import { shuffle, random } from "../utils"

interface LeftWordType {
    id: number
    label: string
    audio: string
}

interface RightWordType {
    id: number
    label: string
}

interface StudyStore {
    lesson: number
    //currWord?: WordType
    wordList: WordType[]
    audioPlaying: boolean
    leftList: LeftWordType[]
    rightList: RightWordType[]
    currId: number
    rightIds: number[]
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

export const useMatchStore = defineStore('matchStore', {
    state: (): StudyStore => {
        return {
            lesson: 0,
            //currWord: undefined,
            wordList: [],
            audioPlaying: false,
            leftList: [],
            rightList: [],
            currId: 0,
            rightIds: []
        }
    },

    getters:{

    },

    actions: {
        get() {
            apiGetCurrWord(this.lesson).then(result => {
                if(result) {
                    this.wordList = result
                    this.random()
                }
            })
        },

        right(id: number) {
            if(!this.rightIds.includes(id)) {
                this.rightIds.push(id)
            }
            this.currId = 0
            if(this.rightIds.length >= 5) {
                setTimeout(() => {
                    this.leftList = []
                    this.rightIds = []
                    this.random()
                    this.rightIds = []
                }, 1500)
            }
        },

        random() {
            const list = shuffle(this.wordList, 5);
            list.forEach((item,index) => {
                console.log(item)
                this.leftList[index] = {
                    id: item.id,
                    label: "",
                    audio: item.audio
                }
                this.rightList[index] = {
                    id: item.id,
                    label: ""
                }
                if(item.kanji === '' || item.kanji === item.desc) {
                    this.leftList[index].label = item.kana
                    this.rightList[index].label = item.desc
                }else{
                    const leftLabel = random(['kana', 'kanji']);
                    if(leftLabel === 'kana') {
                        this.leftList[index].label = item.kana
                        const rightName = random(['kanji', 'desc'])
                        this.rightList[index].label = rightName === 'kanji' ? item.kanji :  item.desc
                    }
                    if(leftLabel === 'kanji') {
                        this.leftList[index].label = item.kanji
                        const rightName = random(['kana', 'desc'])
                        this.rightList[index].label = rightName === 'kana' ? item.kana :  item.desc
                    }
                }
            })
            this.leftList = shuffle(this.leftList, 5)
            this.rightList = shuffle(this.rightList, 5)
        },

        play(audio: string) {
            if(audio) {
                this.audioPlaying = true
                const sound = new Audio(audio)
                sound.play().then()
                sound.addEventListener("ended", () => {
                    this.audioPlaying = false
                })
            }
        }
    }
})