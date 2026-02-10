<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use HttpSoft\Emitter\SapiEmitter;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Framework\Template\RendererInterface;
use Framework\Template\PlatesRenderer;

use App\Controllers\HomeController;
use App\Controllers\StrooiController;

require dirname(__DIR__) . "/vendor/autoload.php";

ini_set("display_errors", 1);

// =========================
// 1. Request
// =========================
$request = ServerRequest::fromGlobals();

// =========================
// 2. Container setup
// =========================
$builder = new DI\ContainerBuilder;

$builder->addDefinitions([
    ResponseFactoryInterface::class => DI\create(\Nyholm\Psr7\Factory\Psr17Factory::class),
    RendererInterface::class => DI\create(PlatesRenderer::class)
]);

$builder->useAttributes(true);

$container = $builder->build();

// =========================
// 3. Router + strategy
// =========================
$router = new Router();

$strategy = new ApplicationStrategy();
$strategy->setContainer($container); // belangrijk voor DI
$router->setStrategy($strategy);

// =========================
// 4. Routes
// =========================
$router->get("/", [HomeController::class, "index"]);
$router->get("/strooiwagens", [StrooiController::class, "index"]);

// =========================
// 5. Dispatch
// =========================
$response = $router->dispatch($request);

// =========================
// 6. Emit response
// =========================
$emitter = new SapiEmitter();
$emitter->emit($response);
