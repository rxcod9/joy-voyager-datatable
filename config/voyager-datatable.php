<?php

return [

    /*
     * The config_key for voyager-datatable package.
     */
    'config_key' => env('VOYAGER_DATATABLE_CONFIG_KEY', 'joy-voyager-datatable'),

    /*
    |--------------------------------------------------------------------------
    | Controllers config
    |--------------------------------------------------------------------------
    |
    | Here you can specify voyager controller settings
    |
    */

    'controllers' => [
        'namespace' => 'Joy\\VoyagerDatatable\\Http\\Controllers',
    ],

    /*
     * List of available builders for DataTables.
     * This is where you can register your custom dataTables builder.
     */
    'engines'        => [
        'dataType'   => Joy\VoyagerDatatable\DataTypeDataTable::class,
        // 'eloquent'   => Yajra\DataTables\EloquentDataTable::class,
        // 'query'      => Yajra\DataTables\QueryDataTable::class,
        // 'collection' => Yajra\DataTables\CollectionDataTable::class,
        // 'resource'   => Yajra\DataTables\ApiResourceDataTable::class,
        // 'resource'   => Yajra\DataTables\ApiResourceDataTable::class,
    ],
];
