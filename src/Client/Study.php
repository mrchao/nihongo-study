<?php

namespace Devine\NihongoStudy\Client;


use Devine\NihongoStudySdk\Helps\DictHelp;
use Devine\NihongoStudySdk\Vars\StudyVars;
use MysqliDb;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class Study
{
    private MysqliDb $db;

    public function __construct(MysqliDb $db)
    {
        $this->db = $db;
    }

    public function getByLesson(ServerRequest $request, Response $response)
    {
        $token = $request->getParam("token","");
        $lesson = $request->getParam("lesson", 0);
        if(empty($lesson)) {
            return $response->withJson([]);
        }

        $wordList = $this->db->where("lesson", $lesson)
            ->get("word");

        $userId = $this->db->where("device_id", $token)->getValue("user", 'id');
        $uWordList = $this->db
            ->where("user_id", $userId)
            ->where("lesson", $lesson)
            ->get("word_study");

        $uWordMap = [];
        if(!empty($uWordList)) {
            $uWordMap = array_column($uWordList, null,"word_id");
        }

        $newWordList = [];
        foreach($wordList as &$item) {
            $item['audio'] = DictHelp::getAudioUrl($item['lesson'], $item['idx']);
            if(array_key_exists($item['id'], $uWordMap)) {
                // var_dump($uWordMap[$item['id']]);
                $uWord = $uWordMap[$item['id']];
                if ($uWord['study_status'] === StudyVars::IGNORE) {
                    continue;
                }
                if($uWord['study_status'] === StudyVars::FORGET) {
                    $now = time();
                    $lastTime = $uWord['studied_time'] + StudyVars::FORGET_TIME;
                    if($now < $lastTime) {
                        continue;
                    }
                }
                if($uWord['study_status'] === StudyVars::BLURRY) {
                    $now = time();
                    $lastTime = $uWord['studied_time'] + StudyVars::BLURRY_TIME;
                    if($now < $lastTime) {
                        continue;
                    }
                }
                if($uWord['study_status'] === StudyVars::REMEBER) {
                    $now = time();
                    $lastTime = $uWord['studied_time'] + StudyVars::REMEBER_TIME;
                    if($now < $lastTime) {
                        continue;
                    }
                }
            }
            $newWordList[] = $item;
        }

        return $response->withJson($newWordList);
    }

    public function postStatus(ServerRequest $request, Response $response)
    {
        $token = $request->getParam("token","");
        $wordId = $request->getParam("wordId", 0);
        $status = $request->getParam("status", -1);

        if(empty($token) || $status === -1) {
            return $response->withJson(false);
        }

        $userId = $this->db->where("device_id", $token)->getValue("user", 'id');

        $word = $this->db
            ->where("id", $wordId)
            ->getOne("word");

        $uWord = $this->db
            ->where("user_id", $userId)
            ->where("word_id", $wordId)
            ->getOne("word_study");
        if(empty($uWord)) {
            $result = $this->db
                ->insert("word_study", [
                    'user_id' => $userId,
                    'word_id' => $wordId,
                    'studied' => 1,
                    'studied_time' => time(),
                    'study_status' => $status,
                    'lesson' => $word['lesson']
                ]);
        }else{
            $result = $this->db
                ->where("user_id", $userId)
                ->where("word_id", $wordId)
                ->update("word_study", [
                    'studied' => 1,
                    'studied_time' => time(),
                    'study_status' => $status
                ]);

        }

        return $response->withJson($result);
    }

}