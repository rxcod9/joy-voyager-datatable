<?php

namespace Joy\VoyagerDatatable\View\Components;

use Illuminate\Http\Request;
use Illuminate\View\Component;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\DataType;

class Datatables extends Component
{
    /**
     * The request.
     *
     * @var Request
     */
    protected $request;

    /**
     * The slugs.
     *
     * @var array
     */
    protected $slugs;

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
     * @param array       $slugs
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
        array $slugs = [],
        ?bool $withLabel = true,
        ?bool $autoWidth = false,
        ?array $columnDefs = [],
        ?bool $withoutCheckbox = true,
        ?bool $withoutActions = true,
        ?string $dataId = null
    ) {
        $this->request         = $request;
        $this->slugs           = $slugs;
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
        $dataTypes = Voyager::model('DataType')->when($this->slugs, function ($query) {
            $query->whereIn('slug', $this->slugs);
        })->get();

        $view = 'joy-voyager-datatable::components.datatables';

        return Voyager::view($view, [
            'dataTypes'       => $dataTypes,
            'slugs'           => $this->slugs,
            'withLabel'       => $this->withLabel,
            'autoWidth'       => $this->autoWidth,
            'columnDefs'      => $this->columnDefs,
            'withoutCheckbox' => $this->withoutCheckbox,
            'withoutActions'  => $this->withoutActions,
            'dataId'          => $this->dataId,
        ]);
    }
}
