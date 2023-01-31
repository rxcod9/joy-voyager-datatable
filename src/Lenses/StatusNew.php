<?php

namespace Joy\VoyagerDatatable\Lenses;

use Illuminate\Http\Request;

class StatusNew extends AbstractLens
{
    public function getRouteKey()
    {
        return 'status-new';
    }

    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.status_new');
    }

    public function getIcon()
    {
        return 'fa-solid fa-sparkles';
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'class'   => 'status-new-lens',
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
        return in_array('New', $keys) || in_array('New', $values);
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

        if (in_array('New', $keys)) {
            $query->whereStatus('New');
            return;
        }

        if (in_array('New', $values)) {
            $key = array_search('New', $options);
            $query->whereStatus($key);
            return;
        }
    }
}
