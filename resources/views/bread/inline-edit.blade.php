@php
    $edit = true; // !is_null($dataTypeContent->getKey());
    $add  = false; // is_null($dataTypeContent->getKey());
@endphp
<div class="row">
    <div class="col-md-12">

        <div class="panel panel-bordered">
            <!-- form start -->
            <form role="form"
                    class="form-inline-edit"
                    action="{{ $edit ? route('voyager.'.$dataType->slug.'.inline-update', ['id' => $dataTypeContent->getKey(), 'field' => $field]) : route('voyager.'.$dataType->slug.'.inline-store') }}"
                    method="POST" enctype="multipart/form-data">
                <!-- PUT Method if we are editing -->
                @if($edit)
                    {{-- method_field("PUT") --}}
                @endif

                <!-- CSRF TOKEN -->
                {{ csrf_field() }}

                <div class="panel-body row">

                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Adding / Editing -->
                    @php
                        $dataTypeRows = $dataType->{($edit ? 'editRows' : 'addRows' )}->filter(function ($editRow) use ($field) {
                            return $editRow->field === $field;
                        });
                    @endphp

                    @foreach($dataTypeRows as $row)
                        <!-- GET THE DISPLAY OPTIONS -->
                        @php
                            $display_options = $row->details->display ?? NULL;
                            if ($dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')}) {
                                $dataTypeContent->{$row->field} = $dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')};
                            }
                        @endphp
                        @if (isset($row->details->legend) && isset($row->details->legend->text))
                            <legend class="text-{{ $row->details->legend->align ?? 'center' }}" style="background-color: {{ $row->details->legend->bgcolor ?? '#f0f0f0' }};padding: 5px;">{{ $row->details->legend->text }}</legend>
                        @endif

                        <div class="form-group @if($row->type == 'hidden') hidden @endif col-md-{{ $display_options->width ?? 12 }} {{ $errors->has($row->field) ? 'has-error' : '' }}" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                            {{ $row->slugify }}
                            <label class="control-label" for="name">{{ $row->getTranslatedAttribute('display_name') }}</label>
                            @include('voyager::multilingual.input-hidden-bread-edit-add')
                            @if (isset($row->details->view))
                                @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'action' => ($edit ? 'edit' : 'add'), 'view' => ($edit ? 'edit' : 'add'), 'options' => $row->details])
                            @elseif ($row->type == 'relationship')
                                @include('voyager::formfields.relationship', ['options' => $row->details])
                            @else
                                {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                            @endif

                            @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                            @endforeach
                            @if ($errors->has($row->field))
                                @foreach ($errors->get($row->field) as $error)
                                    <span class="help-block">{{ $error }}</span>
                                @endforeach
                            @endif
                        </div>
                    @endforeach

                </div><!-- panel-body -->

                <div class="panel-footer">
                    @section('submit-buttons')
                        <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                    @stop
                    @yield('submit-buttons')
                </div>
            </form>

            <iframe id="form_target" name="form_target" style="display:none"></iframe>
            <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post"
                    enctype="multipart/form-data" style="width:0;height:0;overflow:hidden">
                <input name="image" id="upload_file" type="file"
                            onchange="$('#my_form').submit();this.value='';">
                <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
                {{ csrf_field() }}
            </form>

        </div>
    </div>
</div>