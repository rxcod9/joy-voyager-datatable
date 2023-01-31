<input type="text" class="form-control input-sm" name="{{ $row->field }}"
        placeholder="{{ $options->placeholder ?? $row->getTranslatedAttribute('display_name') }}"
       value="{{ $dataTypeContent->{$row->field} ?? $options->filter_default ?? '' }}">
