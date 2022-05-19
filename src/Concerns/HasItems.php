<?php

namespace Iaa\ShoppingBasket\Concerns;

use Iaa\ShoppingBasket\Models\BasketItemCollection;

trait HasItems
{
    /**
     * @var \Iaa\ShoppingBasket\Models\Basket
     */
    protected $basket;

    /**
     * Get the basket contents.
     *
     * @return \Iaa\ShoppingBasket\Models\BasketItemCollection|\Iaa\ShoppingBasket\Models\BasketItem[]
     */
    public function items(): BasketItemCollection
    {
        return $this->basket->items;
    }
}
