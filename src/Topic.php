<?php

namespace Devine\NihongoStudy;

use MysqliDb;
use Devine\NihongoStudySdk\Vars\TopicVars;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class Topic
{
    private MysqliDb $db;

    public function __construct(MysqliDb $db)
    {
        $this->db = $db;
    }

    public function fetchByTopicGroup(ServerRequest $request, Response $response)
    {
        $level = $request->getParam("level", 0);
        $topicGroup = $request->getParam("topicGroup", "");
        $topicGroupArr = explode("_", $topicGroup);

        $list = $this->db
            ->where('level', $level)
            ->where('lesson_num', $topicGroupArr[0])
            ->where('lesson_num_split', $topicGroupArr[1])
            ->where('part_num', $topicGroupArr[2])
            ->get("topic");
        foreach($list as &$item) {
            $item['answer_json'] = json_decode($item['answer_json']);
            $question = json_decode($item['question']);
            foreach ($question as $idx => $value) {
                if($item['is_audio'] && substr($value, -3) === 'mp3') {
                    unset($question[$idx]);
                }
            }
            $item['question'] = array_values($question);
        }
        return $response->withJson($list);
    }

    public function menuList(ServerRequest $request, Response $response)
    {
        $level = TopicVars::LEVEL;

        $list = $this->db->get("topic", null, [
            'id', 'level', 'lesson_num', 'lesson_num_split', 'part_num'
        ]);

        $menuList = [];
        foreach ($list as $topicItem) {
            if(!isset($menuList[$topicItem['level']])) {
                $menuList[$topicItem['level']] = [
                    'label' => TopicVars::LEVEL[$topicItem['level']],
                    'value' => $topicItem['level'],
                    'children' => []
                ];
            }
            $lessonIndex = $topicItem['lesson_num'] . '_' .
                $topicItem['lesson_num_split'] . '_' .
                $topicItem['part_num'];
            if(!isset($menuList[$topicItem['level']]['children'][$lessonIndex])) {
                $lessonSplitName = TopicVars::LESSON_SPLIT[$topicItem['lesson_num_split']];
                $menuList[$topicItem['level']]['children'][$lessonIndex] = [
                    'label' => '第' . $topicItem['lesson_num'] . '课' . $lessonSplitName . "[" . $topicItem['part_num'] . "]",
                    'value' => $lessonIndex,
                ];
            }
            // $menuList[$topicItem['level']]['children'][$lessonIndex]['children'][] = $topicItem;
        }

        foreach($menuList as $idx => $subList) {
            $subList['children'] = array_values($subList['children']);
            $menuList[$idx] = $subList;
        }
        sort($menuList);
        return $response->withJson($menuList);
    }
}