<?php

declare(strict_types=1);

namespace App\Basket\Model\Exception;

use App\Basket\Model\Basket\BasketId;
use App\Basket\Model\ERP\ProductId;

/**
 * @author Florent Blaison
 */
final class ProductAddedTwice extends \RuntimeException
{
    public static function toBasket(BasketId $basketId, ProductId $productId) : self
    {
        return new self(
            sprintf(
                'Product %s added twice to basket %s',
                $productId->toString(),
                $basketId->toString()
            )
        );
    }
}
