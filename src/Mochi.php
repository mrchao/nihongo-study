<?php

namespace Devine\NihongoStudy;

use Slim\Http\Response;
use Slim\Http\ServerRequest;

class Mochi
{
    const FILE_PATH = TMP_ROOT . "/word/1.txt";
    const OUT_FILE_PATH = TMP_ROOT . "/mochi_listen";
    const AUDIO_URI = "http://voice.file.ecomaping.com/";

    // number
    // kanji(romaji)
    // sex
    // chinese
    // lesson
    // tags

    /*
     * # <furigana>chinese</furigana>
     * {振り仮名}(ふがな)
     * ![book01-lesson01-00.mp3](@media/gGs2AleC.mp3)
     * book01-lesson01-00.mp3
     * ---
     * #tags
     * ---
     * */

    public function __invoke(ServerRequest $request, Response $response)
    {
        $content = file_get_contents(self::FILE_PATH);
        $lineArr = array_filter(explode("\n", $content));

        $result = [];
        foreach($lineArr as $line) {
            $line = explode("\t", $line);
// var_dump($line);exit;
            list($num, $dc, $cx, $chinese, $lesson, $sound, $tags) = $line;

            $newDc = preg_replace("/(\p{Han}+)(\[)/ui",'{$1}$2', $dc);
            $newDc = str_replace(["[", "]"], ["(",")"], $newDc);

            $cx = str_replace(['[',']'], "", $cx);

            $sound = str_replace(['[',']'], "", $sound);
            $soundArr = explode(":", $sound);

            preg_match("/(\d+)+单元/i", $tags, $unitNum);
            $unitStr = str_pad($unitNum[1], 2,"0", STR_PAD_LEFT);
            preg_match("/(\d+)+课/i", $tags, $lessonNum);
            $lessonStr = str_pad($lessonNum[1], 2,"0", STR_PAD_LEFT);

            if(empty($cx)) {
                $cx = "寒暄";
            }else{
                $cx = trim(str_replace(" ", "", $cx));
            }
            $num++;
            $num = str_pad($num,4,'0',STR_PAD_LEFT);

            $mdFileArr = [
                "序号：$num",
                "",
                "![$soundArr[1]](" . self::AUDIO_URI . '/'. $soundArr[1] . ")",
                "## 仮　名：{{{$newDc}}}",
                "## 中国語：{{{$chinese}}}",
                "---",
                "#初级 #单元-{$unitStr} #课:{$lessonStr} #词性[{$cx}]",
                "---"
            ];


            $result[] = $mdFileArr;
            $fileContent = implode("\n", $mdFileArr);
            file_put_contents(self::OUT_FILE_PATH . "/{$unitStr}-{$lessonStr}-{$num}.md", $fileContent);
        }


        return $response->withJson($result);
    }
}