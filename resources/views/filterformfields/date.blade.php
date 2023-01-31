<input type="date" class="form-control input-sm" data-filter-group-from data-filter-group="{{ $row->field }}" name="{{ $row->field }}[from]"
       placeholder="From {{ $row->getTranslatedAttribute('display_name') }}"
       value="@if(isset($dataTypeContent->{$row->field})){{ \Carbon\Carbon::parse($dataTypeContent->{$row->field})->format('Y-m-d') }}@endif">
<input type="date" class="form-control input-sm" data-filter-group-to data-filter-group="{{ $row->field }}" name="{{ $row->field }}[to]"
       placeholder="To {{ $row->getTranslatedAttribute('display_name') }}"
       value="@if(isset($dataTypeContent->{$row->field})){{ \Carbon\Carbon::parse($dataTypeContent->{$row->field})->format('Y-m-d') }}@endif">
