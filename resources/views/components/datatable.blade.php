@php
    array_push($columnDefs, ['targets' => 'dt-not-orderable', 'orderable' => false]);
    if($withoutCheckbox) {
        array_push($columnDefs, ['targets' => 'dt-index', 'visible' =>  false]);
    }
    if($withoutActions) {
        array_push($columnDefs, ['targets' => 'dt-actions', 'visible' =>  false]);
    }
@endphp
<div id="wrapper{{ $dataId }}" class="panel panel-bordered">
    @if($withLabel)
    <div class="panel-header">
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i> {{ $dataType->getTranslatedAttribute('display_name_plural') }}
        </h1>
    </div>
    @endif
    <div class="panel-body">
        {{-- @include('joy-voyager-datatable::partials.global-filters') --}}
        <div class="table-responsive col-md-12 row">
            <table id="dataTable{{ $dataId }}" class="table table-sm">
                <thead>
                    <tr>
                        @if($showCheckboxColumn)
                            <th class="dt-not-orderable dt-index">
                                <input type="checkbox" class="select_all">
                            </th>
                        @endif
                        @foreach($dataType->browseRows as $row)
                        <th class="dt-col-{{ $row->field }}">
                            {{ $row->getTranslatedAttribute('display_name') }}
                        </th>
                        @endforeach
                        <th class="actions text-right dt-not-orderable dt-actions">{{ __('voyager::generic.actions') }}</th>
                    </tr>
                </thead>
                @include('joy-voyager-datatable::partials.column-filters')
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('javascript')

    {{-- Single delete modal --}}
    <div id="modal-wrapper{{ $dataId }}" class="modal modal-danger fade delete_modal" tabindex="-1" role="dialog">
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
        /**
        * Execute a function given a delay time
        * 
        * @param {type} func
        * @param {type} wait
        * @param {type} immediate
        * @returns {Function}
        */
        var debounce = function (func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        };

        $(document).ready(function () {

            var options = {!! json_encode(
                array_merge([
                    "order" => $orderColumn,
                    "autoWidth" => $autoWidth,
                    "language" => __('voyager::datatable'),
                    "columnDefs" => $columnDefs,
                    "processing" => true,
                    "serverSide" => true,
                    // "colReorder" => true,
                    "stateSave" => config('joy-voyager-datatable.stateSave', true),
                    "ajax" => [
                        'url' => route('voyager.'.$dataType->slug.'.post-ajax', ['lense' => $activeLens, 'showSoftDeleted' => $showSoftDeleted]),
                        'type' => 'POST',
                    ],
                    "columns" => \dataTypeTableColumns($dataType, $showCheckboxColumn, $searchableColumns, $sortableColumns),
                ],
                config('voyager.dashboard.data_tables', []))
            , true) !!};

            options = $.extend(
                options,
                {
                    "drawCallback": function( settings ) {
                        $('#wrapper{{ $dataId }} .select_all').off('click');
                        $('#wrapper{{ $dataId }} .select_all').on('click', function(e) {
                            e.stopPropagation();
                            $('#wrapper{{ $dataId }} input[name="row_id"]').prop('checked', $(this).prop('checked')).trigger('change');
                        });
                    }
                }
            );

            var table{{ $dataId }} = $('#wrapper{{ $dataId }} #dataTable{{ $dataId }}').DataTable(options);

            const initColumnFilters{{ $dataId }} = function(el) {
                console.log('initColumnFilters{{ $dataId }}');
                const dataTable{{ $dataId }} = $(el).DataTable();
                $(el).DataTable().table().columns().eq(0).each(function(colIdx) {

                    // cell
                    var cell = $('thead.dt-col-filters tr th', el)
                        .eq($(dataTable{{ $dataId }}.table().column(colIdx).header()).index());
                    const filterType = cell.data('type');

                    switch (filterType) {
                        case 'code_editor':
                        case 'markdown_editor':
                        case 'rich_text_box':
                        case 'text_area':
                        case 'color':
                        case 'text':
                            $('input', cell)
                                .off('keyup change')
                                .on('keyup change', debounce(function (e) {
                                    console.log('e', e);
                                    e.stopPropagation();
                                    dataTable{{ $dataId }}.table().column(colIdx).search($(this).val()).draw();
                            }, 500));
                            break;
                        case 'timestamp':
                            $('input[type="datetime"]', cell)
                                .off('dp.change')
                                .on('dp.change', debounce(function (e) {
                                    e.stopPropagation();
                                    const filterGroup = $(this).data('filter-group');
                                    const parent = $(this).closest('.form-group');
                                    const fromElValue = $('input[type="datetime"][data-filter-group=' + filterGroup + '][data-filter-group-from]', parent).val();
                                    const toElValue = $('input[type="datetime"][data-filter-group=' + filterGroup + '][data-filter-group-to]', parent).val();
                                    dataTable{{ $dataId }}.table().column(colIdx).search([fromElValue, toElValue]).draw();
                            }, 500));
                            break;
                        case 'number':
                            $('input[type="number"]', cell)
                                .off('keyup change')
                                .on('keyup change', debounce(function (e) {
                                    e.stopPropagation();
                                    const filterGroup = $(this).data('filter-group');
                                    const parent = $(this).closest('.form-group');
                                    const fromElValue = $('input[type="number"][data-filter-group=' + filterGroup + '][data-filter-group-from]', parent).val();
                                    const toElValue = $('input[type="number"][data-filter-group=' + filterGroup + '][data-filter-group-to]', parent).val();
                                    dataTable{{ $dataId }}.table().column(colIdx).search([fromElValue, toElValue]).draw();
                            }, 500));
                            break;
                        case 'date':
                            $('input[type="date"]', cell)
                                .off('change')
                                .on('change', debounce(function (e) {
                                    e.stopPropagation();
                                    const filterGroup = $(this).data('filter-group');
                                    const parent = $(this).closest('.form-group');
                                    const fromElValue = $('input[type="date"][data-filter-group=' + filterGroup + '][data-filter-group-from]', parent).val();
                                    const toElValue = $('input[type="date"][data-filter-group=' + filterGroup + '][data-filter-group-to]', parent).val();
                                    dataTable{{ $dataId }}.table().column(colIdx).search([fromElValue, toElValue]).draw();
                            }, 500));
                            break;
                        case 'image':
                        case 'multiple_images':
                        case 'media_picker':
                        case 'file':
                        case 'multiple_checkbox':
                        case 'checkbox':
                        case 'radio_btn':
                        case 'select_multiple':
                        case 'select_dropdown':
                            $('select', cell)
                                .off('change.col-filter-' + filterType)
                                .on('change.col-filter-' + filterType, function (e) {
                                    dataTable{{ $dataId }}.table().column(colIdx).search($(this).val()).draw();
                            });
                            break;
                        case 'relationship':
                            const relationshipType = cell.data('relationship-type');
                            switch (relationshipType) {
                                case 'belongsTo':
                                case 'belongsToMany':
                                    $('select', cell)
                                        .off('change.col-filter-' + filterType + '-' + relationshipType)
                                        .on('change.col-filter-' + filterType + '-' + relationshipType, function (e) {
                                            dataTable{{ $dataId }}.table().column(colIdx).search($(this).val()).draw();
                                    });
                                    break;
                                case 'morphTo':
                                    const changeMorphToTypeHandler = function (e) {
                                        const parent = $(this).closest('.form-group');
                                        const morphToType = $('.select2-morph-to-type', parent);
                                        const morphToId = $('.select2-morph-to-id', parent);

                                        morphToId.off('change.col-filter-' + filterType + '-' + relationshipType + '-id');
                                        morphToId.val([]).trigger('change');
                                        morphToId.on('change.col-filter-' + filterType + '-' + relationshipType + '-id', changeMorphToIdHandler);

                                        dataTable{{ $dataId }}.table().column(colIdx).search(morphToType.val() + ',,' + morphToId.val().join(',')).draw();
                                    };
                                    const changeMorphToIdHandler = function (e) {
                                        const parent = $(this).closest('.form-group');
                                        const morphToType = $('.select2-morph-to-type', parent);
                                        const morphToId = $('.select2-morph-to-id', parent);
                                        dataTable{{ $dataId }}.table().column(colIdx).search(morphToType.val() + ',,' + morphToId.val().join(',')).draw();
                                    };
                                    $('select.select2-morph-to-type', cell)
                                        .off('change.select2-morph-to-type')
                                        .off('change.col-filter-' + filterType + '-' + relationshipType + '-type')
                                        .on('change.col-filter-' + filterType + '-' + relationshipType + '-type', changeMorphToTypeHandler);
                                    $('select.select2-morph-to-id', cell)
                                        .off('change.col-filter-' + filterType + '-' + relationshipType + '-id')
                                        .on('change.col-filter-' + filterType + '-' + relationshipType + '-id', changeMorphToIdHandler);
                                    break;
                            
                                default:
                                    console.log('NOT IMPLEMENTED YET filterType: ' + filterType)
                                    break;
                            }
                            break;
                    
                        default:
                            console.log('NOT IMPLEMENTED YET filterType: ' + filterType)
                            break;
                    }
                });
            };

            const destroyColumnFilters{{ $dataId }} = function(el) {
                console.log('destroyColumnFilters{{ $dataId }}');
                const dataTable{{ $dataId }} = $(el).DataTable();
                $(el).DataTable().table().columns().eq(0).each(function(colIdx) {

                    // cell
                    var cell = $('thead.dt-col-filters tr th', el)
                        .eq($(dataTable{{ $dataId }}.table().column(colIdx).header()).index());
                    const filterType = cell.data('type');

                    switch (filterType) {
                        case 'code_editor':
                        case 'markdown_editor':
                        case 'rich_text_box':
                        case 'text_area':
                        case 'color':
                        case 'text':
                            $('input', cell)
                                .off('keyup change');
                            break;
                        case 'timestamp':
                            $('input[type="datetime"]', cell)
                                .off('dp.change');
                            break;
                        case 'number':
                            $('input[type="number"]', cell)
                                .off('keyup change');
                            break;
                        case 'date':
                            $('input[type="date"]', cell)
                                .off('change');
                            break;
                        case 'image':
                        case 'multiple_images':
                        case 'media_picker':
                        case 'file':
                        case 'multiple_checkbox':
                        case 'checkbox':
                        case 'radio_btn':
                        case 'select_multiple':
                        case 'select_dropdown':
                            $('select', cell)
                                .off('change.col-filter-' + filterType);
                            break;
                        case 'relationship':
                            const relationshipType = cell.data('relationship-type');
                            switch (relationshipType) {
                                case 'belongsTo':
                                case 'belongsToMany':
                                    $('select', cell)
                                        .off('change.col-filter-' + filterType + '-' + relationshipType);
                                    break;
                                case 'morphTo':
                                    $('select.select2-morph-to-type', cell)
                                        .off('change.col-filter-' + filterType + '-' + relationshipType + '-type');
                                    $('select.select2-morph-to-id', cell)
                                        .off('change.col-filter-' + filterType + '-' + relationshipType + '-id');
                                    break;
                            
                                default:
                                    console.log('NOT IMPLEMENTED YET filterType: ' + filterType)
                                    break;
                            }
                            break;

                        default:
                            console.log('NOT IMPLEMENTED YET filterType: ' + filterType)
                            break;
                    }
                });
            };

            const clearColumnFilters{{ $dataId }} = function(el) {
                console.log('clearColumnFilters{{ $dataId }}');
                destroyColumnFilters{{ $dataId }}(el);
                const dataTable{{ $dataId }} = $(el).DataTable();
                $(el).DataTable().table().columns().eq(0).each(function(colIdx) {

                    // cell
                    var cell = $('thead.dt-col-filters tr th', el)
                        .eq($(dataTable{{ $dataId }}.table().column(colIdx).header()).index());
                    const filterType = cell.data('type');

                    switch (filterType) {
                        case 'code_editor':
                        case 'markdown_editor':
                        case 'rich_text_box':
                        case 'text_area':
                        case 'color':
                        case 'text':
                            $('input', cell).val(null);
                            break;
                        case 'timestamp':
                            $('input[type="datetime"]', cell).val(null);
                            break;
                        case 'number':
                            $('input[type="number"]', cell).val(null);
                            break;
                        case 'date':
                            $('input[type="date"]', cell).val(null);
                            break;
                        case 'image':
                        case 'multiple_images':
                        case 'media_picker':
                        case 'file':
                        case 'multiple_checkbox':
                        case 'checkbox':
                        case 'radio_btn':
                        case 'select_multiple':
                        case 'select_dropdown':
                            const selectEl = $('select', cell);
                            if(selectEl.prop('multiple')) {
                                selectEl.val([]).trigger('change');
                            } else {
                                selectEl.val(null).trigger('change');
                            }
                            break;
                        case 'relationship':
                            const relationshipType = cell.data('relationship-type');
                            switch (relationshipType) {
                                case 'belongsTo':
                                case 'belongsToMany':
                                    const selectEl = $('select', cell);
                                    if(selectEl.prop('multiple')) {
                                        selectEl.val([]).trigger('change');
                                    } else {
                                        selectEl.val(null).trigger('change');
                                    }
                                    break;
                                case 'morphTo':
                                    const morphToTypeEl = $('select.select2-morph-to-type', cell);
                                    morphToTypeEl.val(null).trigger('change');

                                    const morphToIdEl = $('select.select2-morph-to-id', cell);
                                    morphToIdEl.val([]).trigger('change');
                                    break;
                            
                                default:
                                    console.log('NOT IMPLEMENTED YET filterType: ' + filterType)
                                    break;
                            }
                            break;

                        default:
                            console.log('NOT IMPLEMENTED YET filterType: ' + filterType)
                            break;
                    }

                    // reset filters
                    dataTable{{ $dataId }}.table().column(colIdx).search('');
                });
                initColumnFilters{{ $dataId }}(el);
                dataTable{{ $dataId }}.table().draw();
            };

            initColumnFilters{{ $dataId }}($('#wrapper{{ $dataId }} #dataTable{{ $dataId }}'));

            $('#wrapper{{ $dataId }} #dataTable{{ $dataId }} .dt-col-reset-filters').off('click');
            $('#wrapper{{ $dataId }} #dataTable{{ $dataId }} .dt-col-reset-filters').on('click', function(e) {
                e.stopPropagation();
                clearColumnFilters{{ $dataId }}($('#wrapper{{ $dataId }} #dataTable{{ $dataId }}'));
                return false;
            });

            $('#wrapper{{ $dataId }} #dataTable{{ $dataId }} .dt-col-reload').off('click');
            $('#wrapper{{ $dataId }} #dataTable{{ $dataId }} .dt-col-reload').on('click', function(e) {
                e.stopPropagation();
                $('#wrapper{{ $dataId }} #dataTable{{ $dataId }}').DataTable().ajax.reload();
                return false;
            });

            $('#wrapper{{ $dataId }} .select_all').off('click');
            $('#wrapper{{ $dataId }} .select_all').on('click', function(e) {
                e.stopPropagation();
                $('#wrapper{{ $dataId }} input[name="row_id"]').prop('checked', $(this).prop('checked')).trigger('change');
            });
        });

        var deleteFormAction;
        $('#wrapper{{ $dataId }} #dataTable{{ $dataId }}').on('click', 'td .delete', function (e) {
            $('#modal-wrapper{{ $dataId }} .delete_form')[0].action = '{{ route('voyager.'.$dataType->slug.'.destroy', '__id') }}'.replace('__id', $(this).data('id'));
            $('#modal-wrapper{{ $dataId }}.delete_modal').modal('show');
        });

        $('#wrapper{{ $dataId }} #dataTable{{ $dataId }}').on('change', 'input[name="row_id"]', function (e) {
            var ids = [];
            $('#wrapper{{ $dataId }} input[name="row_id"]').each(function() {
                if ($(this).is(':checked')) {
                    ids.push($(this).val());
                }
            });
            $('.selected_ids').val(ids);
        });
    </script>
