<?php

namespace Joy\VoyagerDatatable\FilterFormFields;

class HiddenHandler extends AbstractHandler
{
    protected $codename = 'hidden';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('joy-voyager-datatable::filterformfields.hidden', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }
}
