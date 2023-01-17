<?php

declare(strict_types=1);

use TCG\Voyager\Events\Routing;
use TCG\Voyager\Events\RoutingAdmin;
use TCG\Voyager\Events\RoutingAdminAfter;
use TCG\Voyager\Events\RoutingAfter;
use TCG\Voyager\Facades\Voyager;

/*
|--------------------------------------------------------------------------
| Voyager Routes
|--------------------------------------------------------------------------
|
| This file is where you may override any of the routes that are included
| with Voyager.
|
*/

Route::group(['prefix' => config('joy-voyager-datatable.admin_prefix', 'admin')], function () {
    Route::group(['as' => 'voyager.'], function () {
        // event(new Routing()); @deprecated

        $namespacePrefix = '\\'.config('joy-voyager-datatable.controllers.namespace').'\\';

        Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {
            // event(new RoutingAdmin()); @deprecated

            try {
                foreach (Voyager::model('DataType')::all() as $dataType) {
                    $breadController = $namespacePrefix.'VoyagerBaseController';

                    Route::get($dataType->slug . '/datatable', $breadController.'@index')->name($dataType->slug.'.datatable');
                    Route::get($dataType->slug . '/ajax', $breadController.'@ajax')->name($dataType->slug.'.ajax');
                    Route::post($dataType->slug . '/ajax', $breadController.'@ajax')->name($dataType->slug.'.post-ajax');

                    Route::get($dataType->slug . '/{id}/preview', $breadController.'@preview')->name($dataType->slug.'.preview');

                    Route::get($dataType->slug . '/quick-create', $breadController.'@quickCreate')->name($dataType->slug.'.quick-create');
                    Route::post($dataType->slug . '/quick-store', $breadController.'@quickStore')->name($dataType->slug.'.quick-store');

                    Route::get($dataType->slug . '/{id}/quick-edit', $breadController.'@quickEdit')->name($dataType->slug.'.quick-edit');
                    Route::post($dataType->slug . '/{id}/quick-update', $breadController.'@quickUpdate')->name($dataType->slug.'.quick-update');

                    Route::get($dataType->slug . '/{id}/inline-edit/{field}', $breadController.'@inlineEdit')->name($dataType->slug.'.inline-edit');
                    Route::post($dataType->slug . '/{id}/inline-update/{field}', $breadController.'@inlineUpdate')->name($dataType->slug.'.inline-update');

                    Route::delete($dataType->slug . '/{id}/quick-delete', $breadController.'@destroy')->name($dataType->slug.'.quick-delete');
                }
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException("Custom routes hasn't been configured because: ".$e->getMessage(), 1);
            } catch (\Exception $e) {
                // do nothing, might just be because table not yet migrated.
            }

            // event(new RoutingAdminAfter()); @deprecated
        });

        // event(new RoutingAfter()); @deprecated
    });
});
