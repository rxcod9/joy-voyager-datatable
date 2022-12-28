<?php

namespace Joy\VoyagerDatatable\Actions;

use Illuminate\Http\Request;
use TCG\Voyager\Actions\AbstractAction;

class PreviewAction extends AbstractAction
{
    public function getTitle()
    {
        return __('joy-voyager-datatable::generic.preview_btn');
    }

    public function getIcon()
    {
        return 'voyager-eye';
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'class'             => 'btn btn-sm btn-success pull-right preview',
            'target'            => '_blank',
            'data-id'           => $this->data->getKey(),
            'data-loading-text' => "<i class='fa fa-spinner fa-spin'></i> <span class='hidden-xs hidden-sm'>" . $this->getTitle() . '...</span>',
        ];
    }

    public function getDefaultRoute()
    {
        return route('voyager.' . $this->dataType->slug . '.preview', $this->data->getKey());
    }

    public function shouldActionDisplayOnDataType()
    {
        return config('joy-voyager-datatable.quick-preview.enabled', true) !== false
            && isInPatterns(
                $this->dataType->slug,
                config('joy-voyager-datatable.quick-preview.allowed_slugs', ['*'])
            )
            && !isInPatterns(
                $this->dataType->slug,
                config('joy-voyager-datatable.quick-preview.not_allowed_slugs', [])
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
