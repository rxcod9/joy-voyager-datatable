<?php

namespace Joy\VoyagerDatatable\Lenses;

use Illuminate\Http\Request;

class UnassignedLens extends AbstractLens
{
    public function getRouteKey()
    {
        return 'unassigned';
    }

    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.unassigned');
    }

    public function getIcon()
    {
        return 'voyager-people';
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'class'   => 'unassigned-lens',
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
        return $this->dataType->rows->where('field', 'assigned_to_id')->first();
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
        $query->whereNull('assigned_to_id');
    }
}
