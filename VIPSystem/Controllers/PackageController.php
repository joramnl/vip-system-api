<?php


namespace VIPSystem\Controllers;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PackageController
{
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function list(Request $request, Response $response, $args) {
        $data = $this->container->get('db')->select('package', '*');

        $json = [
            "success" => true,
            "results" => $data
        ];

        return $response->withJson($json);
    }

    public function get(Request $request, Response $response, $args) {
        $data = $this->container->get('db')->select('package', '*', [
            "package_id" => $args['id']
        ]);

        if (count($data) > 0) {
            $json = [
                "success" => true,
                "results" => $data
            ];

            return $response->withJson($json);
        }
        else
        {
            $json = [
                "success" => false,
                "error" => "Package not found"
            ];

            return $response
                ->withStatus(404)
                ->withJson($json);
        }
    }
}