<?php

use Slim\App;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Devine\NihongoStudy\Translate;
use Devine\NihongoStudy\Admin;
use Devine\NihongoStudy\Client;
use Devine\NihongoStudy\LoadHtml;
use Devine\NihongoStudy\Topic;
use Devine\NihongoStudy\Mochi;
use Devine\NihongoStudy\MochiToDict;
use Devine\NihongoStudy\ClientLogin;
use Devine\NihongoStudy\Dict;
use Devine\NihongoStudy\Word;

return function(App $app) {

    // 设置路由参数
    $app
        ->getRouteCollector()
        ->setDefaultInvocationStrategy(new RequestResponseArgs());

    $app->get("/", function (ServerRequest $request, Response $response) {
        return $response->write("success");
    });
    $app->post("/login", ClientLogin::class);
    $app->get("/categoryList", [Dict::class, "categoryList"]);
    $app->get("/historyList", [Client::class, "historyList"]);
    $app->put("/dict/{id:[0-9]+}/ignore", [Dict::class, "ignore"]);

    $app->get("/load", LoadHtml::class);

    $app->get("/translate", Translate::class);
    $app->map(['POST', 'GET'],"/admin/kanjiSubmit", [Admin::class, 'kanjiSubmit']);
    $app->get("/admin/getKanjiList", [Admin::class, 'getKanjiList']);

    $app->get("/startWordCheck", [Client::class, "startWordCheck"]);
    $app->post("/submitAnswered", [Client::class, "submitAnswered"]);


    $app->get("/topic/menuList", [Topic::class, "menuList"]);
    $app->get("/topic/fetchByTopicGroup", [Topic::class, "fetchByTopicGroup"]);

    $app->get("/mochi", Mochi::class);
    $app->get("/mochiToDict", MochiToDict::class);

    $app->get("/admin/fetchBySortNum", [Dict::class, "getOneBySortNum"]);
    $app->get("/admin/fetchByKanjiEqKana", [Dict::class, "getKanjiEqKanaList"]);
    $app->post("/admin/syncKanjiList", [Dict::class, "syncKanjiList"]);

    $app->get("/test", \Devine\NihongoStudy\Test::class);

    $app->get("/script/word", \Devine\NihongoStudy\ScriptWord::class);
    $app->get("/word/all", [Word::class, "all"]);

    // study
    $app->get("/study/fetchByLesson", [Client\Study::class, "getByLesson"]);


    $app->any("[/{params:.*}]", function(ServerRequest $request, Response $response) {
        return $response->write("404");
    });

};