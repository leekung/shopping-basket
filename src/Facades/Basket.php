<?php

namespace Iaa\ShoppingBasket\Facades;

use Illuminate\Support\Facades\Facade as Base;

/**
 * @see \Iaa\ShoppingBasket\BasketManager
 */
class Basket extends Base
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'basket';
    }
}
