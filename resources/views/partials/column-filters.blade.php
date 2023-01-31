<thead class="dt-col-filters">
    <tr>
        @if($showCheckboxColumn)
            <th class="dt-not-orderable dt-index">
                <!-- <input type="checkbox" class="select_all"> -->
                <a href="javascript:;" class="dt-col-reload"><i class="fa fa-refresh"></i></a>
                <a href="javascript:;" class="dt-col-reset-filters"><i class="fa fa-remove"></i></a>
            </th>
        @endif

        <!-- Filtering -->
        @foreach($dataType->browseRows as $row)
        <th class="dt-col-filter dt-col-filter-{{ $row->field }}" data-type="{{ $row->type }}" @if($row->type === 'relationship') data-relationship-type="{{ $row->details->type }}" @endif>
            @if(app('joy-voyager-datatable')->canFilterFormField($row, $dataType, $filterDataTypeContent))
                @include('joy-voyager-datatable::partials.filter-col', ['withLabel' => false, 'rowClass' => 'form-group'])
            @endif
        </th>
        @endforeach
        @if(!($withoutActions ?? false))
        <th class="actions text-right dt-not-orderable dt-filter-actions">
            {{-- {{ __('voyager::generic.actions') }} --}}
            @if(!$showCheckboxColumn)
                <a href="javascript:;" class="dt-col-reload"><i class="fa fa-refresh"></i></a>
                <a href="javascript:;" class="dt-col-reset-filters"><i class="fa fa-remove"></i></a>
            @endif
        </th>
        @endif
    </tr>
</thead>