<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;

if (!function_exists('dataTypeTableColumns')) {
    /**
     * Helper to get dataType dataTable columns.
     */
    function dataTypeTableColumns(
        DataType $dataType,
        bool $showCheckboxColumn,
        array $searchableColumns = [],
        array $sortableColumns = []
    ): array {
        $model   = app($dataType->model_name);
        $columns = $showCheckboxColumn ? [[
            'data'       => 'index',
            'name'       => $model->getKeyName(),
            'orderable'  => true,
            'searchable' => true,
            'class'      => 'dt-col-index',
        ]] : [];

        $editableColumns = $dataType->editRows->pluck('field')->toArray();

        $browseColumns = $dataType->browseRows->map(
            function (DataRow $row) use ($editableColumns, $searchableColumns, $sortableColumns) {
                $editable = in_array($row->field, $editableColumns);
                return [
                    'data'       => $row->field,
                    'name'       => $row->field,
                    'orderable'  => in_array($row->field, $sortableColumns),
                    'searchable' => in_array($row->field, $searchableColumns),
                    'class'      => implode(' ', ['dt-col-' . $row->field, $editable ? 'dt-col-editable' : '']),
                ];
            }
        )->toArray();

        $actionColumns = [[
            'data'       => 'actions',
            'name'       => 'actions',
            'orderable'  => false,
            'searchable' => false,
            'class'      => 'no-sort no-click bread-actions dt-col-actions',
        ]];

        return array_merge($columns, $browseColumns, $actionColumns);
    }
}

if (!function_exists('dataTypeRawColumns')) {
    /**
     * Raw columns
     */
    function dataTypeRawColumns(DataType $dataType, bool $showCheckboxColumn): array
    {
        $model = app($dataType->model_name);

        $columns = $showCheckboxColumn ? ['index'] : [];

        $browseColumns = $dataType->browseRows->filter(function (DataRow $row) use ($model) {
            return dataRowsMayHaveHtml($model, $row);
        })->map(function (DataRow $row) {
            return $row->field;
        })->toArray();

        $actionColumns = ['actions'];

        return array_merge($columns, $browseColumns, $actionColumns);
    }
}

if (!function_exists('dataRowsMayHaveHtml')) {
    /**
     * May have html
     */
    function dataRowsMayHaveHtml(Model $model, DataRow $row): bool
    {
        if (isset($row->details->view)) {
            return true;
        }

        if ($row->type == 'image') {
            return true;
        }

        if ($row->type == 'relationship') {
            return true;
        }

        if ($row->type == 'select_multiple') {
            return false;
        }

        if (
            $row->type == 'multiple_checkbox'
            && property_exists($row->details, 'options')
        ) {
            return false;
        }

        if (
            ($row->type == 'select_dropdown' || $row->type == 'radio_btn')
            && property_exists($row->details, 'options')
        ) {
            return false;
        }

        if ($row->type == 'date' || $row->type == 'timestamp') {
            return false;
        }

        if ($row->type == 'checkbox') {
            return false;
        }

        if ($row->type == 'color') {
            return true;
        }

        if ($row->type == 'text') {
            return !!is_field_translatable($model, $row);
        }

        if ($row->type == 'text_area') {
            return !!is_field_translatable($model, $row);
        }

        if ($row->type == 'file') {
            return true;
        }

        if ($row->type == 'rich_text_box') {
            return true;
        }

        if ($row->type == 'coordinates') {
            return true;
        }

        if ($row->type == 'multiple_images') {
            return true;
        }

        if ($row->type == 'media_picker') {
            return true;
        }

        return !!is_field_translatable($model, $row);
    }
}

if (!function_exists('modelHasScope')) {
    /**
     * May have html
     *
     * @param Model|string $model
     */
    function modelHasScope($model, string $scope): bool
    {
        return method_exists($model, 'scope' . Str::studly($scope));
    }
}
