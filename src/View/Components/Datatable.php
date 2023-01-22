<?php

namespace Joy\VoyagerDatatable\View\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;
use TCG\Voyager\Facades\Voyager;
use Joy\VoyagerCore\Http\Controllers\Traits\BreadRelationshipParser;
use TCG\Voyager\Models\DataType;

class Datatable extends Component
{
    use BreadRelationshipParser;

    /**
     * The request.
     *
     * @var Request
     */
    protected $request;

    /**
     * The slug.
     *
     * @var string
     */
    protected $slug;

    /**
     * The withLabel.
     *
     * @var bool|null
     */
    protected $withLabel;

    /**
     * The autoWidth.
     *
     * @var bool
     */
    protected $autoWidth;

    /**
     * The columnDefs.
     *
     * @var array
     */
    protected $columnDefs;

    /**
     * The withoutCheckbox.
     *
     * @var bool|null
     */
    protected $withoutCheckbox;

    /**
     * The withoutActions.
     *
     * @var bool|null
     */
    protected $withoutActions;

    /**
     * The dataId.
     *
     * @var string|null
     */
    protected $dataId;

    /**
     * Create the component instance.
     *
     * @param Request     $request
     * @param string      $slug
     * @param bool|null   $withLabel
     * @param bool        $autoWidth
     * @param array       $columnDefs
     * @param bool|null   $withoutCheckbox
     * @param bool|null   $withoutActions
     * @param string|null $dataId
     *
     * @return void
     */
    public function __construct(
        Request $request,
        string $slug,
        ?bool $withLabel = null,
        ?bool $autoWidth = false,
        ?array $columnDefs = [],
        ?bool $withoutCheckbox = null,
        ?bool $withoutActions = null,
        ?string $dataId = null
    ) {
        $this->request         = $request;
        $this->slug            = $slug;
        $this->withLabel       = $withLabel;
        $this->autoWidth       = $autoWidth;
        $this->columnDefs      = $columnDefs;
        $this->withoutCheckbox = $withoutCheckbox;
        $this->withoutActions  = $withoutActions;
        $this->dataId          = $dataId;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $this->slug)->first();

        // Check permission
        // $this->authorize('browse', app($dataType->model_name));

        $orderBy         = $this->request->get('order_by', $dataType->order_column);
        $sortOrder       = $this->request->get('sort_order', $dataType->order_direction);
        $usesSoftDeletes = false;
        $showSoftDeleted = false;

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            // Use withTrashed() if model uses SoftDeletes and if toggle is selected
            if ($model && in_array(SoftDeletes::class, class_uses_recursive($model)) && Auth::user()->can('delete', app($dataType->model_name))) {
                $usesSoftDeletes = true;

                if ($this->request->get('showSoftDeleted')) {
                    $showSoftDeleted = true;
                }
            }

            // If a column has a relationship associated with it, we do not want to show that field
            $this->removeRelationshipField($dataType, 'browse');
        } else {
            $model = false;
        }

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($model);

        // Actions
        $actions = [];

        foreach (Voyager::actions() as $action) {
            $action = new $action($dataType, $model);

            if ($action->shouldActionDisplayOnDataType()) {
                $actions[] = $action;
            }
        }

        // Define showCheckboxColumn
        $showCheckboxColumn = false;
        if (Auth::user()->can('delete', app($dataType->model_name))) {
            $showCheckboxColumn = true;
        } else {
            foreach ($actions as $action) {
                if (method_exists($action, 'massAction')) {
                    $showCheckboxColumn = true;
                }
            }
        }

        // Define orderColumn
        $orderColumn = [];
        if ($orderBy) {
            $index       = $dataType->browseRows->where('field', $orderBy)->keys()->first() + ($showCheckboxColumn ? 1 : 0);
            $orderColumn = [[$index, $sortOrder ?? 'desc']];
        }

        // Define list of columns that can be sorted server side
        $searchableColumns = $this->getSearchableColumns($dataType->browseRows);
        $sortableColumns   = $this->getSortableColumns($dataType->browseRows);

        $view = 'joy-voyager-datatable::components.datatable';

        if (view()->exists('joy-voyager-datatable::' . $this->slug . '.components.datatable')) {
            $view = 'joy-voyager-datatable::' . $this->slug . '.components.datatable';
        }

        // Filter
        $filterDataTypeContent = (strlen($dataType->model_name) != 0)
                            ? new $dataType->model_name()
                            : false;

        $filterRows = $dataType->rows;
        foreach ($filterRows as $key => $row) {
            $filterRows[$key]['col_width'] = 100;
        }

        // Eagerload Relations
        $this->eagerLoadRelations($filterDataTypeContent, $dataType, 'browse', $isModelTranslatable);

        return Voyager::view($view, [
            'actions'               => $actions,
            'dataType'              => $dataType,
            'filterDataTypeContent' => $filterDataTypeContent,
            'isModelTranslatable'   => $isModelTranslatable,
            'orderBy'               => $orderBy,
            'orderColumn'           => $orderColumn,
            'searchableColumns'     => $searchableColumns,
            'sortableColumns'       => $sortableColumns,
            'sortOrder'             => $sortOrder,
            'usesSoftDeletes'       => $usesSoftDeletes,
            'showSoftDeleted'       => $showSoftDeleted,
            'showCheckboxColumn'    => $showCheckboxColumn,
            'withLabel'             => $this->withLabel,
            'autoWidth'             => $this->autoWidth,
            'columnDefs'            => $this->columnDefs,
            'withoutCheckbox'       => $this->withoutCheckbox,
            'withoutActions'        => $this->withoutActions,
            'dataId'                => $this->dataId,
        ]);
    }

    protected function getSearchableColumns($rows)
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
        })
        ->pluck('field')
        ->toArray();
    }

    protected function getSortableColumns($rows)
    {
        return $rows->filter(function ($item) {
            if ($item->type != 'relationship') {
                return true;
            }
            if ($item->details->type != 'belongsTo') {
                return false;
            }

            return !$this->relationIsUsingAccessorAsLabel($item->details);
        })
        ->pluck('field')
        ->toArray();
    }

    protected function relationIsUsingAccessorAsLabel($details)
    {
        return ($details->model ?? null) && in_array($details->label, app($details->model)->additional_attributes ?? []);
    }
}
