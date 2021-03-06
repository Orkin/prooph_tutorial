<?php

declare(strict_types=1);

namespace App\Basket\Model\ERP;

/**
 * @author Florent Blaison
 */
final class ProductId
{
    private $id;

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    private function __construct(string $id)
    {
        if($id === '') {
            throw new \InvalidArgumentException("Product id must not be an empty string");
        }

        $this->id = $id;
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function equals($other): bool
    {
        if(!$other instanceof self) {
            return false;
        }

        return $this->id === $other->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
