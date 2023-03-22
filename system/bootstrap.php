<?php

use Slim\App;
use DI\ContainerBuilder;

// require_once WEB_ROOT . "/core/utils.php";

$container = new ContainerBuilder();
$container->addDefinitions(WEB_ROOT . '/system/container.php');

try {
    $ci = $container->build();
    $app = $ci->get(App::class);
} catch (Exception $e) {
    die("系统初始化失败");
}

(require WEB_ROOT . '/config/route.php')($app);
(require WEB_ROOT . '/system/middleware.php')($app);

return $app;