<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use League\Route\Router;
use HttpSoft\Emitter\SapiEmitter;
use GuzzleHttp\Psr7\ServerRequest;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Http\Exception\NotFoundException;

define("APP_ROOT", dirname(__DIR__));

require APP_ROOT . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

$env = $_ENV["APP_ENV"] ?? "prod";

require $env === "dev"
    ? APP_ROOT . "/config/errors_dev.php"
    : APP_ROOT . "/config/errors_prod.php";

$request = ServerRequest::fromGlobals();

$builder = new DI\ContainerBuilder;

$builder->addDefinitions(APP_ROOT . "/config/definitions.php");

$builder->useAttributes(true);

$container = $builder->build();

$router = new Router;

$strategy = new ApplicationStrategy;
$strategy->setContainer($container);
$router->setStrategy($strategy);

$routes = require APP_ROOT . "/config/routes.php";
$routes($router);

try {

    $response = $router->dispatch($request);

} catch (NotFoundException $e) {
    
    http_response_code(404);

    if ($env === "dev") {

        throw $e;

    } else {

        require APP_ROOT . "/views/404.html";
        exit;

    }
}

$emitter = new SapiEmitter;

$emitter->emit($response);