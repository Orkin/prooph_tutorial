<?php

declare(strict_types=1);

namespace App\Basket\Model\Exception;

use App\Basket\Model\ERP\ProductId;

/**
 * @author Florent Blaison
 */
final class UnknownProduct extends \InvalidArgumentException
{
    public static function withProductId(ProductId $productId) : self
    {
        return new self(
            sprintf(
                'Product with %s is unknown.',
                $productId->toString()
            )
        );
    }
}
