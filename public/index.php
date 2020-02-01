<?php

error_reporting(0);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use VIPSystem\App;
use VIPSystem\Middleware\CorsMiddleware;

require '../vendor/autoload.php';

$system = new App;

$app = $system->getSlimApp();

$app->add(new CorsMiddleware);

$app->get('/users', function (Request $request, Response $response, array $args) {


    $data = $this->db->select('user', '*');

    $json = [
        "success" => true,
        "results" => $data
    ];

    return $response->write(json_encode($json));

});

$app->get('/users/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

    $data = $this->db->get('user', '*', [
        "user_id" => $args['id']
    ]);

    if (count($data) > 0) {
        // SELECT package_name FROM package LEFT JOIN user_package ON user_package.package_id = package.package_id WHERE user_package.user_id = 1
        $data['packages'] = $packages = $this->db->select(
        // Table name
            'package',

            // Join
            [
                // The row package_id from packages is equal to package_id of user_package
                "[>]user_package" => "package_id"
            ],

            // Columns
            [
                "package.package_id",
                "package.package_name"
            ],

            // WHERE
            [
                "user_package.user_id" => $args['id']
            ]
        );

        $json = [
            "success" => true,
            "results" => $data
        ];
    } else {
        $json = [
            "success" => false,
            "error" => "User not found"
        ];
    }

    return $response->write(json_encode($json));

});

$app->get('/packages', function (Request $request, Response $response, array $args) {

    $data = $this->db->select('package', '*');

    $json = [
        "success" => true,
        "results" => $data
    ];

    return $response->write(json_encode($json));

});

$app->get('/packages/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

    $data = $this->db->select('package', '*', [
        "package_id" => $args['id']
    ]);

    if (count($data) > 0) {
        $json = [
            "success" => true,
            "results" => $data
        ];
    } else {
        $json = [
            "success" => false,
            "error" => "Package not found"
        ];
    }

    return $response->write(json_encode($json));

});

try {
    $app->run();
} catch (Throwable $e) {
    error_log($e);
}