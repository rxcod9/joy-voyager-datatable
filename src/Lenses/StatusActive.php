<?php

namespace Joy\VoyagerDatatable\Lenses;

use Illuminate\Http\Request;

class StatusActive extends AbstractLens
{
    public function getRouteKey()
    {
        return 'status-active';
    }

    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.status_active');
    }

    public function getIcon()
    {
        return 'fas fa-toggle-on';
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'class'   => 'status-active-lens',
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
        $row = $this->dataType->rows->where('field', 'status')->first();
        if (!$row) {
            return false;
        }
        $keys   = array_keys((array) optional($row->details)->options ?? []);
        $values = array_values((array) optional($row->details)->options ?? []);
        return in_array('Active', $keys) || in_array('Active', $values);
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
        $row = $this->dataType->rows->where('field', 'status')->first();
        if (!$row) {
            return false;
        }

        $options = (array) optional($row->details)->options ?? [];
        $keys    = array_keys($options);
        $values  = array_values($options);

        if (in_array('Active', $keys)) {
            $query->whereStatus('Active');
            return;
        }

        if (in_array('Active', $values)) {
            $key = array_search('Active', $options);
            $query->whereStatus($key);
            return;
        }
    }
}
