<?php

namespace Iaa\ShoppingBasket;

use Illuminate\Support\ServiceProvider;
use Iaa\ShoppingBasket\Models\Basket;

class BasketServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('shopping-basket.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }
    }

    /**
     * Register the application services.
     * TODO dedicated basket factory class
     * TODO replace Laravel framework facades with contracts.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'shopping-basket');

        $this->app->singleton('basket', function ($app) {
            if ($app['session']->has('basket')) {
                return BasketManager::fromSessionIdentifier($app['session']->get('basket'));
            }

            if ($app['auth']->check()) {
                return BasketManager::fromUserId($app['auth']->user());
            }

            $basket_id = intval(request()->header('basket-id'));
            $basket_hash = request()->header('basket-hash');
            if ($basket_id && $basket_hash && sha1($basket_id . substr(config('app.key'), 0, 10)) == $basket_hash) {
                return BasketManager::fromSessionIdentifier($basket_id);
            }

            return new BasketManager(new Basket());
        });

        $this->app->alias('basket', BasketManager::class);
        $this->app->alias('basket', BasketContract::class);
    }
}
