<?php

namespace Devine\NihongoStudySdk\Serv;

use Slim\Factory\AppFactory;
use MysqliDb;

class DictServ
{
    private array $kanjiList = [];
    public static bool $mdFile = false;
    public array $mdList = [];
    public array $dbList = [];
    const AUDIO_URI = "http://voice.file.ecomaping.com/";
    const OUT_PATH = WEB_ROOT . "/tmp/";
    private MysqliDb $db;

    public function __construct($path = "")
    {
        if(!empty($path)) {
            $content = file_get_contents($path);
            $this->kanjiList = array_filter(explode("\n", $content));
        }
        $this->db = MysqliDb::getInstance();
    }

    public function parse(): DictServ
    {
        foreach ($this->kanjiList as $kanji) {
            $arr = explode("\t", $kanji);
            // 序号，单词，词性，中文，所属单元/课，声音，标签
            list($num, $dc, $cx, $chinese, $lesson, $sound, $tags) = $arr;

            $num++;
            $newNum = $this->getNum($num);
            $newDc = $this->getDc($dc);
            $newCx = $this->getCx($cx);
            $newChinese = $this->formatChinese($chinese);
            $soundUrl = $this->getSoundUrl($sound);
            $unitStr = $this->getUnitNum($tags);
            $lessonStr = $this->getLessonNum($tags);

            $kanji = $this->getKanji($dc);
            $kana = $this->getKana($dc);
            $htmlCode = $this->rubyToHtml($newDc);

            $this->dbList[] = [
                'sort_num' => (int) $newNum,
                'unit_num' => (int) $unitStr,
                'lesson_num' => (int) $lessonStr,
                // 'source' => $dc,
                'kanji' => $kanji,
                'kana' => $kana,
                'ruby_code' => $newDc,
                'html_code' => $htmlCode,
                'translate_cn' => $newChinese,
                'attr' => $newCx,
                'sound_url' => $soundUrl
            ];

            if(self::$mdFile) {
                $newCx = $this->getCx($cx, '_');
                $this->mdList[] = [
                    "序号：$newNum",
                    "",
                    "![sound]({$soundUrl})",
                    "## 仮　名：{{{$newDc}}}",
                    "## 中国語：{{{$newChinese}}}",
                    "---",
                    "#初级 #单元-{$unitStr} #课-{$lessonStr} #属[{$newCx}]",
                    "---",
                    "$unitStr-$lessonStr-$newNum"
                ];
            }
        }
        return $this;
    }

    public function saveToDb()
    {
        foreach($this->dbList as $item) {
            $sortNum = $item['sort_num'];
            $row = $this->db
                ->where("sort_num", $sortNum)
                ->getOne("dict");
            if(empty($row)) {
                $this->db->insert("dict", $item);
            }else{
                $this->db
                    ->where("sort_num", $sortNum)
                    ->update("dict", $item);
            }
        }
    }

    public function saveToFile($path = "")
    {
        if(empty($path)) {
            $path = self::OUT_PATH;
        }
        $dirName = $this->randomStr(6);
        $path = $path . $dirName;

        if(!is_dir($path)) {
            mkdir($path);
        }

        foreach($this->mdList as $md) {
            $fileName = end($md);
            $fileContent = implode("\n", $md);
            file_put_contents($path . "/{$fileName}.md", $fileContent);
        }
    }

    /**
     * 获取序号
     * @param $num
     * @param int $length
     * @return string
     */
    public function getNum($num, int $length = 4): string
    {
        return str_pad($num,$length,'0',STR_PAD_LEFT);
    }

    /**
     * @param $str
     * @return array|string|string[]|null
     */
    public function getDc($str)
    {
        $newStr = preg_replace("/(\p{Han}+)(\[)/ui", '{$1}$2', $str);
        return str_replace(["[", "]"], ["(", ")"], $newStr);
    }

    public function getKanji($str)
    {
        $pattern = "/\[([^\]]+)\]/ui";
        $newStr = preg_replace($pattern, "", $str);
        return str_replace(" ","", $newStr);
    }

    public function getKana($str)
    {
        $newStr = preg_replace("/(\p{Han}+)/ui", '', $str);
        return str_replace(["[","]", " "], "", $newStr);
    }

    /**
     * @param $str
     * @return string
     */
    public function getCx($str): string
    {
        $str = str_replace(['[', ']'], "", $str);
        return empty($str) ? "寒暄" : trim(mb_convert_kana($str,'n','UTF-8'));
    }

    /**
     * @param $str
     * @param string $uri
     * @return string
     */
    public function getSoundUrl($str, string $uri = ""): string
    {
        if(empty($uri)) {
            $uri = self::AUDIO_URI;
        }
        $sound = str_replace(['[',']'], "", $str);
        $soundArr = explode(":", $sound);
        list($name, $url) = $soundArr;
        return $uri . $url;
    }

    /**
     * @param $tags
     * @return string
     */
    public function getUnitNum($tags): string
    {
        preg_match("/(\d+)+单元/i", $tags, $arr);
        list($all, $first) = $arr;
        return str_pad($first, 2,"0", STR_PAD_LEFT);
    }

    public function getLessonNum($tags): string
    {
        preg_match("/(\d+)+课/i", $tags, $arr);
        list($all, $first) = $arr;
        return str_pad($first, 2,"0", STR_PAD_LEFT);
    }

    public function formatChinese($str, $sign = "")
    {
        return str_replace(['(',')'], ["「{$sign}","{$sign}」"], $str);
    }

    /**
     * @param $str
     * @return string
     */
    public function rubyToHtml($str)
    {
        // {先生}(せんせい)
        // <ruby>先生<rp>(</rp><rt>せんせい</rt><rp>)</rp></ruby>
        $arr = explode(" ", $str);
        foreach($arr as $idx => $value) {
            $newValue = str_replace(['(',')'], ["<rp>(</rp><rt>","</rt><rp>)</rp>"], $value);
            $newValue = str_replace(['{','}'], "", $newValue);
            $arr[$idx] = "<ruby>{$newValue}</ruby>"; // str_replace(['{','}'], ["<ruby>","</ruby>"], $newValue);
        }
        return implode("", $arr);
    }

    //生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
    function randomStr($length): string
    {
        $arr = array_merge(range('a', 'z'));
        $str = '';
        $arrLen = count($arr);
        for ($i = 0; $i < $length; $i++)
        {
            $rand = mt_rand(0, $arrLen - 1);
            $str.= $arr[$rand];
        }
        return $str;
    }

}