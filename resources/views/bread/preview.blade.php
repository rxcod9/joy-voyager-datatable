<div class="row">
    <div class="col-md-12">

        <div class="panel panel-bordered" style="padding-bottom:5px;">
            <!-- form start -->
            @foreach($dataType->readRows as $row)
                @php
                if ($dataTypeContent->{$row->field.'_read'}) {
                    $dataTypeContent->{$row->field} = $dataTypeContent->{$row->field.'_read'};
                }
                @endphp
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">{{ $row->getTranslatedAttribute('display_name') }}</h3>
                </div>

                <div class="panel-body" style="padding-top:0;">
                    @if (isset($row->details->view_read))
                        @include($row->details->view_read, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'view' => 'read', 'options' => $row->details])
                    @elseif (isset($row->details->view))
                        @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'action' => 'read', 'view' => 'read', 'options' => $row->details])
                    @elseif($row->type == "image")
                        <img class="img-responsive"
                                src="{{ filter_var($dataTypeContent->{$row->field}, FILTER_VALIDATE_URL) ? $dataTypeContent->{$row->field} : Voyager::image($dataTypeContent->{$row->field}) }}">
                    @elseif($row->type == 'multiple_images')
                        @if(json_decode($dataTypeContent->{$row->field}))
                            @foreach(json_decode($dataTypeContent->{$row->field}) as $file)
                                <img class="img-responsive"
                                        src="{{ filter_var($file, FILTER_VALIDATE_URL) ? $file : Voyager::image($file) }}">
                            @endforeach
                        @else
                            <img class="img-responsive"
                                    src="{{ filter_var($dataTypeContent->{$row->field}, FILTER_VALIDATE_URL) ? $dataTypeContent->{$row->field} : Voyager::image($dataTypeContent->{$row->field}) }}">
                        @endif
                    @elseif($row->type == 'relationship')
                            @include('voyager::formfields.relationship', ['view' => 'read', 'options' => $row->details])
                    @elseif($row->type == 'select_dropdown' && property_exists($row->details, 'options') &&
                            !empty($row->details->options->{$dataTypeContent->{$row->field}})
                    )
                        <?php echo $row->details->options->{$dataTypeContent->{$row->field}}; ?>
                    @elseif($row->type == 'select_multiple')
                        @if(property_exists($row->details, 'relationship'))

                            @foreach(json_decode($dataTypeContent->{$row->field}) as $item)
                                {{ $item->{$row->field}  }}
                            @endforeach

                        @elseif(property_exists($row->details, 'options'))
                            @if (!empty(json_decode($dataTypeContent->{$row->field})))
                                @foreach(json_decode($dataTypeContent->{$row->field}) as $item)
                                    @if (@$row->details->options->{$item})
                                        {{ $row->details->options->{$item} . (!$loop->last ? ', ' : '') }}
                                    @endif
                                @endforeach
                            @else
                                {{ __('voyager::generic.none') }}
                            @endif
                        @endif
                    @elseif($row->type == 'date' || $row->type == 'timestamp')
                        @if ( property_exists($row->details, 'format') && !is_null($dataTypeContent->{$row->field}) )
                            {{ \Carbon\Carbon::parse($dataTypeContent->{$row->field})->formatLocalized($row->details->format) }}
                        @else
                            {{ $dataTypeContent->{$row->field} }}
                        @endif
                    @elseif($row->type == 'checkbox')
                        @if(property_exists($row->details, 'on') && property_exists($row->details, 'off'))
                            @if($dataTypeContent->{$row->field})
                            <span class="label label-info">{{ $row->details->on }}</span>
                            @else
                            <span class="label label-primary">{{ $row->details->off }}</span>
                            @endif
                        @else
                        {{ $dataTypeContent->{$row->field} }}
                        @endif
                    @elseif($row->type == 'color')
                        <span class="badge badge-lg" style="background-color: {{ $dataTypeContent->{$row->field} }}">{{ $dataTypeContent->{$row->field} }}</span>
                    @elseif($row->type == 'coordinates')
                        @include('voyager::partials.coordinates')
                    @elseif($row->type == 'rich_text_box')
                        @include('voyager::multilingual.input-hidden-bread-read')
                        {!! $dataTypeContent->{$row->field} !!}
                    @elseif($row->type == 'file')
                        @if(json_decode($dataTypeContent->{$row->field}))
                            @foreach(json_decode($dataTypeContent->{$row->field}) as $file)
                                <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($file->download_link) ?: '' }}">
                                    {{ $file->original_name ?: '' }}
                                </a>
                                <br/>
                            @endforeach
                        @elseif($dataTypeContent->{$row->field})
                            <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($row->field) ?: '' }}">
                                {{ __('voyager::generic.download') }}
                            </a>
                        @endif
                    @else
                        @include('voyager::multilingual.input-hidden-bread-read')
                        <p>{{ $dataTypeContent->{$row->field} }}</p>
                    @endif
                </div><!-- panel-body -->
                @if(!$loop->last)
                    <hr style="margin:0;">
                @endif
            @endforeach

        </div>
    </div>
</div>
{{--<div class="row">
    <div class="col-md-12">
        <x-joy-voyager-relations-tables
            :parentSlug="$dataType->slug"
            :id="$dataTypeContent->getKey()"
        />
    </div>
</div>--}}