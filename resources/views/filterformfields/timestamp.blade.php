<input type="datetime" class="form-control input-sm datepicker" data-filter-group-from data-filter-group="{{ $row->field }}" name="{{ $row->field }}[from]"
       data-widgetParent="body"
       value="@if(isset($dataTypeContent->{$row->field})){{ \Carbon\Carbon::parse($dataTypeContent->{$row->field})->format('m/d/Y g:i A') }}@endif">
<input type="datetime" class="form-control input-sm datepicker" data-filter-group-to data-filter-group="{{ $row->field }}" name="{{ $row->field }}[to]"
       data-widgetParent="body"
       value="@if(isset($dataTypeContent->{$row->field})){{ \Carbon\Carbon::parse($dataTypeContent->{$row->field})->format('m/d/Y g:i A') }}@endif">
