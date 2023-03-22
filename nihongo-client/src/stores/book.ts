import {defineStore} from "pinia"
import { DictType, OptionType} from "../types"
import {useStorage} from "vue3-storage"
import {useDictStore} from "./dict";
import {padStart} from "lodash";

const storage = useStorage()

interface BookStore {
    unitValues: number[]
    lessonValues: number[]
    kanaList: DictType[]
}

export const useBookStore = defineStore('bookStore', {
    state: (): BookStore => {
        return {
            unitValues: [],
            lessonValues: [],
            kanaList: []
        }
    },
    getters: {
        getUnitOptions: () =>  {
            const dictStore = useDictStore()
            return Object.entries(dictStore.kanaListLessonGroupUnitGroup).map(item => {
                const [name] = item
                return {
                    label: `单元-${padStart(name,2,'0')}`,
                    value: Number(name)
                }
            })
        },
        getLessonOptions: (state) => {
            const dictStore = useDictStore()
            return Object.entries(dictStore.kanaListLessonGroupUnitGroup).map(item => {
                if (state.unitValues.includes(Number(item[0]))) {
                    return Object.entries(item[1]).map(lesson => {
                        const [name] = lesson
                        return {
                            label: `第${padStart(name, 2, '0')}课`,
                            value: Number(name)
                        }
                    })
                }
            }).flat(1).filter(value => value) as OptionType[]
        },
        getKanaList: (state) => {
            const dictStore = useDictStore()
            return dictStore.getDictList.filter(kana => {
                return state.unitValues.includes(kana.unit_num)
                    && state.lessonValues.includes(kana.lesson_num)
            })
        },
    },
    actions: {
        load() {
            const book = storage.getStorageSync("book")
            if(book) {
                this.unitValues = book.unitValues
                this.lessonValues = book.lessonValues
                this.kanaList = book.kanaList
            }
        },
        setCache() {
          this.kanaList = this.getKanaList
          storage.setStorageSync("book", {
              unitValues: this.unitValues,
              lessonValues: this.lessonValues,
              kanaList: this.getKanaList
          })
        }
    }
})