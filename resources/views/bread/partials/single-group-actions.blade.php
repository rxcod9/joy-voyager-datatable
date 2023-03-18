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
    });
@endphp
<div class="btn-group single-group-actions btn-group-xs pull-right">
    @if($otherActions->count() > 0)
        <button type="button" class="btn btn-default dropdown-toggle pull-right" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-left pull-right">
            <li class="pull-right">
                <div class="btn-group pull-right">
                @foreach($otherActions as $action)
                    @if (!method_exists($action, 'massAction'))
                            @include('voyager::bread.partials.actions', ['action' => $action, 'dataType' => $dataType, 'data' => $data])
                    @endif
                @endforeach
                </div>
            </li>
        </ul>
    @endif
    @foreach($crudActions as $action)
        @if (!method_exists($action, 'massAction'))
            @include('voyager::bread.partials.actions', ['action' => $action, 'dataType' => $dataType, 'data' => $data])
        @endif
    @endforeach
</div>