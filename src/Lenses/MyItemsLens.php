<?php

namespace Joy\VoyagerDatatable\Lenses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyItemsLens extends AbstractLens
{
    public function getRouteKey()
    {
        return 'my-items';
    }

    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.my_items');
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
            'class'   => 'my-items-lens',
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
        $hasCreatedBy  = $this->dataType->rows->where('field', 'created_by_id')->first();
        $hasModifiedBy = $this->dataType->rows->where('field', 'modified_by_id')->first();
        $query
            ->where($this->dataType->name . '.assigned_to_id', Auth::id())
            ->when($hasCreatedBy, function ($query) {
                $query->orWhere($this->dataType->name . '.created_by_id', Auth::id());
            })
            ->when($hasModifiedBy, function ($query) {
                $query->orWhere($this->dataType->name . '.modified_by_id', Auth::id());
            });
    }
}
