<input type="hidden" class="form-control input-sm" name="{{ $row->field }}"
       placeholder="{{ $row->getTranslatedAttribute('display_name') }}"
       {!! isBreadSlugAutoGenerator($options) !!}
       value="{{ $dataTypeContent->{$row->field} ?? $options->filter_default ?? '' }}">
