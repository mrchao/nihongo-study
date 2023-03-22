<?php

// 打开严格类型
declare(strict_types = 1);

// 错误异常
error_reporting(E_ALL);
ini_set("display_errors", "stderr");

// 时区
date_default_timezone_set("PRC");

!defined("TMP_ROOT") && define("TMP_ROOT", WEB_ROOT . "/tmp");