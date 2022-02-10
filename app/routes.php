<?php

declare(strict_types=1);

use App\Middlewares\UserHasValidJwtMiddleware;
use App\Controllers\AuthController;
use App\Controllers\StockController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app
        ->group('/auth', function (RouteCollectorProxy $group) {
            $group->post('/login', AuthController::class . ':login');
            $group->post('/register', AuthController::class . ':register');
        });

    $app
        ->group('/app', function (RouteCollectorProxy $group) {
            $group->get('/stocks', StockController::class . ':getStock');
            $group->get('/history', StockController::class . ':getStockHistory');
        })
        ->add(new UserHasValidJwtMiddleware());
};
