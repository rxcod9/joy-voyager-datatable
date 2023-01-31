<input type="time"  data-name="{{ $row->getTranslatedAttribute('display_name') }}"  class="form-control input-sm" data-filter-group-from data-filter-group="{{ $row->field }}" name="{{ $row->field }}[from]"
       placeholder="{{ $options->placeholder ?? $row->getTranslatedAttribute('display_name') }}"
       {!! isBreadSlugAutoGenerator($options) !!}
       value="{{ $dataTypeContent->{$row->field} ?? $options->filter_default ?? '' }}">
<input type="time"  data-name="{{ $row->getTranslatedAttribute('display_name') }}"  class="form-control input-sm" data-filter-group-to data-filter-group="{{ $row->field }}" name="{{ $row->field }}[to]"
       placeholder="{{ $options->placeholder ?? $row->getTranslatedAttribute('display_name') }}"
       {!! isBreadSlugAutoGenerator($options) !!}
       value="{{ $dataTypeContent->{$row->field} ?? $options->filter_default ?? '' }}">
