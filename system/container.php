<?php

use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\App;
use Dotenv\Dotenv;
// use Illuminate\Database\Capsule\Manager;
// use Medoo\Medoo;
return [
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },

    // 环境配置
    Dotenv::class => function () {
        return Dotenv::createImmutable(WEB_ROOT);
    },

//    Medoo::class => function () {
//        return new Medoo([
//            'type' => 'sqlite',
//            'database' => WEB_ROOT . '/database.db'
//        ]);
//    },

    MysqliDb::class => function () {
        return new MysqliDb([
            'host' => $_ENV['db_host'],
            'username' => $_ENV['db_username'],
            'password' => $_ENV['db_password'],
            'db' => $_ENV['db_name'],
            'charset' => 'utf8mb4'
        ]);
    },

];