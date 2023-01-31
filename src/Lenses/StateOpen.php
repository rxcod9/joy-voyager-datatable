<?php

namespace Joy\VoyagerDatatable\Lenses;

use Illuminate\Http\Request;

class StateOpen extends AbstractLens
{
    public function getRouteKey()
    {
        return 'state-open';
    }

    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.state_open');
    }

    public function getIcon()
    {
        return 'fa-solid fa-lock-open';
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'class'   => 'state-open-lens',
            'data-id' => $this->data->getKey(),
        ];
    }

    public function getDefaultRoute()
    {
        return route('voyager.' . $this->dataType->slug . '.index', [
            'lense' => $this->getRouteKey()
        ]);
    }

    public function shouldLensDisplayOnDataType()
    {
        $row = $this->dataType->rows->where('field', 'state')->first();
        if (!$row) {
            return false;
        }
        $keys   = array_keys((array) optional($row->details)->options ?? []);
        $values = array_values((array) optional($row->details)->options ?? []);
        return in_array('Open', $keys) || in_array('Open', $values);
    }

    protected function getSlug(Request $request)
    {
        if (isset($this->slug)) {
            $slug = $this->slug;
        } else {
            $slug = explode('.', $request->route()->getName())[1];
        }

        return $slug;
    }

    public function applyScope($query)
    {
        $row = $this->dataType->rows->where('field', 'state')->first();
        if (!$row) {
            return false;
        }

        $options = (array) optional($row->details)->options ?? [];
        $keys    = array_keys($options);
        $values  = array_values($options);

        if (in_array('Open', $keys)) {
            $query->where($this->dataType->name . '.state', 'Open');
            return;
        }

        if (in_array('Open', $values)) {
            $key = array_search('Open', $options);
            $query->where($this->dataType->name . '.state', $key);
            return;
        }
    }
}
