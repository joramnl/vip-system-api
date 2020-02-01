<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use VIPSystem\App;

require '../vendor/autoload.php';

$system = new App;

$app = $system->getSlimApp();

$app->get('/', function (Request $request, Response $response, array $args) {

    $data = $this->db->select('account', ['id', 'name']);

    return $response->write(json_encode($data));

});

try {
    $app->run();
} catch (Throwable $e) {
    error_log($e);
}