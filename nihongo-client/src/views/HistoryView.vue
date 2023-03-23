<script lang="ts" setup>
// name: HistoryView
// date: 2023/3/19
// user: haruki
import {ref, onMounted} from "vue"
import { AnswerResultKanaType} from "../types";
import {useStorage} from "vue3-storage";
import {useDictStore} from "../stores/dict";
import {useUserStore} from "../stores/user";
import {useAnswerHistoryStore} from "../stores/answerHistory";
import {useRouter} from "vue-router";
const storage = useStorage()
const dictStore = useDictStore()
const userStore = useUserStore()
const answeredHistoryStore = useAnswerHistoryStore()
const getKana = dictStore.getDictById;

const reportList = ref<AnswerResultKanaType[]>([])
const show = ref<boolean>(false)
const router = useRouter()

onMounted(() => {
  dictStore.load()
  answeredHistoryStore.load()
})

const onSelected = (reportJson: AnswerResultKanaType[]) => {
  reportList.value = reportJson
  show.value = true
}

</script>

<template>
  <div v-for="group in answeredHistoryStore.historyList" :key="group.label">
    <div class="title">{{group.label}}</div>
    <var-cell ripple border v-for="(item, idx) in group.value" :key="idx" @click="onSelected(item.reportJson)">
      <var-space align="center">
        <var-chip size="mini">📅{{ item.timeTs }}</var-chip>
        <var-chip size="mini" >✅ {{ item.rightNum }}</var-chip>
        <var-chip size="mini" >❌ {{ item.errorNum }}</var-chip>
        <var-chip size="mini" type="warning">正确率：{{ item.scoreNum }} %</var-chip>
        <var-chip size="mini" type="primary">{{ item.senseStr }}</var-chip>
        <var-chip size="mini" type="success">⏱ ️{{ item.spendTime}}</var-chip>
      </var-space>
    </var-cell>
  </div>

  <var-result
      v-if="answeredHistoryStore.historyList.length === 0"
              class="result"
              type="empty"
              title="ここはありません"
              description="请去作答一次再来这里查看～">
    <template #footer>
      <var-button
          color="var(--result-empty-color)"
          text-color="#fff"
          @click="router.push('/word')">
        知道了
      </var-button>
    </template>
  </var-result>

  <var-popup position="top" v-model:show="show" style="max-height: 90vh; background-color: #e3e3e3; padding-left: 16px; padding-top: 16px">
  <var-row :gutter="16" style="width: 100%">
    <template v-for="kana in reportList" :key="kana.dictId">
    <var-col  :xs="24" :sm="8" :md="6" :lg="4" v-if="getKana(kana.dictId)">
        <var-card outline ripple elevation="0" >
          <template #description>
            <div class="card-head">
              <var-chip size="mini"># {{kana.dictId}}</var-chip>
              <var-chip type="info" size="mini">{{kana.useTimeMs}} 秒</var-chip>
            </div>
            <var-divider dashed margin="3px" />
            <var-space justify="space-around" align="center" class="kana">
              <template v-if="!kana.result">
                <span class="right" v-html="getKana(kana.dictId)?.html_code" />
                <span class="error" v-if="kana.value !== ''">{{ kana.value }}</span>
                <var-icon v-else color="orange" name="warning" />
              </template>
              <template v-else>
                <span v-html="getKana(kana.dictId)?.html_code" />
                <span>{{ kana.value }}</span>
              </template>
            </var-space>
          </template>
          <template #extra>
<!--            <var-button outline text type="primary" size="mini" @click="dictStore.ignore(kana.dictId)">忽略</var-button>-->
          </template>
        </var-card>
    </var-col>
    </template>
  </var-row>
  </var-popup>

</template>

<style scoped>
.var-col {
  margin-bottom: 1em;
}
.card-head {
  padding: 0 1em;
  padding-top: 3px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}
.title {
  color: #CCC;
  font-size: 12px;
  margin-top: 1em;
}
.kana {
  margin-top: 1em;
  height: 80px;
}
.kana span {
  color: var(--chip-default-color)
}
.kana span.right {
  color: var(--chip-success-color);
}
.kana span.error {
  color: var(--chip-danger-color);
}
</style>