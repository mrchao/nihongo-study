<script lang="ts" setup>
// name: WordView
// date: 2023/3/3
// user: devine

import {ref} from "vue"
import { Snackbar } from '@varlet/ui'
import type { Input } from '@varlet/ui/types'
import {useRouter} from "vue-router";
import { useWriteStore } from "../../stores/write"

const router = useRouter()
const inputRef = ref<Input>()
const writeStore = useWriteStore()

const lessonChanged = () => {
  writeStore.get()
}

const replay = () => {
  writeStore.play()
  inputRef.value?.focus()
}

const hint = () => {
  Snackbar['info'](writeStore.currWord?.desc ?? '')
  inputRef.value?.focus()
}

const showWord = ref<boolean>(false)

const next = () => {
  if(writeStore.status === 1) {
    if (writeStore.inputValue) {
      if (writeStore.inputValue === writeStore.currWord?.kana || writeStore.inputValue === writeStore.currWord?.kanji) {
        Snackbar["success"]("答题正确")
      } else {
        Snackbar["error"]("答题错误")
      }
      writeStore.next()
    } else {
      showWord.value = true
      return
    }
  }
  if(writeStore.status === 2) {
      writeStore.get()
  }
  writeStore.next()
  inputRef.value?.focus()
}

const know = () => {
  writeStore.next()
  inputRef.value?.focus()
}

</script>

<template>

  <var-select placeholder="请选择课程" v-model="writeStore.lesson" @change="lessonChanged">
    <var-option label="请选择" :value="0" />
    <template v-for="i in 48">
      <var-option :label="'第' + i + '课'" :value="i" />
    </template>
  </var-select>

  <var-divider />

  <var-row class="menu" justify="space-between" align="center">
    <var-col :span="12">
      <var-space>
        <var-chip>共听写{{writeStore.total}}个词汇</var-chip>
        <var-chip>还有{{ writeStore.wordList.length }}个未答</var-chip>
      </var-space>
    </var-col>
    <var-col :span="12" justify="flex-end">
<!--      <var-space>-->
<!--        <var-button round type="primary" @click="isSetting = true">-->
<!--          <var-icon name="notebook" />-->
<!--        </var-button>-->
<!--        <var-button round type="primary" @click="toHistory">-->
<!--          <var-icon name="information" />-->
<!--        </var-button>-->
<!--      </var-space>-->
    </var-col>
  </var-row>

  <var-divider />

  <var-row gutter="20">
    <var-col :span="12">
      <var-button block type="info" :disabled="writeStore.currWord === undefined" @click="replay">重 放</var-button>
    </var-col>
    <var-col :span="12">
      <var-button block type="info" :disabled="writeStore.currWord === undefined" @click="hint">提 示</var-button>
    </var-col>
  </var-row>

  <var-divider />

  <var-row>
    <var-input ref="inputRef" size="large" placeholder="请输入仮名或者漢字" v-model="writeStore.inputValue" @keyup.enter="next()" />
    <var-button v-if="writeStore.status !== 2" type="primary" block @click="next()">
      {{ writeStore.status === 0 ? '开始答题' : ( writeStore.inputValue === '' ? '忘记' : '下一题') }}
    </var-button>
    <var-button v-else type="success" block @click="next()">重新开始</var-button>
  </var-row>


  <var-row v-if="writeStore.status === 2">
    <var-divider />
    <template  v-for="item in writeStore.resultList">
      <var-cell>
        <template #description>
          <var-space>
            <var-chip plain type="primary">{{ item.kana }} </var-chip>
            <var-chip v-if="item.kanji" plain type="info">{{ item.kanji }}</var-chip>
            <var-chip plain type="success">{{ item.desc }}</var-chip>
          </var-space>
        </template>
        <template #extra>
          <var-chip v-if="item.status === 0" type="warning">遗忘</var-chip>
          <var-chip v-if="item.status === 1" type="danger">错误</var-chip>
        </template>
      </var-cell>
    </template>
  </var-row>


  <var-dialog
      title="単語提示"
      v-model:show="showWord"
      :cancel-button="false"
      confirm-button-text="知道了"
      @confirm="() => showWord = false"
      @closed="() => know()"
  >
    <var-cell title="仮名" :description="writeStore.currWord?.kana" />
    <var-cell title="漢字" :description="writeStore.currWord?.kanji" />
    <var-cell title="中国語" :description="writeStore.currWord?.desc" />
  </var-dialog>

