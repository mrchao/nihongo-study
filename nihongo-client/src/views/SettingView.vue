<script lang="ts" setup>
// name: Setting
// date: 2023/3/5
// user: devine
import {ref, onMounted} from "vue"
import type { CheckboxGroup } from "@varlet/ui/types"
import { useStorage } from "vue3-storage"
import { Snackbar } from "@varlet/ui"
import {useBookStore} from "../stores/book";
import {useDictStore} from "../stores/dict";

const emit = defineEmits(['onComplete'])

const storage = useStorage()
const dictStore = useDictStore()
const bookStore = useBookStore()

onMounted(() => {
  dictStore.load().then(() => {
    bookStore.load()
  })
})

// 单元选择项
const unitRef = ref<CheckboxGroup>()
const unitCheckAll = ref<boolean>(false)
const unitToggle = (value:boolean) => value ? unitRef.value?.checkAll() : unitRef.value?.reset()
// 课选择项
const lessonRef = ref<CheckboxGroup>()
const lessonCheckAll = ref<boolean>(false)
const lessonToggle = (value:boolean) => value ? lessonRef.value?.checkAll() : lessonRef.value?.reset()

const onComplete = () => {
  bookStore.setCache()
  Snackbar.success("成功加载"+bookStore.kanaList.length+"个单词")
  emit("onComplete")
}

</script>

<template>
  <div class="cell-title">等级</div>
  <var-cell>
    <var-checkbox-group ref="unitRef" v-model="bookStore.unitValues">
      <template v-for="(option, idx) in bookStore.getUnitOptions" :key="idx">
        <var-checkbox :checked-value="option.value">{{ option.label }}</var-checkbox>
      </template>
    </var-checkbox-group>
    <var-checkbox v-model="unitCheckAll" type="primary" @change="unitToggle">全选/取消</var-checkbox>
  </var-cell>
  <div class="cell-title">课程</div>
  <var-cell>
    <var-checkbox-group ref="lessonRef" v-model="bookStore.lessonValues">
      <template v-for="(option, idx) in bookStore.getLessonOptions" :key="idx">
        <var-checkbox  :checked-value="option.value">{{ option.label }}</var-checkbox>
      </template>
    </var-checkbox-group>
    <var-checkbox v-model="lessonCheckAll" type="primary" @change="lessonToggle">全选/取消</var-checkbox>
  </var-cell>

  <var-cell>
    <var-button block type="primary" @click="onComplete">保存</var-button>
  </var-cell>

</template>

<style scoped>

</style>