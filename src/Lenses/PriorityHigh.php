<?php

namespace Joy\VoyagerDatatable\Lenses;

use Illuminate\Http\Request;

class PriorityHigh extends AbstractLens
{
    public function getRouteKey()
    {
        return 'priority-high';
    }

    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.priority_high');
    }

    public function getIcon()
    {
        return 'fa-solid fa-star';
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'class'   => 'priority-high-lens',
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
        $row = $this->dataType->rows->where('field', 'priority')->first();
        if (!$row) {
            return false;
        }
        $keys   = array_keys((array) optional($row->details)->options ?? []);
        $values = array_values((array) optional($row->details)->options ?? []);
        return in_array('High', $keys) || in_array('High', $values);
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
        $row = $this->dataType->rows->where('field', 'priority')->first();
        if (!$row) {
            return false;
        }

        $options = (array) optional($row->details)->options ?? [];
        $keys    = array_keys($options);
        $values  = array_values($options);

        if (in_array('High', $keys)) {
            $query->wherePriority('High');
            return;
        }

        if (in_array('High', $values)) {
            $key = array_search('High', $options);
            $query->wherePriority($key);
            return;
        }
    }
}
