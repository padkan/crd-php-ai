<?php

declare(strict_types=1);

namespace App\Product\Domain\Entity;

use App\Product\Domain\ValueObject\Money;
use InvalidArgumentException;

final class Product
{
    public function __construct(
        private ?int $id,
        private string $name,
        private Money $price,
    ) {
        $this->rename($name);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function rename(string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException(
                'Product name cannot be empty.'
            );
        }

        $this->name = $name;
    }

    public function changePrice(Money $price): void
    {
        $this->price = $price;
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new InvalidArgumentException(
                'Product already has an ID.'
            );
        }

        $this->id = $id;
    }
}