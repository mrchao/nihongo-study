<?php

namespace Devine\NihongoStudy;

use MysqliDb;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class Admin
{
    private MysqliDb $db;

    public function __construct(MysqliDb $db)
    {
        $this->db = $db;
    }

    public function getKanjiList(ServerRequest $request, Response $response)
    {
        $kanjiList = $this->db->get("lesson_word");
        return $response->withJson($kanjiList);
    }

    /**
     * @throws \Exception
     */
    public function kanjiSubmit(ServerRequest $request, Response $response)
    {
        $id = $request->getParam("id", 0);
        $kanaJson = json_encode($request->getParam("kana_json", []));

        if(!empty($id)) {
            $params = $request->getParams();
            $params['kana_json'] = $kanaJson;
            $kanjiDB = $this->db
                ->where("id", $id)
                ->getOne("lesson_word");
            $kanaColumns = array_keys($kanjiDB);
            $params = array_filter($params, function ($key) use ($kanaColumns) {
              return !array_key_exists($key, $kanaColumns);
            }, ARRAY_FILTER_USE_KEY);
            $kanjiDiff = array_diff($params, $kanjiDB);
            $result = $this->db
                ->where("id", $id)
                ->update("lesson_word", $kanjiDiff);
        }else{
            $kanji = $request->getParam("kanji", "");
            $level = $request->getParam("level_num", 0);
            $lesson = $request->getParam("lesson_num", 0);
            $hiragana = $request->getParam("hiragana", "");
            $translateCn = $request->getParam("translate_cn", "");
            $htmlCode = $request->getParam("html_code", "");
            $result = $this->db->insert("lesson_word", [
                'kanji' => $kanji,
                'hiragana' => $hiragana,
                'html_code' => $htmlCode,
                'translate_cn' => $translateCn,
                'kana_json' => $kanaJson,
                'lesson_num' => $lesson,
                'level_num' => $level
            ]);
        }

        $err = $this->db->getLastError();
        if(!empty($err)) {
            return $response->withJson($err);
        }
        return $response->withJson($result);
    }
}