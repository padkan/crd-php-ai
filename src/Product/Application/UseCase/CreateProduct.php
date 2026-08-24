<?php

declare(strict_types=1);

namespace App\Product\Application\UseCase;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepositoryInterface;
use App\Product\Domain\ValueObject\Money;

final readonly class CreateProduct
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {
    }

    public function execute(
        string $name,
        string $description,
        int $price,
        string $currency,
    ): Product {
        $money = new Money(
            amount: $price,
            currency: $currency,
        );

        $product = new Product(
            id: null,
            name: $name,
            description: $description,
            price: $money,
        );

        return $this->repository->save($product);
    }
}