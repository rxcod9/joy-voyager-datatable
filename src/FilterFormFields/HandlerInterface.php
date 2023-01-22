<?php

namespace Joy\VoyagerDatatable\FilterFormFields;

interface HandlerInterface
{
    public function handle($row, $dataType, $dataTypeContent);

    public function createContent($row, $dataType, $dataTypeContent, $options);

    public function supports($driver);

    public function getCodename();

    public function getName();
}
