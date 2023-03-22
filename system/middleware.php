<?php


use Slim\App;
use Dotenv\Dotenv;
//use Illuminate\Database\Capsule\Manager;
//use Medoo\Medoo;
// use App\Middle\ErrorMiddle;

return function(App $app) {

    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    $ci = $app->getContainer();

    // 加载环境变量
    $ci->get(Dotenv::class)->load();

    // 加载数据库
    //$ci->get(Medoo::class);
    $ci->get(MysqliDb::class);

    // 异常处理中间件
    $e = $app->addErrorMiddleware(true, true, true);
    //$e->setDefaultErrorHandler(ErrorMiddle::class);

};