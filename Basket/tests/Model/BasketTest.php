<?php

declare(strict_types=1);

namespace App\BasketTest\Model;

use App\Basket\Model\Basket\Basket;
use App\Basket\Model\Basket\BasketId;
use App\Basket\Model\Basket\ShoppingSession;
use App\Basket\Model\ERP\ProductId;
use App\Basket\Model\Event\ProductAddedToBasket;
use App\Basket\Model\Event\ShoppingSessionStarted;
use App\Basket\Model\Exception\ProductAddedTwice;
use App\BasketTest\TestCase;
use Prooph\EventSourcing\AggregateChanged;
use Ramsey\Uuid\Uuid;

/**
 * @author Florent Blaison
 */
class BasketTest extends TestCase
{
    /**
     * @var ShoppingSession
     */
    private $shoppingSession;

    /**
     * @var BasketId
     */
    private $basketId;

    /**
     * @var ProductId
     */
    private $product1;

    protected function setUp()
    {
        $this->shoppingSession = ShoppingSession::fromString('123');
        $this->basketId = BasketId::fromString(Uuid::uuid4()->toString());
        $this->product1 = ProductId::fromString('A1');
    }

    /**
     * @test
     */
    public function it_starts_a_shopping_session()
    {
        $basket = Basket::startShoppingSession($this->shoppingSession, $this->basketId);

        /** @var AggregateChanged[] $events */
        $events = $this->popRecordedEvents($basket);

        $this->assertCount(1, $events);

        /** @var ShoppingSessionStarted $event */
        $event = $events[0];

        $this->assertSame(ShoppingSessionStarted::class, $event->messageName());
        $this->assertTrue($this->basketId->equals($event->basketId()));
        $this->assertTrue($this->shoppingSession->equals($event->shoppingSession()));
    }

    /**
     * @test
     */
    public function it_adds_a_product()
    {
        $basket = $this->reconstituteBasketFromHistory(
            $this->shoppingSessionStarted()
        );

        $basket->addProduct($this->product1);

        /** @var AggregateChanged[] $events */
        $events = $this->popRecordedEvents($basket);

        $this->assertCount(1, $events);

        /** @var ProductAddedToBasket $event */
        $event = $events[0];

        $this->assertSame(ProductAddedToBasket::class, $event->messageName());
        $this->assertTrue($this->basketId->equals($event->basketId()));
        $this->assertTrue($this->product1->equals($event->productId()));
    }

    /**
     * @test
     */
    public function it_throws_exception_if_product_is_added_twice()
    {
        $this->expectException(ProductAddedTwice::class);

        $basket = $this->reconstituteBasketFromHistory(
            $this->shoppingSessionStarted(),
            $this->product1Added()
        );

        //Add same product again
        $basket->addProduct($this->product1);
    }

    /**
     * Helper method to reconstitute a Basket from history
     *
     * With this helper we get better type hinting in the test methods
     * because type hint for reconstituteAggregateFromHistory() is only AggregateRoot
     *
     * @param AggregateChanged[] ...$events
     * @return Basket
     */
    private function reconstituteBasketFromHistory(AggregateChanged ...$events): Basket
    {
        return $this->reconstituteAggregateFromHistory(
            Basket::class,
            $events
        );
    }

    /**
     * Test helper to create a ShoppingSessionStarted event
     *
     * If we need to change signature of the event later, we have a central place in the test case
     * where we can align the creation.
     *
     * @return ShoppingSessionStarted
     */
    private function shoppingSessionStarted(): ShoppingSessionStarted
    {
        return ShoppingSessionStarted::occur($this->basketId->toString(), [
            'shopping_session' => $this->shoppingSession->toString()
        ]);
    }

    /**
     * Test helper to create a ProductAddedToBasket event
     *
     * If we need to change signature of the event later, we have a central place in the test case
     * where we can align the creation.
     *
     * @return ProductAddedToBasket
     */
    private function product1Added(): ProductAddedToBasket
    {
        return ProductAddedToBasket::occur($this->basketId->toString(), [
            'product_id' => $this->product1->toString()
        ]);
    }
}
