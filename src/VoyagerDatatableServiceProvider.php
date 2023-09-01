<?php

namespace Joy\VoyagerDatatable;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Joy\VoyagerDatatable\Events\FilterFormFieldsRegistered;
use Joy\VoyagerDatatable\Facades\Voyager as VoyagerFacade;
use Joy\VoyagerDatatable\FilterFormFields\After\DescriptionHandler;
use Joy\VoyagerDatatable\View\Components\Datatable;
use Joy\VoyagerDatatable\View\Components\Datatables as DatatablesComponent;
use Joy\VoyagerDatatable\View\Components\QuickAdd;
use TCG\Voyager\Facades\Voyager as TcgVoyager;
use Yajra\DataTables\DataTables;

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
        TcgVoyager::replaceAction(
            \TCG\Voyager\Actions\DeleteAction::class,
            \Joy\VoyagerDatatable\Actions\QuickDeleteAction::class,
        );
        TcgVoyager::addAction(\Joy\VoyagerDatatable\Actions\PreviewAction::class);
        TcgVoyager::addAction(\Joy\VoyagerDatatable\Actions\QuickEditAction::class);

        Request::macro('activeLens', function ($dataType) {
            return $this->query('lense', Session::get($dataType->slug . '_activeLens'));
        });

        Request::macro('lens', function ($dataType, $model) {
            $activeLens = $this->activeLens($dataType);

            if (!$activeLens) {
                return;
            }

            $lens = collect(VoyagerFacade::lenses())->first(function ($lens) use ($activeLens, $dataType, $model) {
                $lens = new $lens($dataType, $model);
                return $lens->getRouteKey() === $activeLens;
            });

            if (!$lens) {
                return;
            }

            return new $lens($dataType, $model);
        });

        $this->registerPublishables();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'joy-voyager-datatable');

        $this->mapApiRoutes();

        $this->mapWebRoutes();

        if (config('joy-voyager-datatable.database.autoload_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'joy-voyager-datatable');

        $this->loadDatatablesEngines();

        $this->bootComponents();
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

            if (!method_exists(DataTables::class, $engine) && !DataTables::hasMacro($engine)) {
                DataTables::macro($engine, function () use ($class) {
                    if (!call_user_func_array([$class, 'canCreate'], func_get_args())) {
                        throw new \InvalidArgumentException();
                    }

                    return call_user_func_array([$class, 'create'], func_get_args());
                });
            }
        }
    }

    /**
     * Boot components.
     */
    protected function bootComponents(): void
    {
        app('blade.compiler')->component('joy-voyager-datatable', Datatable::class);
        app('blade.compiler')->component('joy-voyager-datatables', DatatablesComponent::class);
        app('blade.compiler')->component('joy-voyager-quick-add', QuickAdd::class);
        app('blade.compiler')->componentNamespace('\\Joy\\VoyagerDatatable\\View\\Components', 'joy-voyager-datatable');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('JoyVoyagerDatatable', VoyagerFacade::class);

        $this->app->singleton('joy-voyager-datatable', function () {
            return new Voyager();
        });

        $this->registerFilterFormFields();

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

    protected function registerFilterFormFields()
    {
        $filterFilterFormFields = [
            'checkbox',
            'multiple_checkbox',
            'color',
            'date',
            'file',
            'image',
            'multiple_images',
            'media_picker',
            'number',
            'password',
            'radio_btn',
            'rich_text_box',
            'code_editor',
            'markdown_editor',
            'select_dropdown',
            'select_multiple',
            'text',
            'text_area',
            'time',
            'timestamp',
            // 'hidden',
            // 'coordinates',
        ];

        foreach ($filterFilterFormFields as $filterFilterFormField) {
            $class = Str::studly("{$filterFilterFormField}_handler");

            VoyagerFacade::addFilterFormField("Joy\\VoyagerDatatable\\FilterFormFields\\{$class}");
        }

        VoyagerFacade::addAfterFilterFormField(DescriptionHandler::class);

        event(new FilterFormFieldsRegistered($filterFilterFormFields));
    }
}
