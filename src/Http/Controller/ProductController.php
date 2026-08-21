<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Product\Application\UseCase\CreateProduct;
use App\Product\Application\UseCase\GetProduct;
use App\Product\Application\UseCase\ListProducts;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class ProductController
{
    public function __construct(
        private ListProducts $listProducts,
        private GetProduct $getProduct,
        private CreateProduct $createProduct,
    ) {
    }

    public function index(): JsonResponse
    {
        $products = $this->listProducts->execute();

        $data = array_map(
            static fn ($product): array => [
                'id' => $product->id(),
                'name' => $product->name(),
                'price' => $product->price()->amount(),
                'currency' => $product->price()->currency(),
            ],
            $products,
        );

        return new JsonResponse($data);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->getProduct->execute($id);

        return new JsonResponse([
            'id' => $product->id(),
            'name' => $product->name(),
            'price' => $product->price()->amount(),
            'currency' => $product->price()->currency(),
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $product = $this->createProduct->execute(
            name: (string) $data['name'],
            price: (int) $data['price'],
            currency: (string) $data['currency'],
        );

        return new JsonResponse(
            [
                'id' => $product->id(),
                'name' => $product->name(),
                'price' => $product->price()->amount(),
                'currency' => $product->price()->currency(),
            ],
            201,
        );
    }
}