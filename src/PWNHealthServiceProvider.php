<?php namespace AliasProject\PWNHealth;

use Illuminate\Support\ServiceProvider;

class PWNHealthServiceProvider extends ServiceProvider {

   /**
     * Bootstrap the application services.
     *
     * @return void
    */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/pwnhealth.php' => config_path('pwnhealth.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
    */
    public function register()
    {
        //
    }
}
