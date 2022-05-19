<?php

namespace Iaa\ShoppingBasket\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use Iaa\ShoppingBasket\Models\Basket as BasketModel;

trait AttachesToUsers
{
    /**
     * Load the given user's shopping basket.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return static
     */
    public function loadUserBasket(Authenticatable $user): self
    {
        // If the user doesn't yet have a saved basket, we'll
        // just attach the current one to the user and exit.
        if (! $basket = BasketModel::whereUserId($user->getAuthIdentifier())->with('items')->first()) {
            return $this->attachTo($user);
        }

        // If the current basket is empty, we'll load the saved one.
        if ($this->items()->isEmpty()) {
            return $this->refreshBasket($basket);
        }

        // Otherwise, we'll overwrite the saved basket with the current one.
        // TODO add a strategy to be able to merge with the saved basket
        return $this->overwrite($user);
    }

    /**
     * Attach the current basket to the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return static
     */
    public function attachTo(Authenticatable $user): self
    {
        $this->basket->fill([
            'user_id' => $user->getAuthIdentifier(),
        ]);

        if ($this->basket->exists) {
            $this->basket->save();
        }

        return $this;
    }

    /**
     * Delete any old baskets belonging to the given user and attach
     * the current basket to them.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return static
     */
    protected function overwrite(Authenticatable $user): self
    {
        BasketModel::whereUserId($user->getAuthIdentifier())
                 ->whereKeyNot($this->basket->getKey())
                 // Delete them using Eloquent so that events will be fired
                 // and trigger deletion of basket items as well.
                 ->get()->each->delete();

        return $this->attachTo($user);
    }
}
