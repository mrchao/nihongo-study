<script lang="ts" setup>
import {ref, watch, onMounted} from "vue"
import axios from "axios"
import {isJapanese, isKana, isKanji, isRomaji, toHiragana, toKana, toRomaji} from 'wanakana'
import type {KanaType, KanjiResultType, TranslateResultType} from "../../types"
import { lessonOptions, levelOptions} from "../../vars"
import { Kanji } from "../../utils/Kuroshiro"
import { kanjiForm, kanjiFormRule } from "./vars";
import { useLoadingBar } from "naive-ui"
import {debounce} from "../../utils";
import { isNumber, isString } from 'lodash'
import { useNotification, useMessage } from 'naive-ui'
import type { InputInst } from "naive-ui"

const isLoading = ref<boolean>(false)
const cardRef = ref(null)
const loadingBar = useLoadingBar()
const K = Kanji.getInstance()
const translate = async () => {
  kanjiForm.value.kanji = kanjiForm.value.kanji.trim().toLowerCase()

  if(isKana(kanjiForm.value.kanji)) {
    kanjiForm.value.hiragana = kanjiForm.value.kanji
  }
  if(isRomaji(kanjiForm.value.kanji)) {
    kanjiForm.value.hiragana = toKana(kanjiForm.value.kanji)
  }

  if(Kanji.utils.hasKanji(kanjiForm.value.kanji)) {
    kanjiForm.value.hiragana = await K.getKuroshiro().convert(kanjiForm.value.kanji);
    kanjiForm.value.html_code = await K.getKuroshiro().convert(kanjiForm.value.kanji, {
      mode:"furigana", to:"hiragana"
    });
  }

  let result: KanaType[] = []
  if(isJapanese(kanjiForm.value.kanji)) {
    result = K.tokenize(kanjiForm.value.kanji)
  }
  if(isKana(kanjiForm.value.hiragana)) {
    result = K.tokenize(kanjiForm.value.hiragana)
  }

  result.forEach((item, index) => {
    result[index].hiragana = toHiragana(item.reading || '')
    result[index].romaji = toRomaji(item.reading || '')
    result[index].isKanji = isKanji(item.basic_form)
  })

  axios.get<TranslateResultType>("/api/translate", {
    params: kanjiForm.value
  }).then(ret => {
    if(ret.status === 200) {
      const {data} = ret
      kanjiForm.value.translate_cn = data.translate;
      kanjiForm.value.kana_json = result;
    }
  }).finally(() => {
    isLoading.value = false
    loadingBar.finish()
  })

}


const timer = ref(0)
const onUpdate = () => {
  if(kanjiForm.value.kanji.trim() !== '') {
    timer.value = debounce(timer.value, () => {
      isLoading.value = true
      loadingBar.start()
      translate()
    },1000)
  }
}

const isSubmit = ref<boolean>(true)
watch(kanjiForm, (newVal) => {
  isSubmit.value = !(newVal.kanji.trim() && newVal.level_num > 0 && newVal.lesson_num > 0)
},{
  deep: true,
  immediate: true
})


const notification = useNotification();
const message = useMessage()

const submit = () => {
  isLoading.value = true
  axios.post<number|string|boolean>("/api/admin/kanjiSubmit", kanjiForm.value).then(result => {
    const { data } = result
    if(isString(data)) {
      message.error(data)
      return
    }
    if(data === true || isNumber(data)) {
      notification.success({
        content: "单词录入成功"
      })
      getKanjiList()
      return
    }
  }).finally(() => {
    isLoading.value = false
  })
}

const editing = ref<boolean>(false)
const editInput = ref<InputInst|null>(null)
const editTranslate = () => {
  editing.value = true
  if(editInput.value !== null) {
    editInput.value.focus()
  }
}
const editBlur = () => {
  editing.value = false
}

const kanjiListRefresh = ref<boolean>(false)
const getKanjiList = () => {
  kanjiListRefresh.value = true
  axios.get<KanjiResultType[]>("/api/admin/getKanjiList").then(result => {
    const {data} = result
    kanjiList.value = data
  }).finally(() => {
    kanjiListRefresh.value = false
  })
}

const kanjiList = ref<KanjiResultType[]>([])
onMounted(() => {
  getKanjiList()
})

