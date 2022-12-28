<a
    href="{{ route('voyager.'.$dataType->slug.'.quick-create') }}"
    class="btn btn-success btn-add-new quick-create"
    data-loading-text="<i class='fa fa-spinner fa-spin'></i> <span class='hidden-xs hidden-sm'>{{ __('joy-voyager-datatable::generic.quick_add_new') }}...</span>"
>
    <i class="voyager-plus"></i> <span>{{ __('joy-voyager-datatable::generic.quick_add_new') }}</span>
</a>

@include('joy-voyager-datatable::partials.quick-add-script', ['dataType' => $dataType, 'data' => null, 'dataId' => $dataId ?? null])
