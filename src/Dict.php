<?php

namespace Devine\NihongoStudy;

use Devine\NihongoStudySdk\Utils\StrUtil;
use Dotenv\Util\Str;
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

    public function getOneBySortNum(ServerRequest $request, Response $response)
    {
        $sortNum = $request->getParam("sortNum");
        $row = $this->db->where("sort_num", $sortNum)->getOne("dict");
        return $response->withJson($row);
    }

    public function getKanjiEqKanaList(ServerRequest $request, Response $response)
    {
        $all = $this->db
            ->where("is_ignore", 0)
            ->where("lesson_num", 24, "<=")
            ->get("dict");
        // 542  pian 315 !227  kata 238 !304
        $list = array_filter($all, function($item) {
            return $item['kanji'] == $item['kana'];
        });

        $kanjiList = $this->db->get("dict_kanji_map_kana");
        $newKanjiList = [];
        foreach ($kanjiList as $item) {
            $newKanjiList[$item['sort_num']][] = $item['kanji'];
        }

        foreach($list as $idx => $item) {
            $list[$idx]['isEnglish'] = StrUtil::hasKatakana($item['kanji']);
            $list[$idx]['kanjiArr'] = $newKanjiList[$item['sort_num']] ?? [];
            $list[$idx]['hasKanji'] = boolval($newKanjiList[$item['sort_num']] ?? null);
        }
        return $response->withJson(array_values($list));
    }

    public function syncKanjiList(ServerRequest $request, Response $response)
    {
        $list = $request->getParams();
        foreach($list as $item) {
            $sortNum = $item['sort_num'];
            $kanjiArr = $item['kanjiArr'];
            if(!empty($kanjiArr)) {
                foreach($kanjiArr as $kanji) {
                    $row = $this->db
                        ->where('sort_num', $sortNum)
                        ->where('kanji', $kanji)
                        ->getOne("dict_kanji_map_kana");
                    if(empty($row)) {
                        $this->db->insert("dict_kanji_map_kana", [
                            'sort_num' => $sortNum,
                            'kanji' => $kanji,
                            'kana' => $item['kana']
                        ]);
                    }
                }
            }
        }
        return $response->withJson(true);
    }

    private function getKanjiListBySortNum()
    {
        $kanjiList = $this->db->get("dict_kanji_map_kana");
        $newKanjiList = [];
        foreach ($kanjiList as $item) {
            $newKanjiList[$item['sort_num']][] = $item['kanji'];
        }
        return $newKanjiList;
    }

    public function categoryList(ServerRequest $request, Response $response)
    {

        $kanjiList = $this->getKanjiListBySortNum();

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

                // 合并判断正确的数据集合
                $value['isRightList'] = array_values(array_unique(array_merge(
                    [$value['kanji'], $value['kana']],
                    $kanjiList[$value['sort_num']] ?? []
                )));

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