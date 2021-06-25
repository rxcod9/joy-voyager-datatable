<?php

namespace Joy\VoyagerDatatable;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Config;

/**
 * Class VoyagerDatatableServiceProvider
 *
 * @category  Package
 * @package   JoyVoyagerDatatable
 * @author    Ramakant Gangwar <gangwar.ramakant@gmail.com>
 * @copyright 2021 Copyright (c) Ramakant Gangwar (https://github.com/rxcod9)
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

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'joy-voyager-datatable');

        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'joy-voyager-datatable');

        $this->loadDatatablesEngines();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../routes/web.php');
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
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function loadDatatablesEngines(): void
    {
        $engines = (array) config('voyager-datatable.engines', []);
        foreach ($engines as $engine => $class) {
            $engine = Str::camel($engine);

            if (! method_exists(DataTables::class, $engine) && ! DataTables::hasMacro($engine)) {
                DataTables::macro($engine, function () use ($class) {
                    if (! call_user_func_array([$class, 'canCreate'], func_get_args())) {
                        throw new \InvalidArgumentException();
                    }

                    return call_user_func_array([$class, 'create'], func_get_args());
                });
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/voyager-datatable.php', 'joy-voyager-datatable');

        $this->registerCommands();
    }


    /**
     * Register publishables.
     *
     * @return void
     */
    protected function registerPublishables(): void
    {
        $this->publishes(
            [
            __DIR__ . '/../config/voyager-datatable.php' => config_path('joy-voyager-datatable.php'),
            ],
            'config'
        );

        $this->publishes(
            [
            __DIR__ . '/../resources/views' => resource_path('views/vendor/joy-voyager-datatable'),
            ],
            'views'
        );

        $this->publishes(
            [
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/joy-voyager-datatable'),
            ],
            'translations'
        );
    }

    protected function registerCommands(): void
    {
        //
    }
}
