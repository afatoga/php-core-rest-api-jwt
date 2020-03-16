<?php

require "./vendor/autoload.php";

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\OrderController;

$router = new AltoRouter();

// map homepage
$router->map('GET', '/', function () {
    $homeController = new HomeController;
    $homeController->home();
});

// map user details page
$router->map('GET', '/user/[i:id]/', function ($id) {
    require __DIR__ . '/views/user-details.php';
});

// map login
$router->map('POST', '/af-api/login', function () {
    $authController = new AuthController;
    $authController->login();
});

// map getOrders with count
$router->map('GET', '/af-api/getOrders/*', function () {
    $authController = new AuthController;
    $hasPermission = $authController->hasUserPermission();

    $orderController = new OrderController;
    $homeController = new HomeController;

    if ($hasPermission)
    {   
        $count = (int) $_GET['count'];
        $orderList = $orderController->getOrders($count);
        if ($count > 0 && $orderList)
        {
            $homeController->jsonResponse(201, null, $orderList);
        } else {
            $homeController->jsonResponse(404, 'Not Found');
        }
    } else {
        $homeController->jsonResponse(401, 'Not permitted.');
    }

});

// map createNewOrder
$router->map('POST', '/af-api/createNewOrder', function () {
    $authController = new AuthController;
    $hasPermission = $authController->hasUserPermission();

    $orderController = new OrderController;
    $homeController = new HomeController;

    if ($hasPermission)
    {
        if ($orderController->createNewOrder())
        {
            $homeController->jsonResponse(201, 'New order was created.');
        } else {
            $homeController->jsonResponse(401, 'Mistake.');
        }

    } else {
        $homeController->jsonResponse(401, 'Not permitted.');
    }

});

// match current request url
$match = $router->match();

// call closure or throw 404 status
//var_dump($match);

if (is_array($match) && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    // no route was matched
    header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    //var_dump(1);
}