<!--  <var-row justify="center">-->
<!--    <var-paper inline :elevation="2" width="80vw" height="20vh" :radius="8">-->
<!--      <var-space class="countdown" align="center">-->
<!--        <var-button round type="info" :disabled="!starting"  @click="playSound">-->
<!--          <var-icon name="refresh" />-->
<!--        </var-button>-->
<!--        <var-countdown-->
<!--            :auto-start="false"-->
<!--            format="mm : ss : SSS"-->
<!--            :time="answerStore.countTimeMs"-->
<!--            ref="countdownRef"-->
<!--        />-->
<!--      </var-space>-->
<!--      <var-chip v-if="starting"-->
<!--          style="position: absolute;-->
<!--          top:1em;-->
<!--          left: 1em"-->
<!--          type="info">-->
<!--        # {{ answerStore.currAnswer.dictId }}-->
<!--      </var-chip>-->

<!--      <var-loading type="wave" v-if="!answerStore.currDict" />-->
<!--      <template v-else>-->
<!--          <transition name="detail">-->
<!--            <span-->
<!--                v-if="isLoaded"-->
<!--                :style="{fontSize: topicCnFontSize + 'em'}">-->
<!--              {{ answerStore.currDict?.translate_cn }}-->
<!--            </span>-->
<!--          </transition>-->
<!--      </template>-->
<!--    </var-paper>-->
<!--  </var-row>-->

<!--  <input placeholder="输入答案" class="answer-input" ref="inputRef" v-model="answerStore.currAnswer.value"  @keyup.enter="nextAnswer" />-->

<!--  <var-button v-if="!starting"-->
<!--      style="margin-top: 2vh"-->
<!--      block size="large"-->
<!--      type="success"-->
<!--      @click="startAnswer">-->
<!--    开始答题-->
<!--  </var-button>-->
<!--  <var-space v-else style="margin-top: 2vh" justify="center" :size="[50,50]">-->
<!--    <var-button size="large" type="info" :disabled="!isLoaded" @click="jumpAnswer" >忘记了</var-button>-->
<!--    <var-button size="large" type="success" :disabled="answerStore.currAnswer.value === ''" @click="nextAnswer">下一个</var-button>-->
<!--  </var-space>-->

<!--  <var-popup :default-style="false" v-model:show="isAnswered">-->
<!--    <var-result type="success" class="answer-result">-->
<!--      <template #description>-->
<!--        <var-cell border title="答题总数" extra-class="answer-result-cell">-->
<!--          <template #extra>-->
<!--            {{ answerStore.answerResult.amount }}-->
<!--          </template>-->
<!--        </var-cell>-->
<!--        <var-cell border title="答题时间">-->
<!--          <template #extra>-->
<!--            {{ answerStore.answerResult.spendTime }} 秒-->
<!--          </template>-->
<!--        </var-cell>-->
<!--        <var-cell border title="正确率">-->
<!--          <template #extra>-->
<!--            {{ answerStore.answerResult.scoreNum }} %-->
<!--          </template>-->
<!--        </var-cell>-->
<!--        <var-cell border title="综合评价">-->
<!--          <template #extra>-->
<!--            <var-chip type="info" class="sense-text">{{ answerStore.answerResult.senseStr }}</var-chip>-->
<!--          </template>-->
<!--        </var-cell>-->
<!--      </template>-->
<!--      <template #footer>-->
<!--        <var-button type="success" @click="clear">👌 知道了</var-button>-->
<!--      </template>-->
<!--    </var-result>-->
<!--  </var-popup>-->

</template>


<style scoped>

</style>