<?php

declare(strict_types=1);

namespace App\Product\Application\UseCase;

use App\Product\Domain\Repository\ProductRepositoryInterface;
use RuntimeException;

final readonly class DeleteProduct
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {
    }

    public function execute(int $id): void
    {
        $product = $this->repository->findById($id);

        if ($product === null) {
            throw new RuntimeException('Product not found.');
        }

        $this->repository->delete($product);
    }
}
