<div class="dt-global-filters col-md-12 row">
    <!-- Filtering -->
    @foreach($dataType->rows as $row)
        @continue($row->browse || !app('joy-voyager-datatable')->canFilterFormField($row, $dataType, $filterDataTypeContent))
        <div class="form-group col-md-2 col-sm-4 col-xs-6 dt-global-filter dt-global-filter-{{ $row->field }}" data-type="{{ $row->type }}" @if($row->type === 'relationship') data-relationship-type="{{ $row->details->type }}" @endif>
            @include('joy-voyager-datatable::partials.filter-col', ['withLabel' => true])
        </div>
    @endforeach
</div>