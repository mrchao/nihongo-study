import {defineStore} from "pinia"
import {DictType, AnswerResultType } from "../types"
import axios from "axios"
import {useStorage} from "vue3-storage"
import { shuffle, clone} from "lodash";
import {useBookStore} from "./book";
import dayjs from "dayjs";
import {useUserStore} from "./user";
import BigNumber from "bignumber.js"

const storage = useStorage()

const SENSE_ARR = ['SSS', 'SS', 'S', 'A+', 'A', 'A-', 'B+', 'B', 'B-', 'C', 'D', 'E', 'F'];
// sss: all right each 10s
// ss: all right and in10s = 70% out15s = 0
// s: all right and in10s = 30% out15s = 0
// A+: all right and in10s = 10% out15s = 0
// A: all right and out15s = 0
// A-: all right
// B+: err 10%+ and right in5s = 100%
// B: err 10%+
// B-: err 10%+ and right out15s = 20%
// C: err 10%+
// D: err 20%+
// E: err 30%+
// F: err 40%+
const countSense = (answerResult: AnswerResultType) => {
    if(answerResult.amount == answerResult.rightNum) {
        if(answerResult.secIn10sNum === answerResult.amount) {
            return SENSE_ARR.indexOf('SSS')
        }
        if(answerResult.secOut15sNum === 0) {
            const secIn10sBigNum = new BigNumber(answerResult.secIn10sNum)
            const percent = secIn10sBigNum.div(answerResult.amount).decimalPlaces(2).times(100).toNumber()
            if(percent >= 80) return SENSE_ARR.indexOf('SS')
            if(percent >= 50) return SENSE_ARR.indexOf('S')
            if(percent >= 30) return SENSE_ARR.indexOf('A+')
            return SENSE_ARR.indexOf('A')
        }
        return SENSE_ARR.indexOf('A-')
    }else{
        if(answerResult.scoreNum < 60) return SENSE_ARR.indexOf("F")
        if(answerResult.scoreNum < 70) return SENSE_ARR.indexOf("E")
        if(answerResult.scoreNum < 80) return SENSE_ARR.indexOf("D")
        if(answerResult.scoreNum < 90) return SENSE_ARR.indexOf("C")
        return SENSE_ARR.indexOf("B")
    }
}

const apiSubmitAnswered = (answerResult:AnswerResultType, answeredList?:Array<object>) => {
    return axios.post<boolean>("/api/submitAnswered", {
        answeredList: answeredList,
        answerResult: answerResult
    }, {
        headers: {
            token: useUserStore().token
        }
    })
}

interface AnswerStore {
    countTimeMs: number
    currAnswer: {
        dictId: number
        value: string
        useTimeMs: number
        result: boolean
    },
    currDict?: DictType
    answerSortList: number[]
    answeredList: Array<object>
    answerResult: AnswerResultType,
}

export const useAnswerStore = defineStore('answerStore', {
    state: (): AnswerStore => {
        return {
            countTimeMs: 0,
            currAnswer: {
                dictId: 0,
                value: "",
                useTimeMs: 0,
                result: false
            },
            currDict: undefined,
            answerSortList: [],
            answeredList: [],
            answerResult: {
                senseStr: "",
                senseNum: -1,
                scoreNum: 0,
                amount: 0,
                spendTime: 0,
                rightNum: 0,
                errorNum: 0,
                secIn10sNum: 0,
                secOut15sNum: 0
            }
        }
    },
    actions: {
      load () {
          const bookStore = useBookStore()
          this.answerSortList = shuffle(new Array(bookStore.kanaList.length)
              .fill(1).map((v,i) => i)
          );
          this.countTimeMs = this.answerSortList.length * 30 * 1000
      },
      next () {
          const bookStore = useBookStore()
          const dict = bookStore.kanaList.find(kana => kana.id === this.currAnswer.dictId)
          if(dict) {
              this.currAnswer.useTimeMs = dayjs().unix() - this.currAnswer.useTimeMs
              this.currAnswer.result = [dict.kana, dict.kanji].includes(this.currAnswer.value)

              this.currAnswer.result = dict.isRightList.includes(this.currAnswer.value)

              this.answeredList.push(clone(this.currAnswer))
              // 统计
              this.answerResult.spendTime += this.currAnswer.useTimeMs
              if (this.currAnswer.result) {
                  this.answerResult.rightNum++
              } else {
                  this.answerResult.errorNum++
              }
              if (this.currAnswer.useTimeMs <= 10) {
                  this.answerResult.secIn10sNum++
              }
              if (this.currAnswer.useTimeMs > 15) {
                  this.answerResult.secOut15sNum++
              }
          }
          return this.resetCurrAnswer()
      },
      resetCurrAnswer (): boolean {
          const bookStore = useBookStore()
          const nextDictId = this.answerSortList.shift()
          if(nextDictId !== undefined) {
              this.answerResult.amount++
              this.currDict = bookStore.kanaList[nextDictId]
              this.currAnswer = {
                  dictId: this.currDict.id,
                  value: "",
                  useTimeMs: dayjs().unix(),
                  result: false
              }
          }
          return nextDictId === undefined
      },
      submit () {
          // 计算分数评价
          const rightNumBigNum = new BigNumber(this.answerResult.rightNum);
          this.answerResult.scoreNum = rightNumBigNum.div(this.answerResult.amount).decimalPlaces(2).times(100).toNumber();
          this.answerResult.senseNum = countSense(this.answerResult)
          this.answerResult.senseStr = SENSE_ARR[this.answerResult.senseNum]
          return apiSubmitAnswered(this.answerResult, this.answeredList)
              .then(result => {
                  const {status, data} = result
                  return status === 200 ? data : false
              })
      },
      clear () {
          this.$reset()
      }
    }
})