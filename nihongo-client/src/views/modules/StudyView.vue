<script lang="ts" setup>
// name: StudyView
// date: 2023/5/18
// user: haruki
import {ref} from "vue"
import {useStudyStore} from "../../stores/study";
import { Snackbar } from "@varlet/ui";

const studyStore = useStudyStore()

const lessonChanged = () => {
  studyStore.get().then(result => {
    console.log(result)
    if(result) {
      Snackbar.info({
        content: "暂时没有可以复习的单词",
        duration: 1000
      })
    }
  })
}

const showDesc = ref<boolean>(false)
const confirmForget = () => {
  showDesc.value = false
  studyStore.forget()
}

</script>

<template>
  <var-select placeholder="请选择课程" v-model="studyStore.lesson" @change="lessonChanged">
    <var-option label="请选择" :value="0" />
    <template v-for="i in 48">
      <var-option :label="'第' + i + '课'" :value="i" />
    </template>
  </var-select>
  <var-divider />

  <div id="box">
    <span class="word" v-if="studyStore.currWord">{{ studyStore.currWord.kana }}</span>
  </div>

  <var-row justify="space-between" gutter="20">
    <var-col :span="6">
      <var-button block size="large" :disabled="studyStore.audioPlaying" @click="studyStore.ignore()">忽略</var-button>
    </var-col>
    <var-col :span="6">
      <var-button block size="large" :disabled="studyStore.audioPlaying" @click="showDesc = true" type="danger">忘记</var-button>
    </var-col>
    <var-col :span="6">
      <var-button block size="large" :disabled="studyStore.audioPlaying" @click="studyStore.blurry()" type="warning">模糊</var-button>
    </var-col>
    <var-col :span="6">
      <var-button block size="large" :disabled="studyStore.audioPlaying" @click="studyStore.remember()" type="success">记得</var-button>
    </var-col>
  </var-row>

  <var-dialog v-model:show="showDesc"
              :cancel-button="false"
              confirm-button-text="记住了"
              @confirm="confirmForget()"
  >
    <var-cell class="kanji" title="漢字" :description="studyStore.currWord?.kanji" />
    <var-cell class="desc" title="中文" :description="studyStore.currWord?.desc" />
  </var-dialog>

</template>

<style scoped>
#box {
  border: 1px solid #CCC;
  height: 150px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
#box .word {
  font-size: 2em;
}
#box .desc {

}
</style>