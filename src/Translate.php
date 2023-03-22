<?php

namespace Devine\NihongoStudy;

use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Stichoza\GoogleTranslate\GoogleTranslate;

class Translate
{
    public function __invoke(ServerRequest $request, Response $response)
    {
        $hiragana = $request->getParam("hiragana", "");

        $result = "";
        if(!empty($hiragana)) {
            $result = GoogleTranslate::trans($hiragana, "zh", "ja");
        }

        return $response->withJson([
            "hiragana" => $hiragana,
            "translate" => $result
        ]);
    }
}