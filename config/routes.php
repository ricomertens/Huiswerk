<?php

use App\Controllers\HomeController;
use App\Controllers\ProductController;
use League\Route\Router;

return function (Router $router) {

    $router->get("/", [HomeController::class, "index"]);

    $router->get("/products", [ProductController::class, "index"]);

    $router->get("/product/{id:number}", [ProductController::class, "show"]);

    $router->map(["GET", "POST"], "/product/new", [ProductController::class, "create"]);

};