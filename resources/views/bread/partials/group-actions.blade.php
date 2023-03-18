@php
    $crudActions = collect($actions)->filter(function($action) {
        return Str::is([
            '*DeleteAction',
            '*RestoreAction',
            '*EditAction',
            '*ViewAction',
            '*QuickEditAction',
            '*PreviewAction',
            '*ExportAction',
        ], get_class($action));
    });
    $otherActions = collect($actions)->filter(function($action) {
        return !Str::is([
            '*DeleteAction',
            '*RestoreAction',
            '*EditAction',
            '*ViewAction',
            '*QuickEditAction',
            '*PreviewAction',
            '*ExportAction',
        ], get_class($action));
    })->filter(function($action) {
        return !Str::is([
            '*DeleteAction',
            '*RestoreAction',
            '*EditAction',
            '*ViewAction',
            '*QuickEditAction',
            '*PreviewAction',
            '*ExportAction',
        ], get_class($action));
    });
@endphp
<div class="btn-group group-actions">
    @foreach($crudActions as $action)
        @if (method_exists($action, 'massAction'))
            @include('voyager::bread.partials.actions', ['action' => $action, 'dataType' => $dataType, 'data' => null])
        @endif
    @endforeach
    @if($otherActions->count() > 0)
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            @foreach($otherActions as $action)
                @if (method_exists($action, 'massAction'))
                    <li>
                        @include('voyager::bread.partials.actions', ['action' => $action, 'dataType' => $dataType, 'data' => null])
                    </li>
                @endif
            @endforeach
        </ul>
    @endif
</div>