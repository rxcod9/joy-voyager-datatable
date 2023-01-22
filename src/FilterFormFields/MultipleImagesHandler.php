<?php

namespace Joy\VoyagerDatatable\FilterFormFields;

class MultipleImagesHandler extends AbstractHandler
{
    protected $codename = 'multiple_images';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('joy-voyager-datatable::filterformfields.multiple_images', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }
}
