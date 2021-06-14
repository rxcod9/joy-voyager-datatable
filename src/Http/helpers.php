<?php

use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;

if (! function_exists('dataTypeTableColumns')) {
    /**
     * Helper to get dataType dataTable columns.
     */
    function dataTypeTableColumns(DataType $dataType, bool $showCheckboxColumn): array
    {
        $columns = $showCheckboxColumn ? [[
            'data' => 'id',
            'name' => 'id',
        ]] : [];
        $browseColumns = $dataType->browseRows->map(function (DataRow $row) {
            return [
                'data' => $row->field,
                'name' => $row->field,
                // 'orderable' => false,
                // 'searchable' => false
            ];
        })->toArray();

        $actionColumns = [[
            'data' => 'action',
            'name' => 'action',
        ]];

        return array_merge($columns, $browseColumns, $actionColumns);
    }
}
