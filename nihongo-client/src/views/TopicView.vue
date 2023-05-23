<template>
  <var-row :gutter="8">
    <var-col :span="6">

      <var-collapse v-model="topicName" accordion :offset="false">
        <template v-for="menuLevelGroup in topicMenuList">
          <var-collapse-item :title="menuLevelGroup.label" :name="menuLevelGroup.value">
            <template v-for="menu in menuLevelGroup.children">
              <var-cell border @click="loadTopicList(menuLevelGroup.value, menu.value )">{{ menu.label }}</var-cell>
            </template>
          </var-collapse-item>
        </template>
      </var-collapse>

    </var-col>
    <var-col :span="18" direction="column">

      <var-card outline
                :elevation="false"
                :size="[20,20]"
                direction="column"
                v-for="(topic, idx) in topicList"
                :key="topic.id"
                :class="isComplate && !answeredList[idx].result ? 'error':'' "
      >
        <template #title>
          <var-space>
            <var-chip size="mini" type="primary">{{topic.number}}</var-chip>
            <var-link style="white-space: normal" underline="none" type="primary" v-html="topic.title"></var-link>
          </var-space>
        </template>
        <template #description>
          <var-divider description="问题" />
          <div class="topic-question">
            <template v-for="(question, idx) in topic.newQuestion">
              <div v-if="question.type === 0" v-html="question.value" />
              <audio controls v-if="question.type === 1" :src="question.value" />
              <var-image width="80" height="80" fit="contain" v-if="question.type === 2" :src="question.value" />
            </template>
          </div>
          <var-divider description="选项" />
          <div class="answer-list">
            <var-radio-group direction="vertical" v-model="answeredList[idx].value">
              <template v-for="(answer, idx) in topic.newAnswerJson" :key="idx">
                <var-radio
                    :checked-value="idx + 1"
                >
                  <strong>{{radioIdx[idx]}}.</strong>
                  <span v-if="answer.type === 0">{{ answer.value }}</span>
                  <audio controls v-if="answer.type === 1" :src="answer.value" />
                  <var-image width="80" height="80" fit="contain" v-if="answer.type === 2" :src="answer.value" />
                </var-radio>
              </template>
            </var-radio-group>
          </div>
          <template v-if="isComplate && !answeredList[idx].result">
            <var-divider description="答案" />
            <div class="analysis">
              <span v-html="topic.analysis" />
            </div>
          </template>
        </template>
      </var-card>
      <var-button block type="primary" @click="submit" >提交</var-button>
    </var-col>
  </var-row>

</template>

<script lang="ts" setup>
import { onMounted, ref } from "vue"
import axios from "axios";
import { Snackbar } from '@varlet/ui'
import {OptionType, TopicType} from "../types"
import {useStorage} from "vue3-storage";
const storage = useStorage()
const topicMenuList = ref<OptionType[]>([])
const topicList = ref<TopicType[]>([])
const radioIdx = ['a', 'b', 'c', 'd', 'e', 'f', 'g']
const topicName = ref<string>("")
const isComplate = ref<boolean>(false)
const answeredList = ref<{
    value: number
    result: boolean
  }[]>([])

onMounted( async () => {
  let list = storage.getStorageSync("menuList");
  if(!list) {
    const {status , data } = await axios.get("/api/topic/menuList")
    storage.setStorageSync("menuList", data, 7200 * 1000);
    list = data
  }
  topicMenuList.value = list

  const topicListCached = storage.getStorageSync<TopicType[]>("topicList");
  if(topicListCached) {
    const tempList = storage.getStorageSync<{value:number, result: boolean}[]>("answeredList")
    topicListCached.forEach((item, idx:number) => {
      const value = tempList ? tempList[idx]?.value ?? 0 : 0
      answeredList.value.push({
        value: value,
        result: false
      })
    })
    topicList.value = topicListCached
  }
})

const loadTopicList = async (level: number, topicGroup: number) => {
    const {status, data} = await axios.get<TopicType[]>("/api/topic/fetchByTopicGroup", {
      params: {
        level: level,
        topicGroup: topicGroup
      }
    })
    storage.setStorageSync("topicList", data, 7200 * 1000);

  answeredList.value = []
  data.forEach((item) => {
    answeredList.value.push({
      value: 0,
      result: false
    })
  })
  topicList.value = data
}

const submit = () => {
  if(answeredList.value.length !== topicList.value.length) {
    Snackbar.warning("还有题没有选择答案，请检查后提交")
    return
  }
  storage.setStorageSync("answeredList", answeredList.value, 7200 * 1000)

  isComplate.value = true
  topicList.value.forEach((topic, idx) => {
    console.log(topic.answered_num, answeredList.value[idx].value)
    answeredList.value[idx].result = topic.answered_num === answeredList.value[idx].value
  })
  console.log(answeredList.value);
}

</script>

<style scoped>
.var-card {
  margin-top: 1em;
  padding: .5em
}
.var-image {
  border: 1px solid #cccccc;
}
.error {
  border-color: red
}
/*.topic-number {*/
/*  margin-top: 1em;*/
/*  margin-left: 1em;*/
/*}*/
.topic-question {
  /*margin: 1em;*/
  padding: 1em;
  font-size: .8em;
  border-radius: 1em;
  background-color: cornsilk;
}

.answer-list {
  padding-left: 1em;
}
strong {
  margin-right: .5em;
}

.analysis {
  padding: 1em;
  font-size: .8em;
  border-radius: 1em;
  background-color: lightcyan;
}
</style>