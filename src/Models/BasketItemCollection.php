<?php

namespace Iaa\ShoppingBasket\Models;

use Illuminate\Database\Eloquent\Collection;

class BasketItemCollection extends Collection
{
    /**
     * Take a sum and round it to the specified number of spaces.
     *
     * @param  callable|null  $callback
     * @param  int  $places
     * @return float
     */
    public function sumRounded(callable $callback = null, int $places = 2)
    {
        return round($this->sum($callback), $places);
    }
}
