<?php

declare(strict_types=1);

namespace App\Basket\Model\Basket;

use App\Basket\Model\ERP\ERP;
use App\Basket\Model\ERP\ProductId;
use App\Basket\Model\Event\ProductAddedToBasket;
use App\Basket\Model\Event\ShoppingSessionStarted;
use App\Basket\Model\Exception\ProductAddedTwice;
use App\Basket\Model\Exception\ProductOutOfStock;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;

/**
 * @author Florent Blaison
 */
final class Basket extends AggregateRoot
{
    /**
     * @var BasketId
     */
    private $basketId;

    /**
     * @var ShoppingSession
     */
    private $shoppingSession;

    /**
     * @var ProductId[]
     */
    private $products = [];

    public static function startShoppingSession(ShoppingSession $shoppingSession, BasketId $basketId)
    {
        $self = new self();

        $self->recordThat(
            ShoppingSessionStarted::occur(
                $basketId->toString(),
                [
                    'shopping_session' => $shoppingSession->toString(),
                ]
            )
        );

        return $self;
    }

    public function addProduct(ProductId $productId, ERP $erp) : void
    {
        if (array_key_exists($productId->toString(), $this->products)) {
            throw ProductAddedTwice::toBasket($this->basketId, $productId);
        }

        $productStock = $erp->getProductStock($productId);

        if (! $productStock) {
            $this->recordThat(
                ProductAddedToBasket::occur(
                    $this->basketId->toString(),
                    [
                        'product_id' => $productId->toString(),
                        //If we did not get a response, we add the product and check stock later again
                        //the shopping session should not be blocked by a temporarily unavailable ERP system
                        'stock_version' => null,
                        'stock_quantity' => null,
                        'quantity' => 1,
                    ]
                )
            );

            return;
        }

        if($productStock->quantity() === 0) {
            throw ProductOutOfStock::withProductId($productId);
        }

        $this->recordThat(
            ProductAddedToBasket::occur(
                $this->basketId->toString(),
                [
                    'product_id' => $productId->toString(),
                    'stock_version' => $productStock->version(),
                    'stock_quantity' => $productStock->quantity(),
                    'quantity' => 1,
                ]
            )
        );
    }

    protected function aggregateId() : string
    {
        return $this->basketId->toString();
    }

    /**
     * Apply given event
     */
    protected function apply(AggregateChanged $event) : void
    {
        switch ($event->messageName()) {
            case ShoppingSessionStarted::class:
                /** @var $event ShoppingSessionStarted */
                $this->basketId = $event->basketId();
                $this->shoppingSession = $event->shoppingSession();
                break;
            case ProductAddedToBasket::class:
                /** @var $event ProductAddedToBasket */
                $this->products[$event->productId()->toString()] = [
                    'stock_quantity' => $event->stockQuantity(),
                    'stock_version' => $event->stockVersion(),
                    'quantity' => $event->quantity()
                ];
                break;
        }
    }
}
