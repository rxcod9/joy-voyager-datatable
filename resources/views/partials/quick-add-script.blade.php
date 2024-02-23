@once('quick-create' . ($dataId ?? ''))
@push('javascript')

{{-- Quick Create modal --}}
<div class="modal modal-info fade" tabindex="-1" id="quick_create_modal{{ $dataId }}" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="{{ $dataType->icon }}"></i> {{ __('joy-voyager-datatable::generic.quick_add_new').' '.$dataType->getTranslatedAttribute('display_name_singular') }}</h4>
            </div>
            <div class="modal-body">
                
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade modal-danger" id="quick_add_confirm_delete_modal{{ $dataId }}">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="voyager-warning"></i> {{ __('voyager::generic.are_you_sure') }}</h4>
            </div>

            <div class="modal-body">
                <h4>{{ __('voyager::generic.are_you_sure_delete') }} '<span class="quick_add_confirm_delete_name"></span>'</h4>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="quick_add_confirm_delete{{ $dataId }}">{{ __('voyager::generic.delete_confirm') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- End Delete File Modal -->
<script>
    var quickCreateAction = `{{ route('voyager.'.$dataType->slug.'.quick-create') }}`;
    var quickUpdateFormAction = `{{ route('voyager.'.$dataType->slug.'.quick-store') }}`;
    $('.quick-create').click(function (e) {
        const btn = $(this);
        btn.button('loading');
        $.ajax({
            url: quickCreateAction,
            type: 'GET',
            success: function (response) {
                btn.button('reset');

                $('#quick_create_modal{{ $dataId }} .modal-body').html(response);

                $('#quick_create_modal{{ $dataId }}').modal('show');

                window.gMapVm = new Vue({ el: '#coordinates-formfield' });

                var media_picker_element = document.querySelectorAll('div[id^="media_picker_"]');

                // For each ace editor element on the page
                for(var i = 0; i < media_picker_element.length; i++)
                {
                    new Vue({
                        el: media_picker_element[i]
                    });
                }

                var ace_editor_element = document.getElementsByClassName("ace_editor");

                // For each ace editor element on the page
                for(var i = 0; i < ace_editor_element.length; i++)
                {

                    //Define path for libs
                    ace.config.set("basePath", $('meta[name="assets-path"]').attr('content')+"?path=js/ace/libs");

                    // Create an ace editor instance
                    var ace_editor = ace.edit(ace_editor_element[i].id);

                    // Get the corresponding text area associated with the ace editor
                    var ace_editor_textarea = document.getElementById(ace_editor_element[i].id + '_textarea');

                    if(ace_editor_element[i].getAttribute('data-theme')){
                        ace_editor.setTheme("ace/theme/" + ace_editor_element[i].getAttribute('data-theme'));
                    }

                    if(ace_editor_element[i].getAttribute('data-language')){
                        ace_editor.getSession().setMode("ace/mode/" + ace_editor_element[i].getAttribute('data-language'));
                    }
                    
                    ace_editor.on('change', function(event, el) {
                        ace_editor_id = el.container.id;
                        ace_editor_textarea = document.getElementById(ace_editor_id + '_textarea');
                        ace_editor_instance = ace.edit(ace_editor_id);
                        ace_editor_textarea.value = ace_editor_instance.getValue();
                    });
                }

                /********** MARKDOWN EDITOR **********/

                $('textarea.easymde').each(function () {
                    var easymde = new EasyMDE({
                        element: this
                    });
                    easymde.render();
                });

                /********** END MARKDOWN EDITOR **********/

                $('#quick_create_modal{{ $dataId }} .form-group .datepicker').datetimepicker();

                //Init datepicker for date fields if data-datepicker attribute defined
                //or if browser does not handle date inputs
                $('#quick_create_modal{{ $dataId }} .form-group input[type=date]').each(function (idx, elt) {
                    if (elt.hasAttribute('data-datepicker')) {
                        elt.type = 'text';
                        $(elt).datetimepicker($(elt).data('datepicker'));
                    } else if (elt.type != 'date') {
                        elt.type = 'text';
                        $(elt).datetimepicker({
                            format: 'L',
                            extraFormats: [ 'YYYY-MM-DD' ]
                        }).datetimepicker($(elt).data('datepicker'));
                    }
                });

                $('#quick_create_modal{{ $dataId }} select.select2').select2({
                    dropdownParent: $('#quick_create_modal{{ $dataId }}'),
                    width: '100%'
                });
                $('#quick_create_modal{{ $dataId }} select.select2-ajax').each(function() {
                    $(this).select2({
                        dropdownParent: $('#quick_create_modal{{ $dataId }}'),
                        width: '100%',
                        tags: $(this).hasClass('taggable'),
                        createTag: function(params) {
                            var term = $.trim(params.term);

                            if (term === '') {
                                return null;
                            }

                            return {
                                id: term,
                                text: term,
                                newTag: true
                            }
                        },
                        ajax: {
                            url: $(this).data('get-items-route'),
                            data: function (params) {
                                var query = {
                                    search: params.term,
                                    type: $(this).data('get-items-field'),
                                    method: $(this).data('method'),
                                    id: $(this).data('id'),
                                    page: params.page || 1
                                }
                                return query;
                            }
                        }
                    });

                    $(this).on('select2:select',function(e){
                        var data = e.params.data;
                        if (data.id == '') {
                            // "None" was selected. Clear all selected options
                            $(this).val([]).trigger('change');
                        } else {
                            $(e.currentTarget).find("option[value='" + data.id + "']").attr('selected','selected');
                        }
                    });

                    $(this).on('select2:unselect',function(e){
                        var data = e.params.data;
                        $(e.currentTarget).find("option[value='" + data.id + "']").attr('selected',false);
                    });

                    $(this).on('select2:selecting', function(e) {
                        if (!$(this).hasClass('taggable')) {
                            return;
                        }
                        var $el = $(this);
                        var route = $el.data('route');
                        var label = $el.data('label');
                        var errorMessage = $el.data('error-message');
                        var newTag = e.params.args.data.newTag;

                        if (!newTag) return;

                        $el.select2('close');

                        $.post(route, {
                            [label]: e.params.args.data.text,
                            _tagging: true,
                        }).done(function(data) {
                            var newOption = new Option(e.params.args.data.text, data.data.id, false, true);
                            $el.append(newOption).trigger('change');
                        }).fail(function(error) {
                            toastr.error(errorMessage);
                        });

                        return false;
                    });
                });

                tinymce.remove('textarea.richTextBox');

                var additionalConfig = {
                    selector: 'textarea.richTextBox',
                }

                // $.extend(additionalConfig, {!! json_encode($options->tinymceOptions ?? '{}') !!})

                tinymce.init(window.voyagerTinyMCE.getConfig(additionalConfig));

                $('#quick_create_modal{{ $dataId }} #slug').slugify();

                $('#quick_create_modal{{ $dataId }} input[data-slug-origin]').each(function(i, el) {
                    $(el).slugify();
                });

                if(typeof helpers.initSelect2MorphToType === 'function') {
                    helpers.initSelect2MorphToType('select.select2-morph-to-type');
                } else {
                    console.warn('initSelect2MorphToType is not available yet.');
                }
                if(typeof helpers.initSelect2MorphToAjax === 'function') {
                    helpers.initSelect2MorphToAjax('select.select2-morph-to-ajax');
                } else {
                    console.warn('initSelect2MorphToAjax is not available yet.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                btn.button('reset');
                var err = JSON.parse(jqXHR.responseText);
                toastr.error(err.message);
            }
        });

        return false;
    });

    $('#quick_create_modal{{ $dataId }}').on('hidden.bs.modal', function () {
        $('#quick_create_modal{{ $dataId }} .modal-body').html('');
    });
    $('#quick_create_modal{{ $dataId }}').on('click', 'form.form-edit-add button[type="submit"]', function (e) {
    // $('#quick_create_modal{{ $dataId }} form.form-edit-add').submit(function (e) {
        $('#quick_create_modal{{ $dataId }} form.form-edit-add button[type="submit"]').data(
            'loading-text',
            "<i class='fa fa-spinner fa-spin'></i> Saving..."
        ).button('loading');
        e.preventDefault(); // avoid to execute the actual submit of the form.
        // $('#quick_create_modal{{ $dataId }} .modal-body form');
        // $.post(quickUpdateFormAction, {}, function (response) {

        //     // $('#quick_create_modal{{ $dataId }} .modal-body').html(response);
        // });

        // $('#quick_create_modal{{ $dataId }}').modal('show');
        // return false;

        var form = $('#quick_create_modal{{ $dataId }} form.form-edit-add');
        var actionUrl = form.attr('action');
        $('.alert', form).remove();

        var formData = new FormData($('#quick_create_modal{{ $dataId }} form.form-edit-add')[0]);
        
        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: actionUrl,
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 120000,
            success: function(response)
            {
                $('.alert', form).remove();
                $('#quick_create_modal{{ $dataId }} form.form-edit-add button[type="submit"]').button('reset');
                $('#quick_create_modal{{ $dataId }}').modal('hide');
                $('#wrapper{{ $dataId }} #dataTable{{ $dataId }}').DataTable().ajax.reload();
                toastr.success(response.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#quick_create_modal{{ $dataId }} form.form-edit-add button[type="submit"]').button('reset');
                $('.alert', form).remove();
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
    var params = {};
    var $file;

    function deleteHandler(tag, isMulti) {
        return function() {
            $file = $(this).siblings(tag);

            params = {
                slug:   '{{ $dataType->slug }}',
                filename:  $file.data('file-name'),
                id:     $file.data('id'),
                field:  $file.parent().data('field-name'),
                multi: isMulti,
                _token: '{{ csrf_token() }}'
            }

            // Using native confirm as confirm modal inside modal not working
            if(confirm(`{{ __('voyager::generic.are_you_sure_delete') }} ${params.filename}`)) {
                $.post('{{ route('voyager.'.$dataType->slug.'.media.remove') }}', params, function (response) {
                    if ( response
                        && response.data
                        && response.data.status
                        && response.data.status == 200 ) {

                        toastr.success(response.data.message);
                        $file.parent().fadeOut(300, function() { $(this).remove(); })
                    } else {
                        toastr.error("Error removing file.");
                    }
                });
            }
            // $('.quick_add_confirm_delete_modal{{ $dataId }} .quick_add_confirm_delete_name').text(params.filename);
            // $('#quick_add_confirm_delete_modal{{ $dataId }}').modal('show');

        };
    }

    $('document').ready(function () {
        // $('.toggleswitch').bootstrapToggle();

        // @if ($isModelTranslatable)
        //     $('.side-body').multilingual({"createing": true});
        // @endif

        // $('#quick_create_modal{{ $dataId }} .side-body input[data-slug-origin]').each(function(i, el) {
        //     $(el).slugify();
        // });

        $('#quick_create_modal{{ $dataId }}').on('click', '.form-group .remove-multi-image', deleteHandler('img', true));
        $('#quick_create_modal{{ $dataId }}').on('click', '.form-group .remove-single-image', deleteHandler('img', false));
        $('#quick_create_modal{{ $dataId }}').on('click', '.form-group .remove-multi-file', deleteHandler('a', true));
        $('#quick_create_modal{{ $dataId }}').on('click', '.form-group .remove-single-file', deleteHandler('a', false));

        $('#quick_add_confirm_delete{{ $dataId }}').on('click', function(){
            $.post('{{ route('voyager.'.$dataType->slug.'.media.remove') }}', params, function (response) {
                if ( response
                    && response.data
                    && response.data.status
                    && response.data.status == 200 ) {

                    toastr.success(response.data.message);
                    $file.parent().fadeOut(300, function() { $(this).remove(); })
                } else {
                    toastr.error("Error removing file.");
                }
            });

            $('#quick_add_confirm_delete_modal{{ $dataId }}').modal('hide');
        });
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
@endonce