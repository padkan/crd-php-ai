<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Product\Application\UseCase\CreateProduct;
use App\Product\Application\UseCase\DeleteProduct;
use App\Product\Application\UseCase\GetProduct;
use App\Product\Application\UseCase\ListProducts;
use App\Product\Application\UseCase\UpdateProduct;
use App\Product\Domain\Entity\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class ProductController
{
    public function __construct(
        private ListProducts $listProducts,
        private GetProduct $getProduct,
        private CreateProduct $createProduct,
        private UpdateProduct $updateProduct,
        private DeleteProduct $deleteProduct,
    ) {
    }

    public function index(): JsonResponse
    {
        $products = $this->listProducts->execute();

        return new JsonResponse(
            array_map(self::toArray(...), $products),
        );
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->getProduct->execute($id);

        return new JsonResponse(self::toArray($product));
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $product = $this->createProduct->execute(
            name: (string) $data['name'],
            description: (string) ($data['description'] ?? ''),
            price: (int) $data['price'],
            currency: (string) $data['currency'],
        );

        return new JsonResponse(self::toArray($product), 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $product = $this->updateProduct->execute(
            id: $id,
            name: (string) $data['name'],
            description: (string) ($data['description'] ?? ''),
            price: (int) $data['price'],
            currency: (string) $data['currency'],
        );

        return new JsonResponse(self::toArray($product));
    }

    public function delete(int $id): JsonResponse
    {
        $this->deleteProduct->execute($id);

        return new JsonResponse(null, 204);
    }

    private static function toArray(Product $product): array
    {
        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'description' => $product->description(),
            'price' => $product->price()->amount(),
            'currency' => $product->price()->currency(),
        ];
    }
}
