<?php

namespace Joy\VoyagerDatatable\Events;

use Illuminate\Queue\SerializesModels;

class FilterFormFieldsRegistered
{
    use SerializesModels;

    public $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;

        // @deprecate
        //
        event('voyager.filter-form-fields.registered', $fields);
    }
}
