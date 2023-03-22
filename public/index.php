<?php

define("WEB_ROOT", dirname(__DIR__));
require WEB_ROOT . '/config/defaults.php';
require WEB_ROOT . '/vendor/autoload.php';
(require WEB_ROOT . '/system/bootstrap.php')->run();