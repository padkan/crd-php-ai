<?php

declare(strict_types=1);

namespace App\Product\Domain\Entity;

use App\Product\Domain\ValueObject\Money;
use InvalidArgumentException;

final class Product
{
    private const int MAX_DESCRIPTION_LENGTH = 1000;

    public function __construct(
        private ?int $id,
        private string $name,
        private string $description,
        private Money $price,
    ) {
        $this->rename($name);
        $this->changeDescription($description);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
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

    public function changeDescription(string $description): void
    {
        $description = trim($description);

        if (mb_strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'Product description cannot exceed %d characters.',
                self::MAX_DESCRIPTION_LENGTH,
            ));
        }

        $this->description = $description;
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