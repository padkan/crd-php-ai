<?php

declare(strict_types=1);

namespace App\Product\Domain\Repository;

use App\Product\Domain\Entity\Product;

interface ProductRepositoryInterface
{
    public function save(Product $product): Product;

    public function findById(int $id): ?Product;

    /**
     * @return list<Product>
     */
    public function findAll(): array;

    public function delete(Product $product): void;
}