<?php

namespace Joy\VoyagerDatatable\FilterFormFields;

class NumberHandler extends AbstractHandler
{
    protected $codename = 'number';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('joy-voyager-datatable::filterformfields.number', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }
}
