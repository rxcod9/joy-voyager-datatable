<?php

namespace Joy\VoyagerDatatable\Lenses;

abstract class AbstractLens implements LensInterface
{
    protected $dataType;
    protected $data;

    public function __construct($dataType, $data)
    {
        $this->dataType = $dataType;
        $this->data     = $data;
    }

    public function getDataType()
    {
    }

    public function getPolicy()
    {
    }

    public function getRoute($key)
    {
        if (method_exists($this, $method = 'get' . ucfirst($key) . 'Route')) {
            return $this->$method();
        } else {
            return $this->getDefaultRoute();
        }
    }

    public function getAttributes()
    {
        return [];
    }

    public function convertAttributesToHtml()
    {
        $result = '';

        foreach ($this->getAttributes() as $key => $attribute) {
            $result .= $key . '="' . $attribute . '"';
        }

        return $result;
    }

    public function shouldLensDisplayOnDataType()
    {
        return $this->dataType->name === $this->getDataType() || $this->getDataType() === null;
    }

    public function applyScope($query)
    {
        //
    }
}
