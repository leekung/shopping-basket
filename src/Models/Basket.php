<?php

namespace Iaa\ShoppingBasket\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Basket\Models\Basket.
 *
 * @property int $id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Iaa\ShoppingBasket\Models\BasketItemCollection|\Iaa\ShoppingBasket\Models\BasketItem[] $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Iaa\ShoppingBasket\Models\Basket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Iaa\ShoppingBasket\Models\Basket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Iaa\ShoppingBasket\Models\Basket query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Iaa\ShoppingBasket\Models\Basket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Iaa\ShoppingBasket\Models\Basket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Iaa\ShoppingBasket\Models\Basket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Iaa\ShoppingBasket\Models\Basket whereUserId($value)
 * @mixin \Eloquent
 */
class Basket extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Add a deleting listener to delete all items.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $basket) {
            return $basket->items()->delete();
        });
    }

    /**
     * The items in this basket instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(BasketItem::class);
    }
}
