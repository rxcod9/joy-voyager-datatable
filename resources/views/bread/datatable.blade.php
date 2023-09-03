@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing').' '.$dataType->getTranslatedAttribute('display_name_plural'))

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i> {{ $dataType->getTranslatedAttribute('display_name_plural') }}
        </h1>
        @can('add', app($dataType->model_name))
            <a href="{{ route('voyager.'.$dataType->slug.'.create') }}" class="btn btn-success btn-add-new">
                <i class="voyager-plus"></i> <span>{{ __('voyager::generic.add_new') }}</span>
            </a>
            @if(config('joy-voyager-datatable.quick-add.enabled', true))
                <x-joy-voyager-quick-add :slug="$dataType->slug" />
            @endif
        @endcan
        @can('delete', app($dataType->model_name))
            @include('voyager::partials.bulk-delete')
        @endcan
        @can('edit', app($dataType->model_name))
            @if(!empty($dataType->order_column) && !empty($dataType->order_display_column))
                <a href="{{ route('voyager.'.$dataType->slug.'.order') }}" class="btn btn-primary btn-add-new">
                    <i class="voyager-list"></i> <span>{{ __('voyager::bread.order') }}</span>
                </a>
            @endif
        @endcan
        @can('delete', app($dataType->model_name))
            @if($usesSoftDeletes)
                <input type="checkbox" @if ($showSoftDeleted) checked @endif class="show_soft_deletes" data-toggle="toggle" data-on="{{ __('voyager::bread.soft_deletes_off') }}" data-off="{{ __('voyager::bread.soft_deletes_on') }}">
            @endif
        @endcan
        @include('joy-voyager-datatable::bread.partials.group-actions', ['actions' => $actions, 'dataType' => $dataType, 'data' => null])
        @include('voyager::multilingual.language-selector')
    </div>
    @if(config('joy-voyager-datatable.lens.enabled', true))
    <div class="container-fluid">
        @include('joy-voyager-datatable::bread.partials.group-lenses', ['lenses' => $lenses, 'dataType' => $dataType, 'data' => null])
    </div>
    @endif
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <x-joy-voyager-datatable :slug="$dataType->slug" :active-lens="$activeLens" />
            </div>
        </div>
    </div>
@stop

@section('css')
    @if(config('dashboard.data_tables.responsive'))
        <link rel="stylesheet" href="{{ voyager_asset('lib/css/responsive.dataTables.min.css') }}">
    @endif
    <style>
        .modal-body {
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
@stop

@section('javascript')
    <!-- DataTables -->
    @if(config('dashboard.data_tables.responsive'))
        <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif
    <script>
        $(document).ready(function () {

            @if ($isModelTranslatable)
                $('.side-body').multilingual();
                //Reinitialise the multilingual features when they change tab
                $('#wrapper #dataTable').on('draw.dt', function(){
                    $('.side-body').data('multilingual').init();
                })
            @endif
        });

        @if($usesSoftDeletes)
            @php
                $params = [
                    // 'order_by' => $orderBy,
                    // 'sort_order' => $sortOrder,
                    'lense' => $activeLens,
                ];
            @endphp
            $(function() {
                $('.show_soft_deletes').change(function() {
                    if ($(this).prop('checked')) {
                        $('#wrapper #dataTable').before('<a class="redir" href="{{ (route('voyager.'.$dataType->slug.'.index', array_merge($params, ['showSoftDeleted' => 1]), true)) }}"></a>');
                    }else{
                        $('#wrapper #dataTable').before('<a class="redir" href="{{ (route('voyager.'.$dataType->slug.'.index', array_merge($params, ['showSoftDeleted' => 0]), true)) }}"></a>');
                    }

                    $('.redir')[0].click();
                })
            })
        @endif
    </script>
@stop
