<?php

namespace Devine\NihongoStudy;

use Devine\NihongoStudySdk\Utils\StrUtil;
use MysqliDb;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class Client
{
    private MysqliDb $db;
    public function __construct(MysqliDb $db)
    {
        $this->db = $db;
    }

    public function startWordCheck(ServerRequest $request, Response $response)
    {
        $lesson = $request->getParam("lesson_num", []);
        $lesson = array_filter($lesson);
        if(!empty($lesson) && count($lesson) != 6 ) {
            $this->db->where("lesson_num", $lesson, "IN");
        }

        $level = $request->getParam("level_num", []);
        $level = array_filter($level);
        if(!empty($level) && count($level) != 24) {
            $this->db->where("level_num", $level, "IN");
        }
        $wordList = $this->db->objectBuilder()->get("lesson_word");
        foreach($wordList as &$word) {
            $word->kana_json = json_decode($word->kana_json);
        }
        // $querySql = $this->db->getLastQuery();
        return $response->withJson($wordList);
    }

    const SENSE = ['SSS', 'SS', 'S', 'A+', 'A', 'A-', 'B+', 'B', 'B-', 'C', 'D', 'E', 'F'];
    public function submitAnswered(ServerRequest $request, Response $response)
    {
        $token = $request->getHeaderLine("token");
        $userId = $this->db
            ->where("device_id", $token)
            ->getValue("user", 'id',1);
        if(empty($userId)) {
            return $response->withStatus(400, "没有登陆");
        }

        $answeredList = $request->getParam("answeredList", []);
        if(empty($answeredList)) {
            return $response->withStatus(400, "没有答题列表");
        }

        $answerResult = $request->getParam("answerResult", []);
        if(empty($answerResult)) {
            return $response->withStatus(400, "没有答题报告");
        }

        $result = $this->db->insert("report_word", [
            'day_ts' => time(),
            'amount' => $answerResult['amount'],
            'spend_time' => $answerResult['spendTime'],
            'report_json' => json_encode($answeredList),
            'score_num' => $answerResult['scoreNum'],
            'sense' => $answerResult['senseNum'],
            'user_id' => $userId,
            'date_ts' => strtotime(date("Y-m-d"), time()),
            'sec_in10s_num' => $answerResult['secIn10sNum'],
            'sec_out15s_num' => $answerResult['secOut15sNum'],
            'right_num' => $answerResult['rightNum'],
            'error_num' => $answerResult['errorNum']
        ]);

        return $response->withJson($result);
    }

    public function historyList(ServerRequest $request, Response $response)
    {
        $token = $request->getHeaderLine("token");
        $userId = $this->db
            ->where("device_id", $token)
            ->getValue("user", 'id',1);
        if(empty($userId)) {
            return $response->withStatus(400, "没有登陆");
        }


        $list = $this->db
            ->where("user_id", $userId)
            ->where("date_ts", strtotime("last month"), '>')
            ->get("report_word", 20);

        // $newList = [];
        array_multisort(array_column($list, 'day_ts'), SORT_DESC, $list);

        $newGroup = [];
        foreach($list as $item) {
            if(!isset($newGroup[$item['date_ts']])) {
                $dateStr = date("Y-m-d", $item['date_ts']);
                $newGroup[$item['date_ts']] = [
                    'label' => $dateStr,
                    'value' => []
                ];
            }
            $reportJson = json_decode($item['report_json']);
            array_multisort(array_column($reportJson, 'useTimeMs'), SORT_DESC, $reportJson);
            $item['report_json'] = $reportJson;
            $item['sense_str'] = self::SENSE[$item['sense']];

            $item['time_ts'] = date("H:i:s", $item['day_ts']);

            $dateTs = $item['date_ts'];
            $item = StrUtil::toCamel($item);

            $newGroup[$dateTs]['value'][] = $item;
        }

        return $response->withJson(array_values($newGroup));
    }

}