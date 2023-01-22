<!-- {{ $row->getTranslatedAttribute('display_name') }} -->

<!-- GET THE DISPLAY OPTIONS -->
@php
    $display_options = $row->details->display ?? NULL;
    if ($filterDataTypeContent->{$row->field.'_filter'}) {
        $filterDataTypeContent->{$row->field} = $filterDataTypeContent->{$row->field.'_filter'};
    }
@endphp
@if (isset($row->details->legend) && isset($row->details->legend->text))
    {{-- <legend class="text-{{ $row->details->legend->align ?? 'center' }}" style="background-color: {{ $row->details->legend->bgcolor ?? '#f0f0f0' }};padding: 5px;">{{ $row->details->legend->text }}</legend> --}}
@endif

<div class="@if($row->type == 'hidden') hidden @endif {{ $rowClass ?? ''}}" @if(isset($display_options->filter_id)){{ "id=$display_options->filter_id" }}@endif>
    {{ $row->slugify }}
    @if($withLabel ?? false)
        <label class="control-label" for="name">{{ $row->getTranslatedAttribute('display_name') }}</label>
    @endif
    @include('voyager::multilingual.input-hidden-bread-edit-add', ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $filterDataTypeContent, 'content' => $filterDataTypeContent->{$row->field}, 'action' => ('filter'), 'view' => ('filter'), 'options' => $row->details])
    @if (isset($row->details->filter_view))
        @include($row->details->filter_view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $filterDataTypeContent, 'content' => $filterDataTypeContent->{$row->field}, 'action' => ('filter'), 'view' => ('filter'), 'options' => $row->details])
    @elseif ($row->type == 'relationship')
        @include('joy-voyager-datatable::filterformfields.relationship', ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $filterDataTypeContent, 'content' => $filterDataTypeContent->{$row->field}, 'action' => ('filter'), 'view' => ('filter'), 'options' => $row->details])
    @else
        {!! app('joy-voyager-datatable')->filterFormField($row, $dataType, $filterDataTypeContent) !!}
    @endif

    @foreach (app('joy-voyager-datatable')->afterFilterFormFields($row, $dataType, $filterDataTypeContent) as $after)
        {!! $after->handle($row, $dataType, $filterDataTypeContent) !!}
    @endforeach
    {{-- @if ($errors->has($row->field))
        @foreach ($errors->get($row->field) as $error)
            <span class="help-block">{{ $error }}</span>
        @endforeach
    @endif --}}
</div>