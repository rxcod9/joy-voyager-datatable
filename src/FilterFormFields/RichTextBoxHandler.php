<?php

namespace Joy\VoyagerDatatable\FilterFormFields;

class RichTextBoxHandler extends AbstractHandler
{
    protected $codename = 'rich_text_box';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('joy-voyager-datatable::filterformfields.rich_text_box', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }
}
