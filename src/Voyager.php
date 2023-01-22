<?php

namespace Joy\VoyagerDatatable;

use Joy\VoyagerDatatable\FilterFormFields\After\HandlerInterface as AfterHandlerInterface;
use Joy\VoyagerDatatable\FilterFormFields\HandlerInterface;
use TCG\Voyager\Voyager as BaseVoyager;

class Voyager extends BaseVoyager
{
    protected $filterFormFields      = [];
    protected $afterFilterFormFields = [];

    protected $lenses = [
        // MyItemsLense::class,
    ];

    // /**
    //  * Composition
    //  *
    //  * @var BaseVoyager
    //  */
    // protected $parent;

    // /**
    //  * @param BaseVoyager $parent
    //  */
    // public function __construct(BaseVoyager $parent)
    // {
    //     $this->parent = $parent;
    // }

    // /**
    //  * NOTE: This is done to support composition
    //  * @param $name
    //  * @param $arguments
    //  * @return mixed
    //  */
    // public function __call($name, $arguments)
    // {
    //     return $this->parent->$name(...$arguments);
    // }

    public function filterFormField($row, $dataType, $dataTypeContent)
    {
        if (!($this->filterFormFields[$row->type] ?? null)) {
            return;
        }
        $filterFormField = $this->filterFormFields[$row->type];

        return $filterFormField->handle($row, $dataType, $dataTypeContent);
    }

    public function afterFilterFormFields($row, $dataType, $dataTypeContent)
    {
        return collect($this->afterFilterFormFields)->filter(function ($after) use ($row, $dataType, $dataTypeContent) {
            return $after->visible($row, $dataType, $dataTypeContent, $row->details);
        });
    }

    public function canFilterFormField($row, $dataType, $dataTypeContent)
    {
        if (
            !($this->filterFormFields[$row->type] ?? null) &&
            $row->type !== 'relationship'
        ) {
            return false;
        }

        // You can disable filters by row type
        if (in_array($row->type, config('joy-voyager-datatable.filters.type_hidden', []))) {
            return false;
        }

        // You can disable filters by row field globally
        if (in_array($row->field, config('joy-voyager-datatable.filters.hidden', []))) {
            return false;
        }

        // You can disable filters by row field for different data types
        if (in_array($row->field, config('joy-voyager-datatable.' . $dataType->slug . '.filters.hidden', []))) {
            return false;
        }

        if ($row->type === 'relationship') {
            if (!in_array($row->details->type, [
                'belongsTo',
                // 'hasOne',
                // 'hasMany',
                // 'belongsToMany',
                'morphTo',
            ])) {
                return false;
            }
        }

        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            return !in_array($row->field, $model->getHidden());
        }

        return true;
    }

    public function addFilterFormField($handler)
    {
        if (!$handler instanceof HandlerInterface) {
            $handler = app($handler);
        }

        $this->filterFormFields[$handler->getCodename()] = $handler;

        return $this;
    }

    public function addAfterFilterFormField($handler)
    {
        if (!$handler instanceof AfterHandlerInterface) {
            $handler = app($handler);
        }

        $this->afterFilterFormFields[$handler->getCodename()] = $handler;

        return $this;
    }

    public function filterFormFields()
    {
        $connection = config('database.default');
        $driver     = config("database.connections.{$connection}.driver", 'mysql');

        return collect($this->filterFormFields)->filter(function ($after) use ($driver) {
            return $after->supports($driver);
        });
    }

    public function addLense($lense)
    {
        array_push($this->lenses, $lense);
    }

    public function replaceLense($lenseToReplace, $lense)
    {
        $key                = array_search($lenseToReplace, $this->lenses);
        $this->lenses[$key] = $lense;
    }

    public function lenses()
    {
        return $this->lenses;
    }
}
