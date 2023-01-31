<?php

namespace Joy\VoyagerDatatable\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use TCG\Voyager\Models\DataType;

class GlobalFilterColumns
{
    protected GlobalFilter $globalFilter;

    public function __construct(
        GlobalFilter $globalFilter
    ) {
        $this->globalFilter = $globalFilter;
    }

    /**
     * Handle service
     *
     * @param Builder|QueryBuilder $query   Query
     * @param mixed                $keyword Keyword
     */
    public function handle(
        $query,
        $keyword,
        DataType $dataType,
        Request $request,
        array $columns = []
    ): void {
        $rows = $columns ? $dataType->rows->filter(function ($row) use ($columns) {
            return in_array($row->field, $columns) ||
                (optional($row->details)->column && in_array(optional($row->details)->column, $columns));
        }) : $dataType->browseRows;

        $searchableRows = $this->filterSearchableColumns($rows);

        $query->where(function ($query) use ($keyword, $dataType, $request, $searchableRows) {
            foreach ($searchableRows as $row) {
                $query->orWhere(function ($query) use ($keyword, $dataType, $request, $row) {
                    $this->globalFilter->handle($query, $keyword, $row, $dataType, $request);
                });
            }
        });
    }

    protected function filterSearchableColumns($rows)
    {
        return $rows->filter(function ($item) {
            if ($item->type != 'relationship') {
                return true;
            }
            if (!in_array($item->details->type, [
                'belongsTo',
                'belongsToMany',
                'morphTo',
            ])) {
                return false;
            }

            // @todo enable/disable from config

            return !$this->relationIsUsingAccessorAsLabel($item->details);
        });
    }

    protected function relationIsUsingAccessorAsLabel($details)
    {
        return ($details->model ?? null) && in_array($details->label, app($details->model)->additional_attributes ?? []);
    }
}
