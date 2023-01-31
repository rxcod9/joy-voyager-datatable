@php
    $activeLens = request()->query('lense', \Session::get($dataType->slug . '_activeLens'));
    $crudLenses = collect($lenses)->filter(function($lens) {
        return Str::is([
            '*MyItemsLens',
            '*DeleteLens',
            '*RestoreLens',
            '*EditLens',
            '*ViewLens',
            '*QuickEditLens',
            '*PreviewLens',
            '*ExportLens',
        ], get_class($lens));
    });
    $otherLenses = collect($lenses)->filter(function($lens) {
        return !Str::is([
            '*MyItemsLens',
            '*DeleteLens',
            '*RestoreLens',
            '*EditLens',
            '*ViewLens',
            '*QuickEditLens',
            '*PreviewLens',
            '*ExportLens',
        ], get_class($lens));
    });
@endphp
@if(collect($lenses)->count() > 0)
<ul class="nav nav-tabs">
    <li @if(!$activeLens || $activeLens === 'home') class="active" @endif>
        <a href="{{ route('voyager.' . $dataType->slug . '.index', ['lense' => 'home']) }}" class="pull-right home-lens">
            <i class="voyager-home"></i>  {{ __('joy-voyager-datatable::generic.home') }}
        </a>
    </li>
    @foreach($crudLenses as $lens)
        <li @if($lens->getRouteKey() === $activeLens) class="active" @endif>
            @include('joy-voyager-datatable::bread.partials.lenses', ['lens' => $lens, 'dataType' => $dataType, 'data' => null])
        </li>
    @endforeach
    @if($otherLenses->count() > 0)
        {{-- <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
            <span class="caret"></span></a>
            <ul class="dropdown-menu"> --}}
                @foreach($otherLenses as $lens)
                    <li @if($lens->getRouteKey() === $activeLens) class="active" @endif>
                        @include('joy-voyager-datatable::bread.partials.lenses', ['lens' => $lens, 'dataType' => $dataType, 'data' => null])
                    </li>
                @endforeach
            {{-- </ul>
        </li> --}}
    @endif
</ul>
@endif