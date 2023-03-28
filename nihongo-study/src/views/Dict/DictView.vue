<script lang="ts" setup>
// name: DictView
// date: 2023/3/24
// user: haruki
import {ref, onMounted} from "vue"
import axios from "axios";
import {DictType} from "../../types";
import { useMessage } from 'naive-ui'

const message = useMessage()
const list = ref<Array<DictType>>([])

onMounted(() => {
  getKanjiEqKana()
})

const getKanjiEqKana = () => {
  axios.get<Array<DictType>>("/api/admin/fetchByKanjiEqKana").then(result => {
    const {status, data} = result
    if(status == 200) {
      list.value = data
    }
  })
}

const currId = ref<number>(0);
const inputValue = ref<string>("")
const edit = (id: number) => {
  currId.value = id
  inputValue.value = ""
}
const addKanji = () => {
  const dict = list.value.find(item => item.id === currId.value)
  if(dict &&　inputValue.value !== '' && !dict.kanjiArr.includes(inputValue.value)) {
    dict.kanjiArr.push(inputValue.value)
    dict.isSync = true
    inputValue.value = ""
  }
}

const syncKanjiArr = () => {
  const syncList = list.value.filter(item => item.isSync)
  axios.post("/api/admin/syncKanjiList", syncList).then(result => {
    const { status, data } = result
    if(status == 200 && data) {
      message.success("同步成功")
      getKanjiEqKana()
    }
  })
}

</script>

<template>
  <n-button type="success" @click="syncKanjiArr">同步</n-button>
  <n-table :bordered="false" :single-line="false" size="small">
    <thead>
      <tr>
        <th>ID</th>
        <th>chinese</th>
        <th>kana</th>
        <th>kanjiArr</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in list" :class="[item.isEnglish || item.hasKanji　|| item.attr === '叹' ? 'gray':'']">
        <td>{{item.sort_num}}</td>
        <td>{{item.translate_cn}}</td>
        <td>
          <n-popover trigger="click">
            <template #trigger>
              <n-button size="tiny">{{item.kana}}</n-button>
            </template>
            <span>
              <n-input v-model:value="inputValue" @focus="edit(item.id)" @keyup.enter="addKanji"  />
            </span>
          </n-popover>
        </td>
        <td>
          <n-space>
            <n-tag size="small" :bordered="false" v-for="(value, idx) in item.kanjiArr" :key="idx">
              {{ value }}
            </n-tag>
          </n-space>
        </td>
      </tr>
    </tbody>
  </n-table>
</template>

<style scoped>
.gray td {
  color: #CCC;
}
</style>