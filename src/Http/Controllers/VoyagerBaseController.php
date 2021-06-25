<?php

namespace Joy\VoyagerDatatable\Http\Controllers;

use Joy\VoyagerDatatable\Http\Traits\AjaxAction;
use Joy\VoyagerDatatable\Http\Traits\IndexAction;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as TCGVoyagerBaseController;

class VoyagerBaseController extends TCGVoyagerBaseController
{
    use IndexAction;
    use AjaxAction;
}
