<?php

error_reporting(0);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

use VIPSystem\App;

require '../vendor/autoload.php';

$system = new App;

$app = $system->getSlimApp();

try {
    $app->run();
} catch (Throwable $e) {
    error_log($e);
}