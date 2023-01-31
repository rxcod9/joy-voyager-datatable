<?php

namespace Joy\VoyagerDatatable\Lenses;

use Illuminate\Http\Request;

class RecentlyCreated extends AbstractLens
{
    public function getRouteKey()
    {
        return 'recently-created';
    }

    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.recently_created');
    }

    public function getIcon()
    {
        return 'fa-solid fa-user';
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'class'   => 'recently-created-lens',
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
        return $this->dataType->rows->where('field', 'created_at')->first();
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
        $query->whereBetween($this->dataType->name . '.created_at', [now()->subDays(7)->startOfDay(), now()]);
    }
}
