@foreach($actions as $action)
    @php
        $actionKey = Str::afterLast(get_class($action), '\\');
        $actionKey = Str::beforeLast($actionKey, 'Action');
        $actionKey = Str::slug(Str::kebab($actionKey));
    @endphp
        <!-- Loading script for actions -->
        <!-- 'joy-voyager-datatable::partials.' . $actionKey . '-script' -->
        @includeIf('joy-voyager-datatable::partials.' . $actionKey . '-script', ['action' => $action, 'dataType' => $dataType, 'data' => null, 'dataId' => $dataId])
@endforeach