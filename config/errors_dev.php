<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

set_exception_handler(function (Throwable $e) {

    http_response_code(500);

    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();

    throw $e;
});