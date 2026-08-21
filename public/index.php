<?php

declare(strict_types=1);

use App\Http\Controller\ProductController;
use App\Product\Application\UseCase\CreateProduct;
use App\Product\Application\UseCase\GetProduct;
use App\Product\Application\UseCase\ListProducts;
use App\Product\Infrastructure\Persistence\PdoProductRepository;
use App\Shared\Infrastructure\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

require dirname(__DIR__) . '/vendor/autoload.php';

$request = Request::createFromGlobals();

$database = new Database(
    host: getenv('DB_HOST') ?: 'mysql',
    port: (int) (getenv('DB_PORT') ?: 3306),
    database: getenv('DB_DATABASE') ?: 'app',
    username: getenv('DB_USERNAME') ?: 'app',
    password: getenv('DB_PASSWORD') ?: 'secret',
);

$pdo = $database->connect();

$productRepository = new PdoProductRepository($pdo);

$listProducts = new ListProducts($productRepository);
$getProduct = new GetProduct($productRepository);
$createProduct = new CreateProduct($productRepository);

$productController = new ProductController(
    listProducts: $listProducts,
    getProduct: $getProduct,
    createProduct: $createProduct,
);

$routes = require dirname(__DIR__) . '/config/routes.php';

$context = new RequestContext();
$context->fromRequest($request);

$matcher = new UrlMatcher(
    $routes,
    $context,
);

try {
    $parameters = $matcher->match(
        $request->getPathInfo(),
    );

    $controller = $parameters['_controller'];

    $response = match ($controller) {
        'products.index' => $productController->index(),

        'products.show' => $productController->show(
            (int) $parameters['id'],
        ),

        'products.create' => $productController->create(
            $request,
        ),

        default => new JsonResponse(
            ['error' => 'Controller not found'],
            500,
        ),
    };
} catch (ResourceNotFoundException) {
    $response = new JsonResponse(
        ['error' => 'Route not found'],
        404,
    );
} catch (InvalidArgumentException $exception) {
    $response = new JsonResponse(
        ['error' => $exception->getMessage()],
        422,
    );
} catch (RuntimeException $exception) {
    $response = new JsonResponse(
        ['error' => $exception->getMessage()],
        404,
    );
} catch (Throwable) {
    $response = new JsonResponse(
        ['error' => 'Internal server error'],
        500,
    );
}

$response->send();