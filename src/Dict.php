<?php

namespace Devine\NihongoStudy;

use MysqliDb;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class Dict
{
    private MysqliDb $db;
    public function __construct(MysqliDb $db)
    {
        $this->db = $db;
    }

    public function categoryList(ServerRequest $request, Response $response)
    {
        $all = $this->db
            ->where("is_ignore", 0)
            ->get("dict");
        $unitGroup = [];
        foreach($all as $item) {
            if(!isset($unitGroup[$item['unit_num']])) {
                $unitGroup[$item['unit_num']] = [];
            }
            $unitGroup[$item['unit_num']][] = $item;
        }
        $lessonGroup = [];
        foreach ($unitGroup as $unitNum => $group) {
            foreach($group as $value) {
                if(!isset($lessonGroup[$unitNum][$value['lesson_num']])) {
                    $lessonGroup[$unitNum][$value['lesson_num']] = [];
                }
                $lessonGroup[$unitNum][$value['lesson_num']][] = $value;
            }
        }
        return $response->withJson($lessonGroup);
    }

    public function ignore(ServerRequest $request, Response $response, $id)
    {
        $dict = $this->db->where("id", $id)->getOne("dict");
        if(!empty($dict)) {
            $this->db->where("id", $id)->update("dict", [
                'is_ignore' => true
            ]);
        }
        return $response->withJson($dict);
    }

}