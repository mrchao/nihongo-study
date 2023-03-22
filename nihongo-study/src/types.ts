
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