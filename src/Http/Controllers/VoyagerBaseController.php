<?php

namespace Joy\VoyagerDatatable\Http\Controllers;

use Joy\VoyagerDatatable\Traits\AjaxAction;
use Joy\VoyagerDatatable\Traits\IndexAction;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as TCGVoyagerBaseController;

class VoyagerBaseController extends TCGVoyagerBaseController
{
    use IndexAction;
    use AjaxAction;
}
