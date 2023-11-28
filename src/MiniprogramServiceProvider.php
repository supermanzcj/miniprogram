<?php

namespace Superzc\Miniprogram;

use Illuminate\Support\ServiceProvider;

class MiniprogramServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/config/miniprogram.php' => config_path('miniprogram.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('miniprogram', function ($app) {
            return new Miniprogram($app['config']);
        });
    }
}