<?php

namespace Iaa\ShoppingBasket;

// TODO When session is destroyed, delete basket not attached to user

use Countable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use Iaa\ShoppingBasket\Models\Basket;

class BasketManager implements Countable, BasketContract
{
    use Concerns\ManagesBasketItems;
    use Concerns\CalculatesTotals;
    use Concerns\AttachesToUsers;
    use ForwardsCalls;
    use Macroable {
        __call as macroCall;
    }

    /**
     * BasketManager constructor.
     *
     * @param  \Iaa\ShoppingBasket\Models\Basket|\Illuminate\Database\Eloquent\Model  $basket
     */
    public function __construct(Basket $basket)
    {
        $this->basket = $basket;

        $this->refreshBasket();
    }

    /**
     * Instantiate the basket manager with a basket id saved in the current session.
     *
     * @param  string  $identifier
     * @return static
     */
    public static function fromSessionIdentifier($identifier): self
    {
        $basket = Basket::findOrNew($identifier);

        return new static($basket);
    }

    /**
     * Instantiate the basket manager with the basket attached to the currently authenticated user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return static
     */
    public static function fromUserId(Authenticatable $user): self
    {
        return new static(Basket::where('user_id', $user->getAuthIdentifier())->firstOrNew([
            'user_id' => $user->getAuthIdentifier(),
        ]));
    }

    /**
     * Pass dynamic method calls to the items collection.
     *
     * @param  string  $method
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $arguments);
        }

        return $this->forwardCallTo($this->items(), $method, $arguments);
    }
}
