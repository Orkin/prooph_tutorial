<?php

declare(strict_types=1);

namespace App\Basket\Model\Exception;

use App\Basket\Model\ERP\ProductId;

/**
 * @author Florent Blaison
 */
final class ProductOutOfStock extends \RuntimeException
{
    public static function withProductId(ProductId $productId) : self
    {
        return new self(
            sprintf(
                'Product with %s is out of stock.',
                $productId->toString()
            )
        );
    }
}
