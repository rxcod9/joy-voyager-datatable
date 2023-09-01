<?php $selected_value = (isset($dataTypeContent->{$row->field}) && !is_null($dataTypeContent->{$row->field})) ? $dataTypeContent->{$row->field} : null; ?>
<select multiple class="form-control input-sm select2" name="{{ $row->field }}[]">
    <option value="">{{__('voyager::generic.none')}}</option>
    <?php $default = (isset($options->filter_default) && !isset($dataTypeContent->{$row->field})) ? $options->filter_default : null; ?>
    @if(isset($options->options))
        @foreach($options->options as $key => $option)
            @continue($key === '')
            <option value="{{ $key }}" @if($default === $key && $selected_value === NULL) selected="selected" @endif @if($selected_value === $key) selected="selected" @endif>{{ $option }}</option>
        @endforeach
    @endif
</select>