<?php

namespace Joy\VoyagerDatatable\Lenses;

interface LensInterface
{
    public function getRouteKey();

    public function getTitle();

    public function getIcon();

    public function getPolicy();

    public function getAttributes();

    public function getRoute($key);

    public function getDefaultRoute();

    public function getDataType();

    public function applyScope($query);
}
