export interface OptionType {
    label: string;
    value: number;
    children?: OptionType[]
}

export interface TopicType {
    id: number
    number: number
    question: string[]
    is_audio: boolean
    audio_url: string
    answer_json: string[]
}

export interface AnswerResultKanaType {
    dictId: number
    useTimeMs: number
    value: string
    result: boolean|null
}

export interface AnswerResultExtType extends AnswerResultType {
    id: number
    dateTs: number
    dayTs: number
    dateStr: string
    timeTs: string
    reportJson: AnswerResultKanaType[]
    sense: number
    userId: number
}

export interface AnswerResultType {
    senseNum: number
    senseStr: string
    scoreNum: number
    amount: number
    spendTime: number
    rightNum: number
    errorNum: number
    secIn10sNum: number
    secOut15sNum: number
}

export interface UserType {
    nickname: string
    device_id: string
    is_login?: boolean
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
}

export interface CategoryList {
    [unitNum: string]: {
        [lessonNum: string]: DictType[]
    }
}