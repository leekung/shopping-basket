<?php

namespace Iaa\ShoppingBasket\Concerns;

use Illuminate\Support\Facades\Session;
use Iaa\ShoppingBasket\Buyable;
use Iaa\ShoppingBasket\Models\Basket;
use Iaa\ShoppingBasket\Models\BasketItem;

trait ManagesBasketItems
{
    use HasItems;

    /**
     * Get the basket model instance.
     *
     * @return \Iaa\ShoppingBasket\Models\Basket
     */
    public function getModel(): Basket
    {
        return $this->basket;
    }

    /**
     * Add an item to the basket.
     *
     * @param  \Iaa\ShoppingBasket\Buyable  $buyable
     * @param  int  $quantity
     * @param  array|null  $options
     * @return \Iaa\ShoppingBasket\BasketManager
     */
    public function add(Buyable $buyable, int $quantity = 1, array $options = []): self
    {
        $newItem = new BasketItem();
        $newItem->setRelation('buyable', $buyable);
        $newItem->buyable()->associate($buyable);
        $newItem->fill([
            'quantity' => $quantity,
            'options' => $options,
        ]);

        $item = $this->items()->first(function (BasketItem $basketItem) use ($newItem) {
            return $basketItem->getIdentifierAttribute() === $newItem->getIdentifierAttribute();
        });

        // If the item already exists in the basket, we'll
        // just update the quantity by the given value.
        if ($item) {
            $item->increment('quantity', $quantity);

            return $this;
        }

        if (! $this->basket->exists) {
            $this->basket->save();
        }

        // We persist the new item to the database and add it to the items
        // collection. Eloquent doesn't do this by default, so we'll do it ourselves.
        $this->basket->items->add(
            $this->basket->items()->save($newItem)
        );

        $this->basket->push();

        $this->refreshBasket();

        return $this;
    }

    /**
     * Change the quantity of an item in the basket.
     *
     * @param  int  $item
     * @param  int  $quantity
     * @return \Iaa\ShoppingBasket\BasketManager
     * @throws \Exception
     */
    public function update(int $item, int $quantity): self
    {
        return $this->updateQuantity($item, $quantity);
    }

    /**
     * Change the quantity of an item in the basket.
     *
     * @param  int  $item
     * @param  int  $quantity
     * @return \Iaa\ShoppingBasket\BasketManager
     * @throws \Exception
     */
    public function updateQuantity(int $item, int $quantity): self
    {
        if ($quantity <= 0) {
            return $this->remove($item);
        }

        if (! $this->items()->contains($item)) {
            return $this;
        }

        $this->items()->find($item)->update(['quantity' => $quantity]);

        return $this;
    }

    /**
     * Update the options of an item in the basket.
     *
     * @param  int  $item
     * @param  array  $options
     * @return \Iaa\ShoppingBasket\BasketManager
     */
    public function updateOptions(int $item, array $options): self
    {
        $this->items()->find($item)->update(['options' => $options]);

        return $this;
    }

    /**
     * Update an option of an item in the basket.
     *
     * @param  int  $item
     * @param  string  $option
     * @param $value
     * @return \Iaa\ShoppingBasket\BasketManager
     */
    public function updateOption(int $item, string $option, $value): self
    {
        return $this->updateOptions($item, [$option => $value]);
    }

    /**
     * Remove an item from the basket.
     *
     * @param  int  $item
     * @return static
     * @throws \Exception
     */
    public function remove(int $item): self
    {
        $key = $this->items()->search(function (BasketItem $i) use ($item) {
            return $i->getKey() == $item;
        });

        if ($key === false) {
            return $this;
        }

        $this->items()->pull($key)->delete();

        if ($this->items()->isEmpty()) {
            return $this->destroy();
        }

        return $this;
    }

    /**
     * Destroy the basket instance.
     *
     * @return static
     */
    public function destroy()
    {
        $this->basket->delete();

        $this->refreshBasket(new Basket());

        return $this;
    }

    /**
     * Toggle the session key, and recalculate totals.
     *
     * @param  \Iaa\ShoppingBasket\Models\Basket|null  $basket
     * @return static
     */
    public function refreshBasket(Basket $basket = null): self
    {
        if ($basket) {
            $this->basket = $basket;
        }

        $basket = $basket ?? $this->basket;

        if ($basket->exists) {
            $basket->loadMissing('items.buyable');

            Session::put('basket', $basket->getKey());
        } else {
            Session::forget('basket');
        }

        $this->clearCached();

        return $this;
    }

    /**
     * Persist the basket contents to the database.
     *
     * @return static
     */
    protected function persist(): self
    {
        Session::put('basket', $this->basket->getKey());

        return $this;
    }
}
