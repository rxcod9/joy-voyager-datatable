<input type="number"
       class="form-control input-sm"
       data-filter-group-from 
       data-filter-group="{{ $row->field }}"
       name="{{ $row->field }}[from]"
       type="number"
       @if(isset($options->min)) min="{{ $options->min }}" @endif
       @if(isset($options->max)) max="{{ $options->max }}" @endif
       step="{{ $options->step ?? 'any' }}"
       placeholder="From {{ $options->placeholder ?? $row->getTranslatedAttribute('display_name') }}"
       value="{{ $dataTypeContent->{$row->field} ?? $options->filter_default ?? '' }}">
<input type="number"
       class="form-control input-sm"
       data-filter-group-to 
       data-filter-group="{{ $row->field }}"
       name="{{ $row->field }}[to]"
       type="number"
       @if(isset($options->min)) min="{{ $options->min }}" @endif
       @if(isset($options->max)) max="{{ $options->max }}" @endif
       step="{{ $options->step ?? 'any' }}"
       placeholder="To {{ $options->placeholder ?? $row->getTranslatedAttribute('display_name') }}"
       value="{{ $dataTypeContent->{$row->field} ?? $options->filter_default ?? '' }}">
