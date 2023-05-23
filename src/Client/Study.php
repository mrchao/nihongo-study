<?php

namespace Devine\NihongoStudy\Client;


use Devine\NihongoStudySdk\Helps\DictHelp;
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
        $lesson = $request->getParam("lesson", 0);
        if(empty($lesson)) {
            return $response->withJson([]);
        }

        $wordList = $this->db->where("lesson", $lesson)->get("word");

        foreach($wordList as &$item) {
            $item['audio'] = DictHelp::getAudioUrl($item['lesson'], $item['idx']);
        }

        return $response->withJson($wordList);
    }


}