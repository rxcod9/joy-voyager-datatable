@if(isset($options->model) && isset($options->type))

    @if(class_exists($options->model))

        @php $relationshipField = $row->field; @endphp

        @if($options->type == 'belongsTo')

            @if(isset($view) && ($view == 'browse' || $view == 'read'))

                @php
                    $relationshipData = (isset($data)) ? $data : $dataTypeContent;
                    $model = app($options->model);
                    $query = $model::where($options->key,$relationshipData->{$options->column})->first();
                @endphp

                @if(isset($query))
                    <p>{{ $query->{$options->label} }}</p>
                @else
                    <p>{{ __('voyager::generic.no_results') }}</p>
                @endif

            @else

                <select
                    multiple
                    class="form-control input-sm select2-ajax" name="{{ $options->column }}[]"
                    data-get-items-route="{{route('voyager.' . $dataType->slug.'.relation')}}"
                    data-get-items-field="{{$row->field}}"
                    @if(!is_null($dataTypeContent->getKey())) data-id="{{$dataTypeContent->getKey()}}" @endif
                    data-method="filter"
                >
                    @php
                        $model = app($options->model);
                        $query = $model::where($options->key, $dataTypeContent->{$options->column})->get();
                    @endphp

                    <option value="">{{__('voyager::generic.none')}}</option>

                    @foreach($query as $relationshipData)
                        <option value="{{ $relationshipData->{$options->key} }}" @if($dataTypeContent->{$options->column} == $relationshipData->{$options->key}) selected="selected" @endif>{{ $relationshipData->{$options->label} }}</option>
                    @endforeach
                </select>

            @endif

        @elseif($options->type == 'hasOne')

            @php
                $relationshipData = (isset($data)) ? $data : $dataTypeContent;

                $model = app($options->model);
                $query = $model::where($options->column, '=', $relationshipData->{$options->key})->first();

            @endphp

            @if(isset($query))
                <p>{{ $query->{$options->label} }}</p>
            @else
                <p>{{ __('voyager::generic.no_results') }}</p>
            @endif

        @elseif($options->type == 'hasMany')

            @if(isset($view) && ($view == 'browse' || $view == 'read'))

                @php
                    $relationshipData = (isset($data)) ? $data : $dataTypeContent;
                    $model = app($options->model);

                    $selected_values = $model::where($options->column, '=', $relationshipData->{$options->key})->get()->map(function ($item, $key) use ($options) {
                        return $item->{$options->label};
                    })->all();
                @endphp

                @if($view == 'browse')
                    @php
                        $string_values = implode(", ", $selected_values);
                        if(mb_strlen($string_values) > 25){ $string_values = mb_substr($string_values, 0, 25) . '...'; }
                    @endphp
                    @if(empty($selected_values))
                        <p>{{ __('voyager::generic.no_results') }}</p>
                    @else
                        <p>{{ $string_values }}</p>
                    @endif
                @else
                    @if(empty($selected_values))
                        <p>{{ __('voyager::generic.no_results') }}</p>
                    @else
                        <ul>
                            @foreach($selected_values as $selected_value)
                                <li>{{ $selected_value }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif

            @else

                @php
                    $model = app($options->model);
                    $query = $model::where($options->column, '=', $dataTypeContent->{$options->key})->get();
                @endphp

                @if(isset($query))
                    <ul>
                        @foreach($query as $query_res)
                            <li>{{ $query_res->{$options->label} }}</li>
                        @endforeach
                    </ul>

                @else
                    <p>{{ __('voyager::generic.no_results') }}</p>
                @endif

            @endif

        @elseif($options->type == 'belongsToMany')

            @if(isset($view) && ($view == 'browse' || $view == 'read'))

                @php
                    $relationshipData = (isset($data)) ? $data : $dataTypeContent;

                    $selected_values = isset($relationshipData) ? $relationshipData->belongsToMany($options->model, $options->pivot_table, $options->foreign_pivot_key ?? null, $options->related_pivot_key ?? null, $options->parent_key ?? null, $options->key)->get()->map(function ($item, $key) use ($options) {
            			return $item->{$options->label};
            		})->all() : array();
                @endphp

                @if($view == 'browse')
                    @php
                        $string_values = implode(", ", $selected_values);
                        if(mb_strlen($string_values) > 25){ $string_values = mb_substr($string_values, 0, 25) . '...'; }
                    @endphp
                    @if(empty($selected_values))
                        <p>{{ __('voyager::generic.no_results') }}</p>
                    @else
                        <p>{{ $string_values }}</p>
                    @endif
                @else
                    @if(empty($selected_values))
                        <p>{{ __('voyager::generic.no_results') }}</p>
                    @else
                        <ul>
                            @foreach($selected_values as $selected_value)
                                <li>{{ $selected_value }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif

            @else
                <select
                    class="form-control input-sm select2-ajax @if(isset($options->taggable) && $options->taggable === 'on') taggable @endif"
                    name="{{ $relationshipField }}[]" multiple
                    data-get-items-route="{{route('voyager.' . $dataType->slug.'.relation')}}"
                    data-get-items-field="{{$row->field}}"
                    @if(!is_null($dataTypeContent->getKey())) data-id="{{$dataTypeContent->getKey()}}" @endif
                    data-method="filter"
                    @if(isset($options->taggable) && $options->taggable === 'on')
                        data-route="{{ route('voyager.'.\Illuminate\Support\Str::slug($options->table).'.store') }}"
                        data-label="{{$options->label}}"
                        data-error-message="{{__('voyager::bread.error_tagging')}}"
                    @endif
                >

                        @php
                            $selected_keys = [];
                            
                            if (!is_null($dataTypeContent->getKey())) {
                                $selected_keys = $dataTypeContent->belongsToMany(
                                    $options->model,
                                    $options->pivot_table,
                                    $options->foreign_pivot_key ?? null,
                                    $options->related_pivot_key ?? null,
                                    $options->parent_key ?? null,
                                    $options->key
                                )->pluck($options->table.'.'.$options->key);
                            }
                            $selected_values = app($options->model)->whereIn($options->key, $selected_keys)->pluck($options->label, $options->key);
                        @endphp

                        <option value="">{{__('voyager::generic.none')}}</option>

                        @foreach ($selected_values as $key => $value)
                            <option value="{{ $key }}" selected="selected">{{ $value }}</option>
                        @endforeach

                </select>

            @endif

        @endif

    @else

        cannot make relationship because {{ $options->model }} does not exist.

    @endif

@elseif(isset($options->type) && $options->type == 'morphTo')

    @if(isset($view) && ($view == 'browse' || $view == 'read'))

        @php
            $relationshipData = (isset($data)) ? $data : $dataTypeContent;
            $query = isset($relationshipData) ? $relationshipData->morphTo($options->function, $options->type_column, $options->column ?? null)->getResults() : null;
            $type = $query ? collect($options->types)->first(function($item) use($query) {
                return $item->model === get_class($query);
            }) : null;
        @endphp

        @if(isset($query))
            <p><b>{{ $type->display_name }}:</b> {{ $query->{$type->label} }}</p>
        @else
            <p>{{ __('voyager::generic.no_results') }}</p>
        @endif

    @else

        @php
            $relationshipData = (isset($data)) ? $data : $dataTypeContent;
            $relationshipData = isset($relationshipData) ? $relationshipData->morphTo($options->function, $options->type_column, $options->column ?? null)->getResults() : null;
            $type = $relationshipData ? collect($options->types)->first(function($item) use($relationshipData) {
                return $item->model === get_class($relationshipData);
            }) : null;
        @endphp
        <?php $selectedValue = (isset($dataTypeContent->{$options->type_column}) && !is_null($dataTypeContent->{$options->type_column})) ? $dataTypeContent->{$options->type_column} : null; ?>
        <select
            class="form-control input-sm select2-morph-to-type"
            name="{{ $options->type_column }}"
            data-column="{{ $options->column }}"
            style="margin-bottom: 10px;">
            <?php $default = (isset($options->type_default) && !isset($dataTypeContent->{$options->type_column})) ? $options->type_default : null; ?>
            <option value="">{{__('voyager::generic.none')}}</option>
            @if(isset($options->types))
                @foreach($options->types as $option)
                    <option value="{{ $option->model }}" @if($default == $option->model && $selectedValue === NULL) selected="selected" @endif @if($selectedValue == $option->model) selected="selected" @endif>{{ $option->display_name }}</option>
                @endforeach
            @endif
        </select>

        <select
            multiple
            class="form-control input-sm select2-morph-to-ajax select2-morph-to-id" name="{{ $options->column }}[]"
            data-get-items-route="{{route('voyager.' . $dataType->slug.'.morph-to-relation')}}"
            data-get-items-field="{{$row->field}}"
            data-type-column="{{ $options->type_column }}"
            @if(!is_null($dataTypeContent->getKey())) data-id="{{$dataTypeContent->getKey()}}" @endif
            data-method="filter"
            @if($row->required == 1) required @endif
        >
            <option value="">{{__('voyager::generic.none')}}</option>

            @if($relationshipData)
                <option value="{{ $relationshipData->{$type->key ?? 'id'} }}" @if($dataTypeContent->{$options->column} == $relationshipData->{$type->key ?? 'id'}) selected="selected" @endif>{{ $relationshipData->{$type->label} }}</option>
            @endif
        </select>

    @endif
@endif
