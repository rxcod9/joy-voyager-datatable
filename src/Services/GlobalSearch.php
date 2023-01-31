<?php

namespace Joy\VoyagerDatatable\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use TCG\Voyager\Models\DataType;

class GlobalSearch
{
    protected GlobalFilterColumns $globalFilterColumns;

    public function __construct(
        GlobalFilterColumns $globalFilterColumns
    ) {
        $this->globalFilterColumns = $globalFilterColumns;
    }

    /**
     * Handle
     *
     * @param Builder|QueryBuilder $query Query
     */
    public function handle(
        $query,
        $keyword,
        DataType $dataType,
        Request $request
    ): void {
        $defaultColumn = config('joy-voyager-datatable.global_search.default_column', 'id');
        $column        = config('joy-voyager-datatable.global_search.' . $dataType->slug . '.default_column', $defaultColumn);

        if ($column === 'all') {
            $this->globalFilterColumns->handle(
                $query,
                $keyword,
                $dataType,
                $request
            );
            return;
        }

        $columns = explode(',', $column);
        $this->globalFilterColumns->handle(
            $query,
            $keyword,
            $dataType,
            $request,
            $columns
        );
        return;
    }
}
