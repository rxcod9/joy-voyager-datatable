<?php

namespace Joy\VoyagerDatatable\Http\Controllers;

use Joy\VoyagerDatatable\Http\Traits\AjaxAction;
use Joy\VoyagerDatatable\Http\Traits\IndexAction;
use Joy\VoyagerDatatable\Http\Traits\PreviewAction;
use Joy\VoyagerDatatable\Http\Traits\QuickCreateAction;
use Joy\VoyagerDatatable\Http\Traits\QuickEditAction;
use Joy\VoyagerCore\Http\Controllers\VoyagerBaseController as BaseVoyagerBaseController;
use Joy\VoyagerDatatable\Http\Traits\InlineEditAction;
use Joy\VoyagerDatatable\Http\Traits\QuickDeleteAction;

class VoyagerBaseController extends BaseVoyagerBaseController
{
    use IndexAction;
    use AjaxAction;
    use PreviewAction;
    use QuickCreateAction;
    use QuickEditAction;
    use InlineEditAction;
    use QuickDeleteAction;
}
