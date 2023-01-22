<input type="text" class="form-control" name="{{ $row->field }}"
        placeholder="{{ $options->placeholder ?? $row->getTranslatedAttribute('display_name') }}"
       value="{{ $dataTypeContent->{$row->field} ?? $options->filter_default ?? '' }}">
