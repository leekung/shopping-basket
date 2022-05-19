<?php

namespace Iaa\ShoppingBasket;

use Iaa\ShoppingBasket\Models\BasketItemCollection;

interface BasketContract
{
    /**
     * Get the basket contents.
     *
     * @return \Iaa\ShoppingBasket\Models\BasketItemCollection|\Iaa\ShoppingBasket\Models\BasketItem[]
     */
    public function content(): BasketItemCollection;

    /**
     * Get the subtotal of items in the basket.
     *
     * @return int|float
     */
    public function subtotal();

    /**
     * Add an item to the basket.
     *
     * @param  \Iaa\ShoppingBasket\Buyable  $buyable
     * @param  int  $quantity
     */
    public function add(Buyable $buyable, int $quantity);

    /**
     * Change the quantity of an item in the basket.
     *
     * @param  int  $item
     * @param  int  $quantity
     */
    public function update(int $item, int $quantity);

    /**
     * Remove an item from the basket.
     *
     * @param  int  $item
     */
    public function remove(int $item);

    /**
     * Destroy the basket instance.
     */
    public function destroy();
}
