<?php

namespace Joy\VoyagerDatatable\Lenses;

use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;

class AdministratorLens extends AbstractLens
{
    public function getRouteKey()
    {
        return 'administrator';
    }

    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.administrator');
    }

    public function getIcon()
    {
        return 'fa-solid fa-user-secret';
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'class'   => 'administrator-lens',
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
        return $this->dataType->slug === 'users';
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
        $role = Voyager::model('Role')->where('name', 'admin')->firstOrFail();
        $query->where(function ($query) use ($role) {
            $query
                ->whereRoleId($role->id)
                ->orWhereHas('roles', function ($query) use ($role) {
                    $query->whereId($role->id);
                });
        });
    }
}
