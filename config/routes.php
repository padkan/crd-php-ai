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

$routes->add(
    'products.update',
    new Route(
        '/api/products/{id}',
        [
            '_controller' => 'products.update',
        ],
        requirements: [
            'id' => '\d+',
        ],
        methods: ['PUT'],
    ),
);

$routes->add(
    'products.delete',
    new Route(
        '/api/products/{id}',
        [
            '_controller' => 'products.delete',
        ],
        requirements: [
            'id' => '\d+',
        ],
        methods: ['DELETE'],
    ),
);

return $routes;