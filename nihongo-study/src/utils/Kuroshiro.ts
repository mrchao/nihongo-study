import KuromojiAnalyzer from "kuroshiro-analyzer-kuromoji";
import Kuroshiro from "kuroshiro";

export class Kanji {

  private static instance: Kanji;
  private static DIC_URL = "https://takuyaa.github.io/kuromoji.js/demo/kuromoji/dict/";
  private readonly kuroshiro: Kuroshiro
  public static utils = Kuroshiro.Util

  private constructor() {
    const analyzer = new KuromojiAnalyzer({
      dictPath: Kanji.DIC_URL
    });
    this.kuroshiro = new Kuroshiro()
    this.kuroshiro.init(analyzer).then();
  }

  static getInstance()
  {
    if(!Kanji.instance) {
      Kanji.instance = new Kanji()
      return Kanji.instance
    }
    return Kanji.instance
  }

  getKuroshiro() {
    return this.kuroshiro
  }

  tokenize(value:string) {
    return this.kuroshiro._analyzer._analyzer.tokenize(value)
  }

}