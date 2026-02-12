<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use HttpSoft\Emitter\SapiEmitter;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Dotenv\Dotenv;

ini_set("display_errors", 1);

define("APP_ROOT", dirname(__DIR__));

require APP_ROOT . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

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

$response = $router->dispatch($request);

$emitter = new SapiEmitter;

$emitter->emit($response);