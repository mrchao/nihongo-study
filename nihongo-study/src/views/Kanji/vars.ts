import {ref} from "vue";
import {KanjiResultType} from "../../types";

export const kanjiForm = ref<KanjiResultType>({
    kanji: "",
    lesson_num: 0,
    level_num: 0,
    html_code: "",
    translate_cn: "",
    hiragana:"",
    kana_json: []
})

export const kanjiFormRule = {
    kanji: [{
        required: true,
        message: '请输入单词'
    }],
    lesson: [{
        required: true,
        message: "请选择课本"
    }],
    level: [{
        required: true,
        message: "请选择等级"
    }]
}