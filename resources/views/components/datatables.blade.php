@foreach($dataTypes as $dataType)
    <x-joy-voyager-datatable
        :slug="$dataType->slug"
        :data-id="($dataId ?? 'tables') . '-' . $dataType->slug"
        :without-checkbox="$withoutCheckbox"
        :without-actions="$withoutActions"
        :with-label="$withLabel"
    />
@endforeach