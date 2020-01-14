<?php

namespace AmbitionPHP\Captcha;

use Illuminate\Support\ServiceProvider;

/**
 * Class CaptchaServiceProvider.
 *
 * @author Hashem Moghaddari
 */
class CaptchaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration files
        $this->publishes([
            __DIR__.'/../config/scaptcha.php' => config_path('scaptcha.php'),
        ], 'config');

        // HTTP routing
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Validator extensions
        $this->app['validator']->extend('captcha', function ($attribute, $value, $parameters) {
            return captcha_check($value);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //Bind captcha class
        $this->app->bind('captcha', function () {
            return new Captcha();
        });
    }
}
