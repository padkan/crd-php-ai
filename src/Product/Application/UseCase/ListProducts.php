<?php

declare(strict_types=1);

namespace App\Product\Application\UseCase;

use App\Product\Domain\Repository\ProductRepositoryInterface;

final readonly class ListProducts
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {
    }

    public function execute(): array
    {
        return $this->repository->findAll();
    }
}