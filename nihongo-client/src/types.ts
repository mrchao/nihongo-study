export interface OptionType {
    label: string;
    value: number;
    children?: OptionType[]
}

export interface TopicType {
    title: string
    id: number
    number: number
    question: string[]
    is_audio: boolean
    audio_url: string
    answer_json: string[]
    answered_num: number
    analysis:string
    newAnswerJson: {
        type: 0|1|2
        value: string
    }[]
    newQuestion: {
        type: 0|1|2
        value:string
    }[]
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
    isRightList: string[]
}

export interface CategoryList {
    [unitNum: string]: {
        [lessonNum: string]: DictType[]
    }
}

export interface WordType {
    id: number
    kana: string
    tone: string
    kanji: string
    desc: string
    lesson: number
    idx: number
    pos: string
    audio: string

    study: number
    studied: number
    studied_status: number
    studied_time: number
}

export interface WordGroup {
    left: {
        id: number
        label: string
        audio: string
    }[]
    right: {
        id: number
        label: string
    }[]
}

