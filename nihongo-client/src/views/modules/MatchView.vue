<script lang="ts" setup>
// name: MatchView
// date: 2023/5/18
// user: haruki
import {ref} from "vue"
import {useMatchStore} from "../../stores/match";
import { Dialog } from "@varlet/ui";
import {WordType} from "../../types";

const matchStore = useMatchStore()

const lessonChanged = () => {
  matchStore.get()
}

const leftSelected = (id:number, audio:string) => {
  matchStore.currId = id
  matchStore.play(audio)
}

let errorNum = ref<number>(0)
const rightSelected = (id: number) => {
  if(matchStore.currId !== id) {
    errorNum.value++
  }else{
    matchStore.right(id)
  }
}

</script>

<template>
  <var-select placeholder="请选择课程" v-model="matchStore.lesson" @change="lessonChanged">
    <var-option label="请选择" :value="0" />
    <template v-for="i in 48">
      <var-option :label="'第' + i + '课'" :value="i" />
    </template>
  </var-select>
  <var-divider />

  <var-row justify="space-between" :gutter="30">
    <var-col :span="12" direction="column">
      <var-space direction="column" size="large">
        <template v-for="item in matchStore.leftList">
          <var-button type="info" size="large"
              :color="item.id !== matchStore.currId && !matchStore.rightIds.includes(item.id) ? 'linear-gradient(to right, #69dbaa, #3a7afe)' : ''"
              :disabled="matchStore.rightIds.includes(item.id)"
              block
              @click="leftSelected(item.id, item.audio)">
            {{item.label}}
          </var-button>
        </template>
      </var-space>
    </var-col>
    <var-col :span="12"  direction="column">
      <var-space direction="column" size="large">
      <template v-for="item in matchStore.rightList">
        <var-button type="info" size="large"
            :color="!matchStore.rightIds.includes(item.id) ? 'linear-gradient(to left, #69dbaa, #3a7afe)' : ''"
            :disabled="matchStore.rightIds.includes(item.id)"
            block
            @click="rightSelected(item.id)">
          {{item.label}}
        </var-button>
      </template>
      </var-space>
    </var-col>
  </var-row>

  <var-divider />

  <var-cell title="当前答错次数" :description="errorNum.toString()" />

</template>

<style scoped>

</style>