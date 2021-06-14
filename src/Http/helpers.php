<?php

use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;

if (! function_exists('abcAbcXyz')) {
    /**
     * Helper to get dataType dataTable columns.
     */
    function abcAbcXyz(DataType $dataType): array
    {
        return $dataType->browseRows->map(function (DataRow $row) {
            return [
                'data' => $row->field,
                'name' => $row->field,
                // 'orderable' => false,
                // 'searchable' => false
            ];
        });
    }
}
