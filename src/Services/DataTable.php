<?php

namespace Joy\VoyagerDatatable\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TCG\Voyager\Models\DataType;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\DataTables;

class DataTable
{
    protected Column $column;
    protected Filter $filter;

    public function __construct(
        Column $column,
        Filter $filter
    ) {
        $this->column = $column;
        $this->filter = $filter;
    }

    /**
     * Handle
     *
     * @param Builder|QueryBuilder $query Query
     *
     * @return mixed
     */
    public function handle(
        Request $request,
        $query,
        DataType $dataType,
        bool $showCheckboxColumn,
        array $actions,
        bool $isModelTranslatable
    ) {
        $dataTable = DataTables::of($query);

        $this->columns(
            $dataTable,
            $query,
            $dataType,
            $showCheckboxColumn,
            $actions,
            $isModelTranslatable,
        );

        $this->search(
            $request,
            $dataTable,
            $query,
            $dataType,
            $showCheckboxColumn,
            $actions,
            $isModelTranslatable,
        );

        $this->filters(
            $request,
            $dataTable,
            $query,
            $dataType,
            $showCheckboxColumn,
            $actions,
            $isModelTranslatable,
        );

        return $dataTable->make(true);
    }

    /**
     * Columns
     *
     * @param Builder|QueryBuilder $query Query
     */
    public function columns(
        DataTableAbstract $dataTable,
        $query,
        DataType $dataType,
        bool $showCheckboxColumn,
        array $actions,
        bool $isModelTranslatable
    ): void {
        if ($showCheckboxColumn) {
            $dataTable->addColumn('index', function ($data) use ($dataType) {
                return $this->column->indexColumn($data, $dataType);
            });
        }

        foreach ($dataType->browseRows as $row) {
            $dataTable->addColumn($row->field, function ($data) use ($row, $dataType) {
                $content = $data->{$row->field};
                if ($data->{$row->field . '_browse'}) {
                    $data->{$row->field} = $content = $data->{$row->field . '_browse'};
                }
                return $this->column->handle($row, $data, $dataType, $content);
            });
        }

        $dataTable->addColumn('action', function ($data) use ($dataType, $actions) {
            return $this->column->actions($data, $dataType, $actions);
        });

        $rawColumns = dataTypeRawColumns($dataType, $showCheckboxColumn);

        $dataTable->rawColumns($rawColumns, true);
    }

    /**
     * Search
     *
     * @param Builder|QueryBuilder $query Query
     */
    public function search(
        Request $request,
        DataTableAbstract $dataTable,
        $query,
        DataType $dataType,
        bool $showCheckboxColumn,
        array $actions,
        bool $isModelTranslatable
    ): void {
        $modelClass = $dataType->model_name;

        // Note:: Your model must implement scopeGlobalSearch
        // which will be used to filter data by global search
        if (!modelHasScope($modelClass, 'globalSearch')) {
            Log::debug('Your model must implement scopeGlobalSearch');
            $model = app($dataType->model_name);
            $dataTable->filter(function ($query) use ($request, $model) {
                if ($request->has('search.value') && $request->input('search.value')) {
                    switch ($model->getKeyType()) {
                        case 'int':
                            $query->whereKey((int) $request->input('search.value'));
                            break;
                        case 'string':
                            $query->whereKey($request->input('search.value'));
                            break;

                        default:
                            // code...
                            break;
                    }
                }
            });
            return;
        }

        $dataTable->filter(function ($query) use ($request) {
            if ($request->has('search.value') && $request->input('search.value')) {
                $query->globalSearch($request->input('search.value'));
            }
        });
    }

    /**
     * Filters
     *
     * @param Builder|QueryBuilder $query Query
     */
    public function filters(
        Request $request,
        DataTableAbstract $dataTable,
        $query,
        DataType $dataType,
        bool $showCheckboxColumn,
        array $actions,
        bool $isModelTranslatable
    ): void {
        foreach ($dataType->browseRows as $row) {
            $modelClass = $dataType->model_name;

            // Note:: you can override filter for each column by adding scope{field}
            // i.e. for age column you can add scopeAge and for created_at you can add scopeCreatedAt
            if (modelHasScope($modelClass, $row->field)) {
                $dataTable->filterColumn($row->field, function ($query, $keyword) use ($row, $dataType, $request): void {
                    if ($keyword) {
                        $query->scopes([
                            Str::camel($row->field) => [$keyword],
                        ]);
                    }
                });
            }

            $dataTable->filterColumn($row->field, function ($query, $keyword) use ($row, $dataType, $request): void {
                if ($keyword) {
                    $this->filter->handle($query, $keyword, $row, $dataType, $request);
                }
            });
        };
    }
}
