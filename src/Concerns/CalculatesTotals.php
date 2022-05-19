<?php

namespace Iaa\ShoppingBasket\Concerns;

use Closure;
use Illuminate\Support\Facades\Config;
use Iaa\ShoppingBasket\Models\BasketItem;
use Iaa\ShoppingBasket\Models\BasketItemCollection;
use Iaa\ShoppingBasket\Taxable;

trait CalculatesTotals
{
    /**
     * @var float
     */
    protected $subtotal = 0.0;

    /**
     * @var float
     */
    protected $tax = 0.0;

    /**
     * Get the basket contents.
     *
     * @return \Iaa\ShoppingBasket\Models\BasketItemCollection
     */
    public function content(): BasketItemCollection
    {
        return $this->items();
    }

    /**
     * Get the number of items in the basket.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Get the subtotal of items in the basket.
     *
     * @return int|float
     */
    public function subtotal(): float
    {
        if (! $this->subtotal) {
            $this->subtotal = $this->items()->sumRounded(function (BasketItem $item) {
                return $item->subtotal;
            });
        }

        return $this->subtotal;
    }

    /**
     * Get the tax for items in the basket.
     *
     * @param  int|float|null  $rate
     * @return float
     */
    public function tax($rate = null): float
    {
        if (! $this->tax) {
            $this->tax = $this->items()->sumRounded($this->getTaxAmountForItem($rate));
        }

        return $this->tax;
    }

    /**
     * Clear the cached totals.
     *
     * @return void
     */
    public function clearCached(): void
    {
        $this->subtotal = 0.0;
        $this->tax = 0.0;
    }

    /**
     * Figure out how to calculate tax for the basket items.
     *
     * @param  int|float|null  $rate
     * @return \Closure
     */
    protected function getTaxAmountForItem($rate = null): Closure
    {
        if (! $rate && Config::get('shopping-basket.tax.mode') == 'flat') {
            $rate = Config::get('shopping-basket.tax.rate');
        }

        return function (BasketItem $item) use ($rate) {
            if (! $rate) {
                $rate = $item->buyable instanceof Taxable
                    ? $item->buyable->getTaxRate()
                    : 0;
            }

            return Config::get('shopping-basket.tax.mode') === 'fixed-per-item' ? $rate : round($item->price * $item->quantity * ($rate / 100), 2);
        };
    }
}
