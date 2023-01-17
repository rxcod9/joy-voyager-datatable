<?php

namespace Joy\VoyagerDatatable\Actions;

use Illuminate\Http\Request;
use TCG\Voyager\Actions\AbstractAction;

class QuickDeleteAction extends AbstractAction
{
    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.quick_delete_btn');
    }

    public function getIcon()
    {
        return 'voyager-trash';
    }

    public function getPolicy()
    {
        return 'delete';
    }

    public function getAttributes()
    {
        return [
            'class'             => 'btn btn-sm btn-warning pull-right quick-delete',
            'target'            => '_blank',
            'data-id'           => $this->data->getKey(),
            'data-loading-text' => "<i class='fa fa-spinner fa-spin'></i> <span class='hidden-xs hidden-sm'>" . $this->getTitle() . '...</span>',
        ];
    }

    public function getDefaultRoute()
    {
        return route('voyager.' . $this->dataType->slug . '.quick-delete', $this->data->getKey());
    }

    public function shouldActionDisplayOnDataType()
    {
        return config('joy-voyager-datatable.quick-delete.enabled', true) !== false
            && isInPatterns(
                $this->dataType->slug,
                config('joy-voyager-datatable.quick-delete.allowed_slugs', ['*'])
            )
            && !isInPatterns(
                $this->dataType->slug,
                config('joy-voyager-datatable.quick-delete.not_allowed_slugs', [])
            );
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
}
