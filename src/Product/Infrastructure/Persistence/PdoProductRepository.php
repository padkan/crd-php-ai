<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepositoryInterface;
use App\Product\Domain\ValueObject\Money;
use PDO;

final readonly class PdoProductRepository
    implements ProductRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    public function save(Product $product): Product
    {
        if ($product->id() === null) {
            return $this->insert($product);
        }

        return $this->update($product);
    }

    public function findById(int $id): ?Product
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, price_cents, currency
             FROM products
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
        ]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, name, price_cents, currency
             FROM products
             ORDER BY id DESC'
        );

        return array_map(
            fn (array $row): Product => $this->hydrate($row),
            $statement->fetchAll(),
        );
    }

    public function delete(Product $product): void
    {
        if ($product->id() === null) {
            return;
        }

        $statement = $this->pdo->prepare(
            'DELETE FROM products WHERE id = :id'
        );

        $statement->execute([
            'id' => $product->id(),
        ]);
    }

    private function insert(Product $product): Product
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO products (
                name,
                price_cents,
                currency
            ) VALUES (
                :name,
                :price_cents,
                :currency
            )'
        );

        $statement->execute([
            'name' => $product->name(),
            'price_cents' => $product->price()->amount(),
            'currency' => $product->price()->currency(),
        ]);

        $product->assignId(
            (int) $this->pdo->lastInsertId()
        );

        return $product;
    }

    private function update(Product $product): Product
    {
        $statement = $this->pdo->prepare(
            'UPDATE products
             SET
                 name = :name,
                 price_cents = :price_cents,
                 currency = :currency
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $product->id(),
            'name' => $product->name(),
            'price_cents' => $product->price()->amount(),
            'currency' => $product->price()->currency(),
        ]);

        return $product;
    }

    private function hydrate(array $row): Product
    {
        return new Product(
            id: (int) $row['id'],
            name: $row['name'],
            price: new Money(
                amount: (int) $row['price_cents'],
                currency: $row['currency'],
            ),
        );
    }
}