import {defineStore} from "pinia"
import {CategoryList, DictType} from "../types"
import axios from "axios"
import {useStorage} from "vue3-storage"
const storage = useStorage()

interface DictStore {
    kanaListLessonGroupUnitGroup: CategoryList
    kanaList: DictType[]
}

const apiGetCategoryList = (): Promise<CategoryList | false> => {
    return axios.get<CategoryList>("/api/categoryList").then(result => {
        const {data, status} = result
        return status === 200 ? data : false
    })
}

const apiIgnoreById = (id:number): Promise<boolean> => {
    return axios.put(`/api/dict/${id}/ignore`).then(result => {
        const {data, status} = result
        return status === 200
    })
}

const toFlat = (obj: Object):DictType[] => {
    return Object.entries(obj).map(unitGroup => {
        return Object.entries(unitGroup[1] as Array<DictType[]>).map<DictType[]>(lessonGroup => {
            return lessonGroup[1]
        })
    }).flat(2)
}


export const useDictStore = defineStore('dictStore', {
    state: (): DictStore => {
        return {
            kanaListLessonGroupUnitGroup: {},
            kanaList: []
        }
    },
    getters: {
        getDictList: (state) => {
            return toFlat(state.kanaListLessonGroupUnitGroup)
        },
        getDictById: (state) => {
            return (id: number) => toFlat(state.kanaListLessonGroupUnitGroup).find(item => item.id === id)
        }
    },
    actions: {
        async load() {
            let list = storage.getStorageSync("dict")
            if(!list) {
                list = await apiGetCategoryList()
                storage.setStorageSync("dict", list, 7200 * 1000)
            }
            this.kanaListLessonGroupUnitGroup = list;
        },
        ignore(dictId: number) {
            apiIgnoreById(dictId).then(result => {
                if(result) {
                    this.load().then()
                }
            })
        }
    }
})