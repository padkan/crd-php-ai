<?php

declare(strict_types=1);

namespace App\Product\Application\UseCase;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepositoryInterface;
use App\Product\Domain\ValueObject\Money;
use RuntimeException;

final readonly class UpdateProduct
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {
    }

    public function execute(
        int $id,
        string $name,
        string $description,
        int $price,
        string $currency,
    ): Product {
        $product = $this->repository->findById($id);

        if ($product === null) {
            throw new RuntimeException(
                'Product not found.'
            );
        }

        $product->rename($name);
        $product->changeDescription($description);

        $product->changePrice(
            new Money(
                amount: $price,
                currency: $currency,
            )
        );

        return $this->repository->save($product);
    }
}