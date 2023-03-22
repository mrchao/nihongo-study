<template>
  <var-row :gutter="8">
    <var-col :span="6">
        <div
            v-for="menuLevelGroup in topicMenuList"
            :key="menuLevelGroup.value"
        >
          {{ menuLevelGroup.label }}
          <var-divider />
          <var-cell @click="loadTopicList(menuLevelGroup.value, menu.value )" border ripple v-for="menu in menuLevelGroup.children" :key="menu.value">
            {{ menu.label }}
          </var-cell>
        </div>
    </var-col>
    <var-col :span="18">
      <div>

        <var-card outline
            :size="[20,20]"
            direction="column"
            v-for="topic in topicList" :key="topic.id"
        >
          <template #title>
            <var-button class="topic-number" type="info" round>{{topic.number}}</var-button>
          </template>
          <template #description>
            <div class="topic-question">
              <p v-for="value in topic.question" v-html="value" />
              <audio controls v-if="topic.is_audio" :src="topic.audio_url" />
            </div>
            <var-divider />
            <var-cell ripple v-for="(answer, idx) in topic.answer_json" :key="idx">
              <strong>{{radioIdx[idx]}}.</strong>{{ answer }}
            </var-cell>
          </template>
        </var-card>

      </div>
    </var-col>
  </var-row>
</template>

<script lang="ts" setup>
import { onMounted, ref } from "vue"
import axios from "axios";
import {OptionType, TopicType} from "../types"
const topicMenuList = ref<OptionType[]>([])
const topicList = ref<TopicType[]>([])
const radioIdx = ['a', 'b', 'c', 'd']

onMounted( () => {
  axios.get("/api/topic/menuList").then(result => {
    topicMenuList.value = result.data
  })
})

const loadTopicList = (level: number, topicGroup: number) => {
  axios.get("/api/topic/fetchByTopicGroup", {
    params: {
      level: level,
      topicGroup: topicGroup
    }
  }).then(result => {
    topicList.value = result.data
  })
}

</script>

<style scoped>
.var-card {
  margin: 1em 0;
}
.topic-number {
  width: 2.3em;
  margin-top: 1em;
  margin-left: 1em;
}
.topic-question {
  margin: 1em;
  padding: .5em;
  font-size: .8em;
  border-radius: .3em;
  border: 1px #dddddd dashed;
}
strong {
  margin-right: 1em;
}
</style>