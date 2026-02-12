<?php

ini_set("display_errors", 0);
error_reporting(E_ALL);

set_exception_handler(function (Throwable $e) {

    error_log($e);

    http_response_code(500);

    require APP_ROOT . "/views/500.html";

});