const editKanji = (id?: number) => {
  const kanji = kanjiList.value.find(kanji => kanji.id === id);
  if(kanji) {
    kanjiForm.value.id = kanji.id
    kanjiForm.value.kanji = kanji.kanji
    kanjiForm.value.html_code = kanji.html_code
    kanjiForm.value.hiragana = kanji.hiragana
    kanjiForm.value.translate_cn = kanji.translate_cn
    kanjiForm.value.lesson_num = kanji.lesson_num
    kanjiForm.value.level_num = kanji.level_num
    if(isString(kanji.kana_json)) {
      kanjiForm.value.kana_json = JSON.parse(kanji.kana_json)
    }
  }
}

const cancelEdit = () => {
  kanjiForm.value.id = undefined
  kanjiForm.value.kanji = ""
  kanjiForm.value.html_code = ""
  kanjiForm.value.hiragana = ""
  kanjiForm.value.translate_cn = ""
  kanjiForm.value.kana_json = []
}

</script>

<template>
  <n-grid item-responsive cols="12" x-gap="16" y-gap="16">
    <n-grid-item span="12 640:6 840:4">
      <n-form ref="formRef" :model="kanjiForm" :rules="kanjiFormRule">
        <n-form-item label="单词" path="word">
          <n-input
              v-model:value="kanjiForm.kanji"
              placeholder="请输入一个日语单词"
              @keyup="onUpdate"
          />
        </n-form-item>
        <n-form-item label="早道所属等级" path="level">
          <n-radio-group v-model:value="kanjiForm.level_num">
            <n-space>
              <template v-for="(option, idx) in levelOptions" :key="idx">
              <n-radio :value="idx + 1">
                {{ option }}
              </n-radio>
              </template>
            </n-space>
          </n-radio-group>
        </n-form-item>
        <n-form-item label="新标日所属章节" path="lesson">
          <n-select :options="lessonOptions" v-model:value="kanjiForm.lesson_num" />
        </n-form-item>
        <n-button :loading="isLoading" block type="info" :disabled="isSubmit" @click="submit">录入</n-button>
      </n-form>
    </n-grid-item>
    <n-grid-item span="12 640:6 840:8">
      <n-spin :show="isLoading">
        <template #description>正在获取数据中...</template>
      <n-card ref="cardRef" title="单词信息" size="small">
        <n-descriptions label-placement="top" bordered :column="3">
          <n-descriptions-item label="注音" :span="4" content-style="padding-top:1.75em">
            <n-text v-html="kanjiForm.html_code" />
          </n-descriptions-item>
          <n-descriptions-item label="中文" :span="4">
            <n-space justify="center">
              <n-gradient-text v-if="!editing" type="info">{{ kanjiForm.translate_cn }}</n-gradient-text>
              <n-input v-if="editing"
                  ref="editInput"
                  v-model:value="kanjiForm.translate_cn"
                  placeholder="修改翻译"
                  @blur="editBlur"
              />
              <n-button v-if="kanjiForm.translate_cn && !editing" type="info" size="tiny" @click="editTranslate">修改</n-button>
            </n-space>
          </n-descriptions-item>
          <template :key="idx" v-for="(kana, idx) in kanjiForm.kana_json">
            <n-descriptions-item label="基础">
              {{ kana.basic_form }}
            </n-descriptions-item>
            <n-descriptions-item label="罗马音">
              {{ kana.romaji }}
            </n-descriptions-item>
            <n-descriptions-item label="片假名">
              {{ kana.hiragana }}
            </n-descriptions-item>
          </template>
        </n-descriptions>
      </n-card>
      </n-spin>
    </n-grid-item>
    <n-grid-item span="12">
      <n-spin :show="kanjiListRefresh || isLoading">
      <n-space>
      <template v-for="kanji in kanjiList" :key="kanji.id">
        <n-tag
            v-if="kanji.id !== kanjiForm.id"
            :type="kanji.html_code ? 'info':'warning'"
            @click="editKanji(kanji.id)"
        >
          <span v-if="kanji.html_code" v-html="kanji.html_code" />
          <template v-else>{{ kanji.kanji }}</template>
        </n-tag>
        <n-tag type="error" v-else @click="cancelEdit">
          <span v-if="kanji.html_code" v-html="kanji.html_code" />
          <template v-else>{{ kanji.kanji }}</template>
        </n-tag>
      </template>
      </n-space>
      </n-spin>
    </n-grid-item>
  </n-grid>
</template>

<style scoped>
.n-tag {
  height: 3em
}
</style>