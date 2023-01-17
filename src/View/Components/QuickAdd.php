<?php

namespace Joy\VoyagerDatatable\View\Components;

use Illuminate\Http\Request;
use Illuminate\View\Component;
use TCG\Voyager\Facades\Voyager;
use Joy\VoyagerCore\Http\Controllers\Traits\BreadRelationshipParser;

class QuickAdd extends Component
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
     * @param string|null $dataId
     *
     * @return void
     */
    public function __construct(
        Request $request,
        string $slug,
        ?string $dataId = null
    ) {
        $this->request = $request;
        $this->slug    = $slug;
        $this->dataId  = $dataId;
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

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
        } else {
            $model = false;
        }

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($model);

        $view = 'joy-voyager-datatable::components.quick-add';

        if (view()->exists('joy-voyager-datatable::' . $this->slug . '.components.quick-add')) {
            $view = 'joy-voyager-datatable::' . $this->slug . '.components.quick-add';
        }

        return Voyager::view($view, [
            'dataType'            => $dataType,
            'isModelTranslatable' => $isModelTranslatable,
            'dataId'              => $this->dataId,
        ]);
    }
}
