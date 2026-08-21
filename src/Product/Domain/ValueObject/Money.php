<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        private int $amount,
        private string $currency,
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException(
                'Money amount cannot be negative.'
            );
        }

        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException(
                'Currency must use a 3-letter code.'
            );
        }
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return strtoupper($this->currency);
    }
}