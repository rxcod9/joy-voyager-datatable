<?php

namespace Joy\VoyagerDatatable\Http\Controllers;

use Joy\VoyagerDatatable\Http\Traits\AjaxAction;
use Joy\VoyagerDatatable\Http\Traits\IndexAction;
use TCG\Voyager\Http\Controllers\VoyagerUserController as TCGVoyagerUserController;

class VoyagerUserController extends TCGVoyagerUserController
{
    use IndexAction;
    use AjaxAction;
}
