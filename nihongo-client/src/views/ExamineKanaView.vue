<script lang="ts" setup>
// name: WordView
// date: 2023/3/3
// user: devine

import {ref, computed, onMounted} from "vue"
import { Snackbar } from '@varlet/ui'
import type { Input, Countdown } from '@varlet/ui/types'
import { useStorage } from "vue3-storage"
import {useBookStore} from "../stores/book"
import { useAnswerHistoryStore } from "../stores/answerHistory";
import vwSetting from "./SettingView.vue"
import {useAnswerStore} from "../stores/answer"
import {useRouter} from "vue-router";

const storage = useStorage()
const bookStore = useBookStore()
const answerStore = useAnswerStore()
const answerHistoryStore = useAnswerHistoryStore()
const router = useRouter()

const audio = ref<HTMLAudioElement>()
const inputRef = ref<Input>()
const countdownRef = ref<Countdown>()
const isSetting = ref<boolean>(false)
const isLoaded = ref<boolean>(false)
const starting = ref<boolean>(false)
const isAnswered = ref<boolean>(false)

onMounted(() => {
  bookStore.load()
  if(bookStore.kanaList.length === 0) {
    isSetting.value = true
  }
})

// 3em 4
const topicCnFontSize = computed(() => {
  const fontLength = answerStore.currDict?.translate_cn.length ?? 0
  return fontLength * 3 > 15 ? Math.floor(150 / fontLength) / 10  : 3
})
const soundUrl = computed(() => {
  return answerStore.currDict?.sound_url || ""
})

const startAnswer = () => {
  answerStore.load()
  if(answerStore.answerSortList.length === 0) {
    Snackbar.error("没有单词需要测试")
    return
  }
  countdownRef.value?.start()
  jumpAnswer()
  starting.value = true
  inputRef.value?.focus()
}

const nextAnswer = () => {
  if(answerStore.currAnswer.value === '') {
    Snackbar.warning("请输入单词后提交")
    inputRef.value?.focus()
    return
  }
  jumpAnswer()
}

const jumpAnswer = () => {
  preLoad()
  countdownRef.value?.pause()
  const isEnd = answerStore.next()
  if(!isEnd) {
    inputRef.value?.focus()
    playSound()
  }else{
    endAnswer()
  }
}

const endAnswer = async () => {
  countdownRef.value?.reset()
  starting.value = false
  inputRef.value?.blur()
  if(answerStore.answerResult.amount > 0) {
    isAnswered.value = true
    const result = await answerStore.submit()
    if(result) {
      isAnswered.value = true
      answerHistoryStore.reset()
    }
  }
}

const clear = () => {
  isAnswered.value = false
  answerStore.clear()
}

const playSound = () => {
  const sound = new Audio(soundUrl.value)
  sound.play()
  sound.onended = () => {
    countdownRef.value?.start()
    isLoaded.value = true
  }
}
const preLoad = () => {
  countdownRef.value?.pause()
  isLoaded.value = false
}

const toHistory = () => {
  router.push("/word/history")
}

</script>

<template>
  <var-popup position="top" v-model:show="isSetting">
    <vw-setting @onComplete="isSetting = false" />
  </var-popup>

  <var-row class="menu" justify="space-between" align="center">
    <var-col :span="12">
      <var-space>
        <var-chip>共有{{bookStore.kanaList?.length}}个词汇</var-chip>
        <var-chip>还有{{ answerStore.answerSortList.length }}个未答</var-chip>
      </var-space>
    </var-col>
    <var-col :span="12" justify="flex-end">
      <var-space>
        <var-button round type="primary" @click="isSetting = true">
          <var-icon name="notebook" />
        </var-button>
        <var-button round type="primary" @click="toHistory">
          <var-icon name="information" />
        </var-button>
      </var-space>
    </var-col>
  </var-row>

  <var-row justify="center">
    <var-paper inline :elevation="2" width="80vw" height="20vh" :radius="8">
      <var-space class="countdown" align="center">
        <var-button round type="info" :disabled="!starting"  @click="playSound">
          <var-icon name="refresh" />
        </var-button>
        <var-countdown
            :auto-start="false"
            format="mm : ss : SSS"
            :time="answerStore.countTimeMs"
            ref="countdownRef"
        />
      </var-space>
      <var-chip v-if="starting"
          style="position: absolute;
          top:1em;
          left: 1em"
          type="info">
        # {{ answerStore.currAnswer.dictId }}
      </var-chip>

      <var-loading type="wave" v-if="!answerStore.currDict" />
      <template v-else>
          <transition name="detail">
            <span
                v-if="isLoaded"
                :style="{fontSize: topicCnFontSize + 'em'}">
              {{ answerStore.currDict?.translate_cn }}
            </span>
          </transition>
      </template>
    </var-paper>
  </var-row>

  <input placeholder="输入答案" class="answer-input" ref="inputRef" v-model="answerStore.currAnswer.value"  @keyup.enter="nextAnswer" />

  <var-button v-if="!starting"
      style="margin-top: 2vh"
      block size="large"
      type="success"
      @click="startAnswer">
    开始答题
  </var-button>
  <var-space v-else style="margin-top: 2vh" justify="center" :size="[50,50]">
    <var-button size="large" type="info" :disabled="!isLoaded" @click="jumpAnswer" >忘记了</var-button>
    <var-button size="large" type="success" :disabled="answerStore.currAnswer.value === ''" @click="nextAnswer">下一个</var-button>
  </var-space>
<!--  <var-button @click="endAnswer">测试提交</var-button>-->

  <var-popup :default-style="false" v-model:show="isAnswered">
  <var-result type="success" class="answer-result">
    <template #description>
      <var-cell border title="答题总数" extra-class="answer-result-cell">
        <template #extra>
          {{ answerStore.answerResult.amount }}
        </template>
      </var-cell>
      <var-cell border title="答题时间">
        <template #extra>
          {{ answerStore.answerResult.spendTime }} 秒
        </template>
      </var-cell>
      <var-cell border title="正确率">
        <template #extra>
          {{ answerStore.answerResult.scoreNum }} %
        </template>
      </var-cell>
      <var-cell border title="综合评价">
        <template #extra>
          <var-chip type="info" class="sense-text">{{ answerStore.answerResult.senseStr }}</var-chip>
        </template>
      </var-cell>
    </template>
    <template #footer>
      <var-button type="success" @click="clear">👌 知道了</var-button>
    </template>
  </var-result>
  </var-popup>

</template>


<style scoped>
.menu {
  margin-top: 1em
}
.var-paper {
  margin-top: 5vh;
  justify-content: center;
  align-items: center;
  position: relative;
}
.countdown {
  position: absolute;
  width: 150px;
  top: 1em;
  right: 1em;
}

.answer-input {
  margin-top: 5vh;
  border: 0;
  width: 100%;
  text-align: center;
  height: 9vh;
  font-size: 7vh;
  color: #a6a6a6;
  background-color: #e3e3e3;
  outline: none;
  padding: 0
}
.answer-input:focus {
  color: #338bff;
}

.answer-result {
  width: 70vw;
}
.sense-text {
  height: 8vh;
  font-size: 48px;
}

.detail-enter-from,
.detail-leave-to
{
  opacity: 0 ;
}
.detail-enter-active {
  animation: zoomIn 1.5s ease;
}
.detail-leave-active {
  animation: zoomOut 1.5s ease;
}

@keyframes bounce-in {
  0% { transform: scale(0); }
  50% { transform: scale(1.25); }
  100% { transform: scale(1); }
}
</style>