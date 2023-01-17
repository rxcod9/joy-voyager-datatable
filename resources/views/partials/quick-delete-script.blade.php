@once('quick-delete' . ($dataId ?? ''))
@push('javascript')

{{-- Quick Delete modal --}}
<div class="modal modal-danger fade" tabindex="-1" id="quick_delete_modal{{ $dataId }}" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="voyager-trash"></i> {{ __('voyager::generic.delete_question') }} {{ strtolower($dataType->getTranslatedAttribute('display_name_singular')) }}?</h4>
            </div>
            <div class="modal-footer">
                <form action="#" class="delete_form" method="POST">
                    {{ method_field('DELETE') }}
                    {{ csrf_field() }}
                    <input type="submit" class="btn btn-danger pull-right delete-confirm" value="{{ __('voyager::generic.delete_confirm') }}">
                </form>
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
    var itemId;
    var quickDeleteAction = `{{ route('voyager.'.$dataType->slug.'.quick-delete', ['id' => '__ID__']) }}`;
    var quickDeleteFormAction = `{{ route('voyager.'.$dataType->slug.'.quick-update', ['id' => '__ID__']) }}`;
    $('#wrapper{{ $dataId }} #dataTable{{ $dataId }}').on('click', 'td .quick-delete', function (e) {

        $('#quick_delete_modal{{ $dataId }} .delete_form')[0].action = '{{ route('voyager.'.$dataType->slug.'.quick-delete', '__id') }}'.replace('__id', $(this).data('id'));
        $('#quick_delete_modal{{ $dataId }}').modal('show');

        return false;
    });
    // $('#quick_delete_modal{{ $dataId }}').on('click', 'form.delete_form button[type="submit"]', function (e) {
    $('#quick_delete_modal{{ $dataId }} form.delete_form').submit(function (e) {
        $('#quick_delete_modal{{ $dataId }} form.delete_form button[type="submit"]').data(
            'loading-text',
            "<i class='fa fa-spinner fa-spin'></i> Deleting..."
        ).button('loading');
        e.preventDefault(); // avoid to execute the actual submit of the form.

        var form = $('#quick_delete_modal{{ $dataId }} form.delete_form');
        var actionUrl = form.attr('action');
        $('.alert', form).remove();

        var formData = new FormData($('#quick_delete_modal{{ $dataId }} form.delete_form')[0]);
        
        $.ajax({
            type: "DELETE",
            // enctype: 'multipart/form-data',
            url: actionUrl,
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 120000,
            success: function(response)
            {
                $('.alert', form).remove();
                $('#quick_delete_modal{{ $dataId }} form.delete_form button[type="submit"]').button('reset');
                $('#quick_delete_modal{{ $dataId }}').modal('hide');
                $('#wrapper{{ $dataId }} #dataTable{{ $dataId }}').DataTable().ajax.reload();
                toastr.success(response.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('.alert', form).remove();
                $('#quick_delete_modal{{ $dataId }} form.delete_form button[type="submit"]').button('reset');
                var err = JSON.parse(jqXHR.responseText);
                let errorsHtml = '';
                for(let errorKey in err.errors) {
                    errorsHtml += '<li>' + err.errors[errorKey].join(',') + '</li>';
                }
                if(!errorsHtml) {
                    errorsHtml = err.message;
                }
                const errorHtml = `<div class="alert alert-danger">
                    <ul>
                            ${errorsHtml}
                    </ul>
                </div>`;

                form.prepend(errorHtml);
            }
        });
        return false;
    });
</script>
@endpush
@endonce