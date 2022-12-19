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
     * The dataId.
     *
     * @var string|null
     */
    protected $dataId;

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
     * The withLabel.
     *
     * @var bool|null
     */
    protected $withLabel;

    /**
     * Create the component instance.
     *
     * @param Request     $request
     * @param array       $slugs
     * @param bool|null   $withoutCheckbox
     * @param bool|null   $withoutActions
     * @param bool|null   $withLabel
     * @param string|null $dataId
     *
     * @return void
     */
    public function __construct(
        Request $request,
        array $slugs = [],
        ?bool $withoutCheckbox = true,
        ?bool $withoutActions = true,
        ?bool $withLabel = true,
        ?string $dataId = null
    ) {
        $this->request         = $request;
        $this->slugs           = $slugs;
        $this->withoutCheckbox = $withoutCheckbox;
        $this->withoutActions  = $withoutActions;
        $this->withLabel       = $withLabel;
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
            'withoutCheckbox' => $this->withoutCheckbox,
            'withoutActions'  => $this->withoutActions,
            'withLabel'       => $this->withLabel,
            'dataId'          => $this->dataId,
        ]);
    }
}
