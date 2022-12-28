<?php

namespace Joy\VoyagerDatatable\Actions;

use Illuminate\Http\Request;
use TCG\Voyager\Actions\AbstractAction;

class QuickEditAction extends AbstractAction
{
    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.quick_edit_btn');
    }

    public function getIcon()
    {
        return 'voyager-edit';
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'class'             => 'btn btn-sm btn-success pull-right quick-edit',
            'target'            => '_blank',
            'data-id'           => $this->data->getKey(),
            'data-loading-text' => "<i class='fa fa-spinner fa-spin'></i> <span class='hidden-xs hidden-sm'>" . $this->getTitle() . '...</span>',
        ];
    }

    public function getDefaultRoute()
    {
        return route('voyager.' . $this->dataType->slug . '.quick-edit', $this->data->getKey());
    }

    public function shouldActionDisplayOnDataType()
    {
        return config('joy-voyager-datatable.quick-edit.enabled', true) !== false
            && isInPatterns(
                $this->dataType->slug,
                config('joy-voyager-datatable.quick-edit.allowed_slugs', ['*'])
            )
            && !isInPatterns(
                $this->dataType->slug,
                config('joy-voyager-datatable.quick-edit.not_allowed_slugs', [])
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
