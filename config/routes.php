<?php

declare(strict_types=1);

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add(
    'products.index',
    new Route(
        '/api/products',
        [
            '_controller' => 'products.index',
        ],
        methods: ['GET'],
    ),
);

$routes->add(
    'products.show',
    new Route(
        '/api/products/{id}',
        [
            '_controller' => 'products.show',
        ],
        requirements: [
            'id' => '\d+',
        ],
        methods: ['GET'],
    ),
);

$routes->add(
    'products.create',
    new Route(
        '/api/products',
        [
            '_controller' => 'products.create',
        ],
        methods: ['POST'],
    ),
);

return $routes;