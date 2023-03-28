
import { IpadicFeatures } from "kuromoji";

export interface KanaType extends IpadicFeatures
{
    isKanji: boolean
    romaji: string
    hiragana: string
}

export interface KanjiResultType
{
    id?: number
    kanji: string
    hiragana:string
    translate_cn: string
    html_code: string
    lesson_num: number
    level_num: number
    kana_json?: KanaType[]
}

export interface TranslateResultType {
    form: string;
    translate: string
}

export interface DictType {
    id: number
    html_code:string
    ruby_code:string
    unit_num: number
    lesson_num: number
    sort_num: number
    sound_url: string
    translate_cn: string
    attr: string
    kana: string
    kanji: string
    kanjiArr: string[]
    isSync: boolean
    isEnglish: boolean
    hasKanji: boolean
}