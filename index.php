<?php

require "./vendor/autoload.php";

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\OrderController;
use App\Controllers\PrintController;

$router = new AltoRouter();

// map homepage
$router->map('GET', '/', function () {
    $homeController = new HomeController;
    $homeController->home();
});

// map homepage
$router->map('GET', '/print/*', function () {
    $homeController = new HomeController;

    $hashedDocumentId = filter_var($_GET['doc'], FILTER_SANITIZE_STRING);
    $orderId = filter_var($_GET['orderId'], FILTER_VALIDATE_INT);
    $ownerId = filter_var($_GET['ownerId'], FILTER_VALIDATE_INT);

    if ( md5($orderId . 'yPf.FJY~r)[') !== $hashedDocumentId || !$orderId || !$ownerId) return $homeController->jsonResponse(401, 'Not permitted.');

    $printController = new PrintController;
    $printController->getOrderPDF($orderId, $ownerId);
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

// map getOrders with count ..?count=0 (all)
$router->map('GET', '/af-api/[i:ownerId]/get-orders/*', function ($ownerId) {
    $authController = new AuthController;
    $homeController = new HomeController;

    if (!$authController->hasUserPermission()) return $homeController->jsonResponse(401, 'Not permitted.');

    $orderController = new OrderController;

    $count = filter_var($_GET['count'], FILTER_VALIDATE_INT);

    if ($count >= 0) {
        $orderList = $orderController->getOrders($ownerId, (int) $count);

        if ($orderList) {
            $homeController->jsonResponse(200, null, $orderList);
        } else {
            $homeController->jsonResponse(404, 'Not Found.');
        }
    } else {
        $homeController->jsonResponse(400, 'Bad request.');
    }
});

// map get order total price
$router->map('GET', '/af-api/[i:ownerId]/get-order-total-price/*', function ($ownerId) {
    $authController = new AuthController;
    $homeController = new HomeController;

    if (!$authController->hasUserPermission($ownerId)) return $homeController->jsonResponse(401, 'Not permitted.');

    $orderController = new OrderController;
    $orderId = filter_var($_GET['orderId'], FILTER_VALIDATE_INT);

    if ($orderId) $totalPrice = $orderController->getOrderTotalPrice($orderId, $ownerId);
    if (is_null($totalPrice)) {
        $homeController->jsonResponse(404, 'Order not found.');
    } else {
        $homeController->jsonResponse(200, null, ['totalPrice' => $totalPrice]);
    }
});

// map createNewOrder
$router->map('POST', '/af-api/create-new-order', function () {
    $authController = new AuthController;
    $homeController = new HomeController;

    if (!$authController->hasUserPermission()) return $homeController->jsonResponse(401, 'Not permitted.');

    $orderController = new OrderController;

    if ($orderController->createNewOrder()) {
        $homeController->jsonResponse(201, 'New order was created.');
    } else {
        $homeController->jsonResponse(400, 'Mistake.');
    }
});

$router->map('POST', '/af-api/update-order', function () {
    $authController = new AuthController;
    $homeController = new HomeController;

    if (!$authController->hasUserPermission()) return $homeController->jsonResponse(401, 'Not permitted.');

    $orderController = new OrderController;

    if ($orderController->updateOrder()) {
        $homeController->jsonResponse(200, 'Order was updated.');
    } else {
        $homeController->jsonResponse(400, 'Mistake.');
    }
});

$router->map('POST', '/af-api/insert-new-order-item', function () {
    $authController = new AuthController;
    $homeController = new HomeController;

    if (!$authController->hasUserPermission()) return $homeController->jsonResponse(401, 'Not permitted.');

    $orderController = new OrderController;

    if ($orderController->insertNewOrderItem()) {
        $homeController->jsonResponse(201, 'New item was created.');
    } else {
        $homeController->jsonResponse(401, 'Mistake.');
    }
});

$router->map('POST', '/af-api/update-order-item', function () {
    $authController = new AuthController;
    $homeController = new HomeController;

    if (!$authController->hasUserPermission()) return $homeController->jsonResponse(401, 'Not permitted.');

    $orderController = new OrderController;

    if ($orderController->updateOrderItem()) {
        $homeController->jsonResponse(200, 'Order item was updated.');
    } else {
        $homeController->jsonResponse(400, 'Mistake.');
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
    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    echo '1';
}
