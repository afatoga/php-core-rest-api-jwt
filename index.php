<?php

require "./vendor/autoload.php";

use App\Controllers\HomeController;
use App\Controllers\AuthController;

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

// map user details page
$router->map('POST', '/v1/api/login', function () {
    $authController = new AuthController;
    $authController->login();
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