@endpush
@include('joy-voyager-datatable::bread.partials.actions-script', ['actions' => $actions, 'dataType' => $dataType, 'data' => null, 'dataId' => $dataId])
@include('joy-voyager-datatable::partials.inline-edit-script', ['dataType' => $dataType, 'dataId' => $dataId])
@once('coordinates')
@push('javascript')
<script>
    Vue.component('coordinates', {
        props: {
            apiKey: {
                type: String,
                required: true,
            },
            points: {
                type: Array,
                required: true,
            },
            showAutocomplete: {
                type: Boolean,
                default: true,
            },
            showLatLng: {
                type: Boolean,
                default: true,
            },
            zoom: {
                type: Number,
                required: true,
            }
        },
        data() {
            return {
                autocomplete: null,
                lat: '',
                lng: '',
                map: null,
                marker: null,
                onChangeDebounceTimeout: null,
                place: null,
            };
        },
        mounted() {
            // Load Google Maps script
            let gMapScript = document.createElement('script');
            gMapScript.setAttribute('src', 'https://maps.googleapis.com/maps/api/js?key='+this.apiKey+'&callback=gMapVm.$refs.coordinates.initMap&libraries=places');
            document.head.appendChild(gMapScript);
        },
        methods: {
            initMap: function() {
                console.log('initMap');
                var vm = this;

                var center = vm.points[vm.points.length - 1];

                // Set initial LatLng
                this.setLatLng(center.lat, center.lng);

                // Create map
                vm.map = new google.maps.Map(document.getElementById('map'), {
                    zoom: vm.zoom,
                    center: new google.maps.LatLng(center.lat, center.lng)
                });

                // Create marker
                vm.marker = new google.maps.Marker({
                    position: new google.maps.LatLng(center.lat, center.lng),
                    map: vm.map,
                    draggable: true
                });

                // Listen to map drag events
                google.maps.event.addListener(vm.marker, 'drag', vm.onMapDrag);

                // Setup places Autocomplete
                if (this.showAutocomplete) {
                    vm.autocomplete = new google.maps.places.Autocomplete(document.getElementById('places-autocomplete'));
                    places = new google.maps.places.PlacesService(vm.map);
                    vm.autocomplete.addListener('place_changed', vm.onPlaceChange);
                }
            },

            setLatLng: function(lat, lng) {
                this.lat = lat;
                this.lng = lng;
            },

            moveMapAndMarker: function(lat, lng) {
                this.marker.setPosition(new google.maps.LatLng(lat, lng));
                this.map.panTo(new google.maps.LatLng(lat, lng));
            },

            onMapDrag: function(event) {
                this.setLatLng(event.latLng.lat(), event.latLng.lng());

                this.onChange('mapDragged');
            },

            onInputKeyPress: function(event) {
                if (event.which === 13) {
                    event.preventDefault();
                }
            },

            onPlaceChange: function() {
                this.place = this.autocomplete.getPlace();

                if (this.place.geometry) {
                    this.setLatLng(this.place.geometry.location.lat(), this.place.geometry.location.lng());
                    this.moveMapAndMarker(this.place.geometry.location.lat(), this.place.geometry.location.lng());
                }

                this.onChange('placeChanged');
            },

            onLatLngInputChange: function(event) {
                this.moveMapAndMarker(this.lat, this.lng);

                this.onChange('latLngChanged');
            },

            onChange: function(eventType) {
                console.log('eventType', eventType);
                // commented out vendor/tcg/voyager/resources/views/formfields/coordinates.blade.php -> onChange
            },
        }
    });
</script>
@endpush()
@endonce()
