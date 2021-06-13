<?php

namespace Joy\VoyagerDatatable;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Class VoyagerDatatableServiceProvider
 *
 * @category  Package
 * @package   JoyVoyagerDatatable
 * @author    Ramakant Gangwar <gangwar.ramakant@gmail.com>
 * @copyright 2020 Copyright (c) Ramakant Gangwar (https://github.com/rxcod9)
 * @license   http://github.com/rxcod9/joy-voyager-datatable/blob/main/LICENSE New BSD License
 * @link      https://github.com/rxcod9/joy-voyager-datatable
 */
class VoyagerDatatableServiceProvider extends ServiceProvider
{
    /**
     * Boot
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishables();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'joy-voyager-datatable');

        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'joy-voyager-datatable');
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../routes/web.php');
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/voyager-datatable.php', 'joy-voyager-datatable');

        $this->registerCommands();
    }


    /**
     * Register publishables.
     *
     * @return void
     */
    protected function registerPublishables(): void
    {
        $this->publishes([
            __DIR__.'/../config/voyager-datatable.php' => config_path('joy-voyager-datatable.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/joy-voyager-datatable'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/joy-voyager-datatable'),
        ], 'translations');
    }

    protected function registerCommands(): void
    {
        //
    }
}
