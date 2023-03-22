
// https://github.com/hexenq/kuroshiro-analyzer-kuromoji/pull/7
declare class KuromojiAnalyzer {
    _analyzer: any;
    constructor(dictPath?: { dictPath: string });
    init(): Promise<void>;
    parse(str:string):Promise<any>;
}

declare module "kuroshiro-analyzer-kuromoji" {
    export = KuromojiAnalyzer
}

// https://github.com/hexenq/kuroshiro/pull/93
declare class Kuroshiro {
    _analyzer: KuromojiAnalyzer;
    constructor();
    init(_analyzer: any): Promise<void>;
    convert(
        str: string,
        options?: {
            to?:string;
            mode?: string;
            romajiSystem?:string;
            delimiter_start?: string;
            delimiter_end?: string;
        }
    ): Promise<string>;
    static Util: {
        isHiragana: (ch: string) => boolean;
        isKatakana: (ch: string) => boolean;
        isKana: (ch: string) => boolean;
        isKanji: (ch: string) => boolean;
        isJapanese: (ch: string) => boolean;
        hasHiragana: (str: string) => boolean;
        hasKatakana: (str: string) => boolean;
        hasKana: (str: string) => boolean;
        hasKanji: (str: string) => boolean;
        hasJapanese: (str: string) => boolean;
        kanaToHiragana: (str: string) => string;
        kanaToKatakana: (str: string) => string;
        kanaToRomaji: (
            str: string,
            system: "nippon" | "passport" | "hepburn"
        ) => string;
    }
}

declare module "kuroshiro" {
    export = Kuroshiro
}