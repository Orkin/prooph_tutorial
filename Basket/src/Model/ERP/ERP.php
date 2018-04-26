<?php
/**
 * User: orkin
 * Date: 25/04/2018
 * Time: 20:00.
 */

namespace App\Basket\Model\ERP;

use App\Basket\Model\Exception\UnknownProduct;

/**
 * @author Florent Blaison
 */
interface ERP
{
    /**
     * Get stock information for given product
     *
     * If stock information cannot be fetched from the ERP system
     * this method returns null.
     *
     * If product is not known by the ERP system this method must throw an UnknownProduct exception
     *
     * @param ProductId $productId
     * @return ProductStock|null
     * @throws UnknownProduct
     */
    public function getProductStock(ProductId $productId): ?ProductStock;
}
