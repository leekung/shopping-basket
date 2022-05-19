# A Database Driven Shopping Basket for Laravel

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/iaa/shopping-basket.svg?style=flat-square)](https://packagist.org/packages/iaa/shopping-basket)
[![Total Downloads](https://img.shields.io/packagist/dt/iaa/shopping-basket.svg?style=flat-square)](https://packagist.org/packages/iaa/shopping-basket)
[![Build Status](https://travis-ci.com/iaa/shopping-basket.svg?branch=master)](https://travis-ci.com/iaa/shopping-basket)
[![StyleCI](https://github.styleci.io/repos/213270049/shield?branch=master)](https://github.styleci.io/repos/213270049)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/iaa/shopping-basket/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/iaa/shopping-basket/?branch=master)

This is a simple shopping basket implementation for Laravel 6/7/8. It automatically serializes your basket to the database and loads the related product models.

## Usage

To get started, add the `Buyable` interface to your model.

```php
use Illuminate\Database\Eloquent\Model;
use Iaa\ShoppingBasket\Buyable;
use Iaa\ShoppingBasket\BuyableTrait;

class Product extends Model implements Buyable
{
    use BuyableTrait;
}
```

Make sure you implement the `getBuyableDescription` and `getBuyablePrice` methods with the respective product description and product price.

Now you can add products to the basket.
```php
use Iaa\ShoppingBasket\Facades\Basket;

$product = Product::create(['name' => 'Pizza Slice', 'price' => 1.99]);
$quantity = 2;

Basket::add($product, $quantity);
```

To retrieve the basket contents:
```php
Basket::content();
// or
Basket::items();
```

To retrieve the total:
```php
Basket::subtotal();
```

You can update the quantity of an item in the basket. The first argument is the primary id of the related `BasketItem`.
```php
$item = Basket:content()->first();

Basket::update($item->id, $item->quantity + 5);
```

Or remove the item completely.
```php
Basket::remove($item->id);
```

### Options
To add item-specific options (such as size or color) to an item in the basket, first register available options in your `Buyable` instance.
```php
class Product extends Model implements Buyable
{
    // ...
    
    public function getOptions(): array
    {
        return [
            'size' => ['18 inch', '36 inch'],
            'color' => ['white', 'blue', 'black'],
        ];
    }
}
```

Then you just pass an associative array as the third parameter of `Basket::add`.
```php
Basket::add($product, 3, ['color' => 'white']);
```
Any invalid options will be silently removed from the array.

You can also add or change options of an item currently in the basket by calling `Basket::updateOption`.
```php
$item = Basket:content()->first();

// Update a single option
Basket::updateOption($item->id, 'color', 'black');

// Update multiple options at once
Basket::updateOptions($item->id, [
    'color' => 'black',
    'size' => '36 inch',
]);
``` 
The options array will be available on the `BasketItem` instance as `$item->options`.

### Attaching to Users

You can attach a basket instance to a user, so that their basket from a previous session can be retrieved. Attaching a basket to a user is acheived by calling the `attachTo` method, passing in an instance of `Illuminate\Contracts\Auth\Authenticatable`.

```php
class RegisterController
{
    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        Basket::attachTo($user);
    }
}
``` 

Then when the user logs in, you can call the `loadUserBasket` method, again passing the user instance.

```php
class LoginController
{
    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        Basket::loadUserBasket($user);
    }
}
```

### Dependency Injection

If you're not a facade person, you can use the container to inject the shopping basket instance by type-hinting the `Iaa\ShoppingBasket\BasketManager` class, or the `Iaa\ShoppingBasket\BasketContract` interface.

### Tax

The shopping basket can calculate the total tax of the items in the basket. Just call
```php
$rate = 13; // The tax rate as a percentage

Basket::tax($rate);
```

You can also set a default tax rate in the included config file.
```php
// config/shopping-basket.php

    'tax' => [
        'rate' => 6,
    ],
```

Then just call `Basket::tax` without a parameter.
```php
Basket::tax();
```

If some of your items have different tax rates applicable to them, or are tax-free, no problem. First modify the config file:
```php
// config/shopping-basket.php

    'tax' => [
        'mode' => 'per-item',
    ],
```
Then, set the tax rate per item by implementing the `Taxable` interface and defining a `getTaxRate` method.
```php
use Iaa\ShoppingBasket\Taxable;

class Product extends Model implements Buyable, Taxable
{
    /**
     * Calculate the tax here based on a database column, or whatever you will.
     *
     * @return int|float
     */
    public function getTaxRate()
    {
        if ($this->tax_rate) {
            return $this->tax_rate;
        }

        if (! $this->taxable) {
            return 0;
        }

        return 8;
    }
```

Now your items will have their custom tax rate applied to them when calling `Basket::tax`. 

## Installation

You can install the package via composer:

```bash
composer require iaa/shopping-basket
```

To publish the config file and migrations, run
```bash
php artisan vendor:publish --provider="Iaa\ShoppingBasket\BasketServiceProvider"
```

And run the included database migrations.

```bash
php artisan migrate
```

## Testing

``` bash
composer test
```

## Starter (demo) Repository
If you would like to see a starter/demo implementation using this shopping basket please check out our [laravel-commerce repository](https://github.com/iaa/laravel-commerce)

## Roadmap

Some things I didn't get around to yet:

- Clear basket instance which has not been attached to a user when session is destroyed.
- Add an Artisan command that will clear any unattached baskets (these two might be mutually exclusive)
- Add ability to configure basket merging strategy when `loadUserBasket` is called

## Credits

- Created by [Avraham Appel](https://github.com/iaa)
- Initial development sponsored by [Bomshteyn Consulting](https://bomshteyn.com)
- Inspired by [LaravelShoppingbasket package](https://github.com/Crinsane/LaravelShoppingbasket) by [@Crisane](https://github.com/Crinsane)

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
