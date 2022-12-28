<?php

namespace Joy\VoyagerDatatable\Http\Controllers;

use Joy\VoyagerDatatable\Http\Traits\AjaxAction;
use Joy\VoyagerDatatable\Http\Traits\IndexAction;
use Joy\VoyagerDatatable\Http\Traits\PreviewAction;
use Joy\VoyagerDatatable\Http\Traits\QuickCreateAction;
use Joy\VoyagerDatatable\Http\Traits\QuickEditAction;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as TCGVoyagerBaseController;

class VoyagerBaseController extends TCGVoyagerBaseController
{
    use IndexAction;
    use AjaxAction;
    use PreviewAction;
    use QuickCreateAction;
    use QuickEditAction;
}
