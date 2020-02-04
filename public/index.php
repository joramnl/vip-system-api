<?php

error_reporting(0);

use VIPSystem\App;

require '../vendor/autoload.php';

$system = new App;

$app = $system->getSlimApp();

try {
    $app->run();
} catch (Throwable $e) {
    error_log($e);
}