<?php

namespace Joy\VoyagerDatatable;

use Illuminate\Support\Facades\DB;
use TCG\Voyager\Models\DataType;
use Yajra\DataTables\EloquentDataTable;

class DataTypeDataTable extends EloquentDataTable
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * Can the DataTable engine be created with these parameters.
     *
     * @param mixed $source
     * @return bool
     */
    public static function canCreate($source)
    {
        return $source instanceof DataType;
    }

    /**
     * EloquentEngine constructor.
     *
     * @param mixed $model
     */
    public function __construct($dataType)
    {
        $builder = (strlen($dataType->model_name) != 0) ? app($dataType->model_name) : DB::table($dataType->name);
        parent::__construct(app($dataType->model_name));

        $this->query = $builder;
    }
}
