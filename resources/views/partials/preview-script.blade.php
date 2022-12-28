@once('preview' . ($dataId ?? ''))
@push('javascript')

{{-- Preview modal --}}
<div class="modal modal-info fade" tabindex="-1" id="preview_modal{{ $dataId }}" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="{{ $dataType->icon }}"></i> {{ __('voyager::generic.viewing') }} {{ ucfirst($dataType->getTranslatedAttribute('display_name_singular')) }} &nbsp;</h4>
            </div>
            <div class="modal-body">
                
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
    var previewFormAction;
    $('#wrapper{{ $dataId }} #dataTable{{ $dataId }}').on('click', 'td .preview', function (e) {
        const btn = $(this);
        btn.button('loading');
        const itemId = $(this).data('id');
        let routeAction = `{{ route('voyager.'.$dataType->slug.'.preview', ['id' => '__ID__']) }}`;
        $.ajax({
            url: routeAction.replace('__ID__', itemId),
            type: 'GET',
            success: function (response) {
                btn.button('reset');
                $('#preview_modal{{ $dataId }} .modal-body').html(response);
                $('#preview_modal{{ $dataId }}').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                btn.button('reset');
                var err = JSON.parse(jqXHR.responseText);
                toastr.error(err.message);
            }
        });
        return false;
    });

</script>
@endpush
@endonce