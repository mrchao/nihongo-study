<?php

namespace Devine\NihongoStudy;

use MysqliDb;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;
use PHPHtmlParser\StaticDom;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class LoadHtml
{
    // 选择
    const LEVEL = "L3";

    private MysqliDb $db;
    private array $LEVEL = ['L1', 'L2', 'L3', 'L4', 'L5', 'L6'];
    const PATH_SOURCE = TMP_ROOT . '/topic/' . self::LEVEL . '/source';
    const PATH_TARGET = TMP_ROOT . '/topic/' . self::LEVEL . '/target';
    const FILTER_FILENAME_ARR = ['.','..', '.DS_Store'];

    const DOM_ROOT_CLASSNAME = '.analysis_block';

    public function __construct(MysqliDb $db)
    {
        $this->db = $db;
    }

    /**
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     * @throws CircularException
     * @throws StrictException
     * @throws LogicalException
     * @throws \Exception
     */
    public function __invoke(ServerRequest $request, Response $response)
    {
        $fileList = [];
        $dir = dir(self::PATH_SOURCE);
        while (($file = $dir->read()) !== false) {
            if(is_file(self::PATH_SOURCE . '/' . $file)) {
                $fileList[] = $file;
            }
        }
        $fileList = array_filter($fileList, function($file) {
            return !in_array($file, self::FILTER_FILENAME_ARR);
        });
        sort($fileList);

        $ret = [];
        foreach($fileList as $idx => $file) {
            // var_dump($idx);
            $dom = StaticDom::loadFromFile(self::PATH_SOURCE . '/' . $file);
            $list = $dom->find(self::DOM_ROOT_CLASSNAME);

            $fileRet = [];
            foreach ($list as $item) {
                $id = $item->getAttribute('id');

                $titleDom = $item->find('.analysis_title');
                $title = "";
                if(count($titleDom) === 1) {
                    $title = trim(strip_tags($titleDom->outerHtml));
                }

                $questionDom = $item->find(".analysis_question");
                $topicNum = $questionDom->find("p.qst_num")->text();

                // 处理问题内容
                $question = [];
                $isAudio = false;
                $hasImage = false;
                $audioUrl = "";
                $imageUrl = "";
                $qstContentList = $questionDom->find(".qst_content")->find(".content_box");
                foreach($qstContentList as $qstItem) {
                    $textDom = $qstItem->find(".content_box_title");
                    if(count($textDom) === 1) {
                        $question[] = $textDom->innerHtml;
                    }
                    $audioDom = $qstItem->find('audio');
                    if (count($audioDom) === 1) {
                        $isAudio = true;
                        $audioUrl = $audioDom[0]->getAttribute("src");
                        $question[] = $audioUrl;
                        $audioDom[0]->setAttribute('controls', 'controls');
                        $item->find('.player_console')->delete();
                        $item->find('.el-loading-mask')->delete();
                    }
                    $imageDom = $qstItem->find("img");
                    if ( count($imageDom) === 1) {
                        $hasImage = true;
                        $imageUrl = $imageDom[0]->getAttribute("src");
                        $question[] = $imageUrl;
                    }
                }

                // 正确答案
                $answered = $item
                    ->find(".is_right")
                    ->find("p.ans_index")
                    ->text();

                // 选择错误
                $errorAnswered = $item->find('.no_right');

                $answeredArr = $item->find(".analysis_answer");
                $answeredNum = count($answeredArr);
                $answeredList = [];
                foreach ($answeredArr as $answeredItem) {
                    $answeredClass = $answeredItem->getAttribute("class");

                    if(! empty($answeredClass)) {
                        $answeredClassArr = explode(" ", $answeredClass);
                        if(
                            array_search("is_right", $answeredClassArr)
                            && array_search("no_right", $answeredClassArr)
                        ) {
                            $answeredClass = str_replace("is_right", "", $answeredClass);
                            $answeredClass = str_replace("no_right", "", $answeredClass);
                            $answeredItem->setAttribute("class", $answeredClass);
                        }
                        if(array_search("is_right", $answeredClassArr)) {
                            $answeredClass = str_replace("is_right", "", $answeredClass);
                            $answeredItem->setAttribute("class", $answeredClass);
                        }
                        if(array_search("no_right", $answeredClassArr)) {
                            $answeredClass = str_replace("no_right", "", $answeredClass);
                            $answeredItem->setAttribute("class", $answeredClass);
                        }
                    }

                    // 文字 / 图片
                    $textDom = $answeredItem->find(".ans_content_text");
                    if(count($textDom) === 1) {
                        $answeredList[] = $textDom->text();
                        continue;
                    }
                    $imgDom = $answeredItem->find('img');
                    if(count($imgDom) === 1) {
                        $answeredList[] = $imgDom->getAttribute('src');
                        continue;
                    }
                    $audioDom = $answeredItem->find("audio");
                    if(count($audioDom) === 1) {
                        $answeredList[] = $audioDom->getAttribute('src');
                    }
                }

                // todo: 明天改这里，dom里有一个标签没有找到，需要做空判断
                $analysisDom = $item
                    ->find(".analysis_content")
                    ->find(".content_box");
                $analysis = trim(strip_tags($analysisDom->innerHtml));

                $fileRet[] = [
                    'id' => $id,
                    'number' => $topicNum,
                    'isAudio' => $isAudio,
                    'audioUrl' => $audioUrl,
                    'title' => $title,
                    'question' => $question,
                    'answeredNum' => intval($answeredNum),
                    'answeredList' => $answeredList,
                    'answered' => intval($answered),
                    'analysis' => $analysis
                ];

                // level sort_num part_num number ver lesson_num lesson_num_split
                $fileArr = explode('.', $file);

                if(self::LEVEL === 'L2') {
                    $lessonNum = $fileArr[1];
                    $lessonNumSplit = $fileArr[2];
                    $partNum = $fileArr[3];
                    $sortNum = $fileArr[0];
                }else{
                    $fileArr = explode('-', $fileArr[0]);
                    $lessonNum = $fileArr[0];
                    $lessonNumSplit = 0;
                    $partNum = $fileArr[1];
                    $sortNum = $idx + 1;
                }

                $row = $this->db
                    ->where('level', array_search(self::LEVEL, $this->LEVEL))
                    ->where('lesson_num', $lessonNum)
                    ->where('lesson_num_split', $lessonNumSplit)
                    ->where('part_num', $partNum)

                    ->where('number', $topicNum)
                    ->where('ver', '5.1')
                    ->get("topic");

                if(count($row) === 0) {
                    $this->db->insert("topic", [
                        'number'     => $topicNum,
                        'is_audio'   => boolval($isAudio),
                        'has_image'  => boolval($hasImage),
                        'audio_url'  => $audioUrl,
                        'image_url'  => $imageUrl,
                        'question'   => json_encode($question),
                        'answer_num' => intval($answeredNum),
                        'answer_json'  => json_encode($answeredList),
                        'answered_num' => intval($answered),
                        'analysis'   => $analysis,
                        'level'      => array_search(self::LEVEL, $this->LEVEL),
                        'sort_num'   => $sortNum,
                        'lesson_num'     => $lessonNum,
                        'lesson_num_split' => $lessonNumSplit,
                        'part_num'  => $partNum,
                        'ver'        => '5.1',
                        'title'     => $title
                    ]);
                }else{
                    $this->db
                        ->where('level', array_search(self::LEVEL, $this->LEVEL))
                        ->where('lesson_num', $lessonNum)
                        ->where('lesson_num_split', $lessonNumSplit)
                        ->where('part_num', $partNum)
                        ->where('number', $topicNum)
                        ->where('ver', '5.1')
                        ->update("topic", [
                            'number'     => $topicNum,
                            'is_audio'   => boolval($isAudio),
                            'has_image'  => boolval($hasImage),
                            'audio_url'  => $audioUrl,
                            'image_url'  => $imageUrl,
                            'question'   => json_encode($question),
                            'answer_num' => intval($answeredNum),
                            'answer_json'  => json_encode($answeredList),
                            'answered_num' => intval($answered),
                            'analysis'   => $analysis,
                            'level'      => array_search(self::LEVEL, $this->LEVEL),
                            'sort_num'   => $sortNum,
                            'lesson_num'     => $lessonNum,
                            'lesson_num_split' => $lessonNumSplit,
                            'part_num'  => $partNum,
                            'ver'        => '5.1',
                            'title'     => $title
                    ]);
                }

            }
            $ret[] = $fileRet;

            $html = <<<EOT
<html lang="zh">
<head>
    <meta charset="UTF-8"/>
    <link type="text/css" rel="stylesheet" href="main.css"/>
</head>
<body>
EOT;
            $str = $html . $dom . "</body></html>";
            $subLesson = "";

            $fileArr = explode('.', $file);
            if(self::LEVEL === 'L2') {
                $lessonNum = $fileArr[1];
                $partNum = $fileArr[3];
                switch ($fileArr[2]) {
                    case "1":
                        $subLesson = "上";
                        break;
                    case "2":
                        $subLesson = "下";
                        break;
                    default:
                }
            }else{
                $fileArr = explode('-', $fileArr[0]);
                $lessonNum = $fileArr[0];
                $partNum = $fileArr[1];
            }
            //var_dump($fileArr);exit;
            $newFileName = self::LEVEL . ".第{$lessonNum}课{$subLesson}.第{$partNum}关.html";
            file_put_contents(self::PATH_TARGET . '/' . $newFileName, $str);
        }
        return $response->withJson($ret);
    }
}