@foreach($dataTypes as $dataType)
    <x-joy-voyager-datatable
        :slug="$dataType->slug"
        :with-label="$withLabel"
        :auto-width="$autoWidth"
        :column-defs="$columnDefs"
        :without-checkbox="$withoutCheckbox"
        :without-actions="$withoutActions"
        :data-id="($dataId ?? 'tables') . '-' . $dataType->slug"
    />
@endforeach