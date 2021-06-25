<?php

namespace Joy\VoyagerDatatable\Http\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;
use Yajra\DataTables\Facades\DataTables;

trait AjaxAction
{
    //***************************************
    //               ____
    //              |  _ \
    //              | |_) |
    //              |  _ <
    //              | |_) |
    //              |____/
    //
    //      Ajax our Data Type (B)READ
    //
    //****************************************

    public function ajax(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $getter = 'paginate';

        $orderBy = $request->input(
            'columns.' . $request->input('order.0.column', 0) . '.name',
            $dataType->order_column
        );
        $sortOrder       = $request->input('order.0.dir', $dataType->order_direction);
        $usesSoftDeletes = false;
        $showSoftDeleted = false;

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            $query = $model::select($dataType->name . '.*');

            if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope' . ucfirst($dataType->scope))) {
                $query->{$dataType->scope}();
            }

            // Use withTrashed() if model uses SoftDeletes and if toggle is selected
            if ($model && in_array(SoftDeletes::class, class_uses_recursive($model)) && Auth::user()->can('delete', app($dataType->model_name))) {
                $usesSoftDeletes = true;

                if ($request->get('showSoftDeleted')) {
                    $showSoftDeleted = true;
                    $query->withTrashed();
                }
            }

            // If a column has a relationship associated with it, we do not want to show that field
            $this->removeRelationshipField($dataType, 'browse');

            $row = $dataType->rows->where('field', $orderBy)->firstWhere('type', 'relationship');
            if ($orderBy && (in_array($orderBy, $dataType->fields()) || !empty($row))) {
                $querySortOrder = (!empty($sortOrder)) ? $sortOrder : 'desc';
                if (!empty($row)) {
                    $query->select([
                        $dataType->name . '.*',
                        'joined.' . $row->details->label . ' as ' . $orderBy,
                    ])->leftJoin(
                        $row->details->table . ' as joined',
                        $dataType->name . '.' . $row->details->column,
                        'joined.' . $row->details->key
                    );
                }

                $query->orderBy($orderBy, $querySortOrder);
            } elseif ($model->timestamps) {
                $query->latest($model::CREATED_AT);
            } else {
                $query->orderBy($model->getKeyName(), 'DESC');
            }
        } else {
            // If Model doesn't exist, get data from table name
            $query = DB::table($dataType->name);
            $model = false;
        }

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($model);

        // Eagerload Relations
        // $this->eagerLoadRelations($dataTypeContent, $dataType, 'browse', $isModelTranslatable);

        // Actions
        $actions = [];

        foreach (Voyager::actions() as $action) {
            $action = new $action($dataType, $model);

            if ($action->shouldActionDisplayOnDataType()) {
                $actions[] = $action;
            }
        }

        // Define showCheckboxColumn
        $showCheckboxColumn = false;
        if (Auth::user()->can('delete', app($dataType->model_name))) {
            $showCheckboxColumn = true;
        } else {
            foreach ($actions as $action) {
                if (method_exists($action, 'massAction')) {
                    $showCheckboxColumn = true;
                }
            }
        }

        // Define orderColumn
        $orderColumn = [];
        if ($orderBy) {
            $index = $dataType->browseRows->where('field', $orderBy)->keys()->first()
                + ($showCheckboxColumn ? 1 : 0);
            $orderColumn = [[$index, $sortOrder ?? 'desc']];
        }

        // Define list of columns that can be sorted server side
        $sortableColumns = $this->getSortableColumns($dataType->browseRows);

        $dataTable = DataTables::of($query);
        if ($showCheckboxColumn) {
            // $dataTable->addIndexColumn();
            $dataTable->addColumn('index', function ($data) use ($dataType) {
                return $this->indexColumn($data, $dataType);
            });
        }

        foreach ($dataType->browseRows as $row) {
            $dataTable->addColumn($row->field, function ($data) use ($row, $dataType) {
                if ($data->{$row->field . '_browse'}) {
                    $data->{$row->field} = $data->{$row->field . '_browse'};
                }
                return $this->column($row, $data, $dataType);
            });
        }

        $dataTable->addColumn('action', function ($data) use ($dataType, $actions) {
            return $this->actions($data, $dataType, $actions);
        });

        $rawColumns = $this->rawColumns($dataType, $showCheckboxColumn);

        return $dataTable->rawColumns($rawColumns, true)->make(true);
    }

    /**
     * Index column
     *
     * @param mixed $data
     */
    protected function indexColumn($data, DataType $dataType): string
    {
        return '<input type="checkbox" name="row_id" id="checkbox_'
            . $data->getKey() . '" value="' . $data->getKey() . '" />';
    }

    /**
     * Column
     *
     * @param DataRow $row
     * @param mixed   $data
     */
    protected function column(DataRow $row, $data, DataType $dataType): string
    {
        $view = '';

        if (isset($row->details->view)) {
            return $this->columnView($row, $data, $dataType);
        }

        if ($row->type == 'image') {
            return $this->columnImage($row, $data, $dataType);
        }

        if ($row->type == 'relationship') {
            return $this->columnRelationship($row, $data, $dataType);
        }

        if ($row->type == 'select_multiple') {
            return $this->columnSelectMultiple($row, $data, $dataType);
        }

        if (
            $row->type == 'multiple_checkbox'
            && property_exists($row->details, 'options')
        ) {
            return $this->columnMultipleCheckbox($row, $data, $dataType);
        }

        if (
            ($row->type == 'select_dropdown' || $row->type == 'radio_btn')
            && property_exists($row->details, 'options')
        ) {
            return $this->columnSelectDropdown($row, $data, $dataType);
        }

        if ($row->type == 'date' || $row->type == 'timestamp') {
            return $this->columnDate($row, $data, $dataType);
        }

        if ($row->type == 'checkbox') {
            return $this->columnCheckbox($row, $data, $dataType);
        }

        if ($row->type == 'color') {
            return $this->columnColor($row, $data, $dataType);
        }

        if ($row->type == 'text') {
            return $this->columnText($row, $data, $dataType);
        }

        if ($row->type == 'text_area') {
            return $this->columnTextArea($row, $data, $dataType);
        }

        if (
            $row->type == 'file'
            && !empty($data->{$row->field})
        ) {
            return $this->columnFile($row, $data, $dataType);
        }

        if ($row->type == 'rich_text_box') {
            return $this->columnRichTextBox($row, $data, $dataType);
        }

        if ($row->type == 'coordinates') {
            return $this->columnCoordinates($row, $data, $dataType);
        }

        if ($row->type == 'multiple_images') {
            return $this->columnMultipleImages($row, $data, $dataType);
        }

        if ($row->type == 'media_picker') {
            return $this->columnMediaPicker($row, $data, $dataType);
        } else {
            return $this->columnDefault($row, $data, $dataType);
        }
    }

    /**
     * Column view
     *
     * @param mixed $data
     */
    protected function columnView(DataRow $row, $data, DataType $dataType): string
    {
        return (string) view($row->details->view, [
            'row'      => $row,
            'dataType' => $dataType,
            'data'     => $data,
            'content'  => $data->{$row->field},
            'action'   => 'browse',
            'view'     => 'browse',
            'options'  => $row->details
        ]);
    }

    /**
     * Column image
     *
     * @param mixed $data
     */
    protected function columnImage(DataRow $row, $data, DataType $dataType): string
    {
        return '<img src="' . (
            !filter_var($data->{$row->field}, FILTER_VALIDATE_URL)
                ? Voyager::image($data->{$row->field})
                : $data->{$row->field}
        ) . '" style="width:100px" />';
    }

    /**
     * Column relationship
     *
     * @param mixed $data
     */
    protected function columnRelationship(DataRow $row, $data, DataType $dataType): string
    {
        return (string) view('voyager::formfields.relationship', [
            'view'    => 'browse',
            'row'     => $row,
            'data'    => $data,
            'options' => $row->details
        ]);
    }

    /**
     * Column select multiple
     *
     * @param mixed $data
     */
    protected function columnSelectMultiple(DataRow $row, $data, DataType $dataType): string
    {
        $view = '';
        if (property_exists($row->details, 'relationship')) {
            foreach ($data->{$row->field} as $item) {
                $view .= $item->{$row->field};
            }
            return $view;
        }

        if (property_exists($row->details, 'options')) {
            if (!empty(json_decode($data->{$row->field}))) {
                $lastKey = end(array_keys(json_decode($data->{$row->field})));
                foreach (json_decode($data->{$row->field}) as $key => $item) {
                    if (@$row->details->options->{$item}) {
                        $view .= $row->details->options->{$item} . ($key !== $lastKey ? ', ' : '');
                    }
                }
                return $view;
            }

            return __('voyager::generic.none');
        }

        return $view;
    }

    /**
     * Column multiple checkbox
     *
     * @param mixed $data
     */
    protected function columnMultipleCheckbox(DataRow $row, $data, DataType $dataType): string
    {
        $view = '';
        if (@count(json_decode($data->{$row->field})) > 0) {
            $lastKey = end(array_keys(json_decode($data->{$row->field})));
            foreach (json_decode($data->{$row->field}) as $key => $item) {
                if (@$row->details->options->{$item}) {
                    $view .= $row->details->options->{$item} . ($key !== $lastKey ? ', ' : '');
                }
            }
            return $view;
        }

        return __('voyager::generic.none');
    }

    /**
     * Column select dropdown
     *
     * @param mixed $data
     */
    protected function columnSelectDropdown(DataRow $row, $data, DataType $dataType): string
    {
        return $row->details->options->{$data->{$row->field}} ?? '';
    }

    /**
     * Column date
     *
     * @param mixed $data
     */
    protected function columnDate(DataRow $row, $data, DataType $dataType): string
    {
        if (
            property_exists($row->details, 'format')
            && null !== $data->{$row->field}
        ) {
            return Carbon::parse($data->{$row->field})->formatLocalized($row->details->format);
        }

        return (string) $data->{$row->field};
    }

    /**
     * Column checkbox
     *
     * @param mixed $data
     */
    protected function columnCheckbox(DataRow $row, $data, DataType $dataType): string
    {
        if (
            property_exists($row->details, 'on')
            && property_exists($row->details, 'off')
        ) {
            if ($data->{$row->field}) {
                return '<span class="label label-info">' . $row->details->on . '</span>';
            }
            return '<span class="label label-primary">' . $row->details->off . '</span>';
        }

        return (string) $data->{$row->field};
    }

    /**
     * Column color
     *
     * @param mixed $data
     */
    protected function columnColor(DataRow $row, $data, DataType $dataType): string
    {
        return '<span class="badge badge-lg" style="background-color: '
            . $data->{$row->field} . '">' . $data->{$row->field} . '</span>';
    }

    /**
     * Column text
     *
     * @param mixed $data
     */
    protected function columnText(DataRow $row, $data, DataType $dataType): string
    {
        $trimContent = mb_strlen($data->{$row->field}) > 200
            ? mb_substr($data->{$row->field}, 0, 200) . ' ...'
            : $data->{$row->field};

        return $this->translatify((string) $trimContent, $row, $data, $dataType);
    }

    /**
     * Column text area
     *
     * @param mixed $data
     */
    protected function columnTextArea(DataRow $row, $data, DataType $dataType): string
    {
        $trimContent = mb_strlen($data->{$row->field}) > 200
            ? mb_substr($data->{$row->field}, 0, 200) . ' ...'
            : $data->{$row->field};

        return $this->translatify((string) $trimContent, $row, $data, $dataType);
    }

    /**
     * Column file
     *
     * @param mixed $data
     */
    protected function columnFile(DataRow $row, $data, DataType $dataType): string
    {
        $view = (string) view('voyager::multilingual.input-hidden-bread-browse', [
            'data' => $data,
            'row'  => $row
        ]);

        if (json_decode($data->{$row->field}) !== null) {
            foreach (json_decode($data->{$row->field}) as $file) {
                $view .= '<a href="' . (
                    Storage::disk(config('voyager.storage.disk'))->url($file->download_link) ?: ''
                ) . '" target="_blank">' . ($file->original_name ?: '') . '</a><br/>';
            }
            return $view;
        }

        return '<a href="' . (
            Storage::disk(config('voyager.storage.disk'))->url($data->{$row->field})
        ) . '" target="_blank">Download</a>';
    }

    /**
     * Column rich text box
     *
     * @param mixed $data
     */
    protected function columnRichTextBox(DataRow $row, $data, DataType $dataType): string
    {
        $trimContent = mb_strlen(strip_tags($data->{$row->field}, '<b><i><u>')) > 200
            ? mb_substr(strip_tags($data->{$row->field}, '<b><i><u>'), 0, 200) . ' ...'
            : strip_tags($data->{$row->field}, '<b><i><u>');

        return $this->translatify((string) $trimContent, $row, $data, $dataType);
    }

    /**
     * Column coordinates
     *
     * @param mixed $data
     */
    protected function columnCoordinates(DataRow $row, $data, DataType $dataType): string
    {
        return (string) view('voyager::partials.coordinates-static-image');
    }

    /**
     * Column multiple images
     *
     * @param mixed $data
     */
    protected function columnMultipleImages(DataRow $row, $data, DataType $dataType): string
    {
        $view   = '';
        $images = json_decode($data->{$row->field});
        if ($images) {
            $images = array_slice($images, 0, 3);
            foreach ($images as $image) {
                $view .= '<img src="' . (
                    !filter_var($image, FILTER_VALIDATE_URL)
                        ? Voyager::image($image)
                        : $image
                ) . '" style="width:50px" />';
            }
        }

        return $view;
    }

    /**
     * Column media picker
     *
     * @param mixed $data
     */
    protected function columnMediaPicker(DataRow $row, $data, DataType $dataType): string
    {
        $view = '';
        if (is_array($data->{$row->field})) {
            $files = $data->{$row->field};
        } else {
            $files = json_decode($data->{$row->field});
        }

        if ($files) {
            if (
                property_exists($row->details, 'show_as_images')
                && $row->details->show_as_images
            ) {
                foreach (array_slice($files, 0, 3) as $file) {
                    $view .= '<img src="' . (
                        !filter_var($file, FILTER_VALIDATE_URL) ? Voyager::image($file) : $file
                    ) . '" style="width:50px" />';
                }
                return $view;
            }

            $view .= '<ul>';
            foreach (array_slice($files, 0, 3) as $file) {
                $view .= '<li>' . $file . '</li>';
            }
            $view .= '</ul>';

            if (count($files) > 3) {
                $view .= __('voyager::media.files_more', ['count' => (count($files) - 3)]);
            }

            return $view;
        }

        if (
            is_array($files)
            && count($files) == 0
        ) {
            return trans_choice('voyager::media.files', 0);
        }

        if ($data->{$row->field} != '') {
            if (
                property_exists($row->details, 'show_as_images')
                && $row->details->show_as_images
            ) {
                return '<img src="' . (
                    !filter_var($data->{$row->field}, FILTER_VALIDATE_URL)
                        ? Voyager::image($data->{$row->field})
                        : $data->{$row->field}
                ) . '" style="width:50px" />';
            }
            return (string) $data->{$row->field};
        }

        return trans_choice('voyager::media.files', 0);
    }

    /**
     * Column default
     *
     * @param mixed $data
     */
    protected function columnDefault(DataRow $row, $data, DataType $dataType)
    {
        return $this->translatify((string) $data->{$row->field}, $row, $data, $dataType);
    }

    /**
     * Column default
     *
     * @param mixed $data
     */
    protected function translatify(string $content, DataRow $row, $data, DataType $dataType)
    {
        $model = app($dataType->model_name);

        if (!is_field_translatable($model, $row)) {
            return $content;
        }

        $view = (string) view('voyager::multilingual.input-hidden-bread-browse', ['data' => $data, 'row' => $row]);
        $view .= '<span>' . $content . '</span>';

        return $view;
    }

    /**
     * Raw columns
     */
    protected function rawColumns(DataType $dataType, bool $showCheckboxColumn): array
    {
        $model = app($dataType->model_name);

        $columns = $showCheckboxColumn ? ['index'] : [];

        $browseColumns = $dataType->browseRows->filter(function (DataRow $row) use ($model) {
            return $this->mayHaveHtml($model, $row);
        })->map(function (DataRow $row) {
            return $row->field;
        })->toArray();

        $actionColumns = ['action'];

        return array_merge($columns, $browseColumns, $actionColumns);
    }

    /**
     * May have html
     */
    protected function mayHaveHtml(Model $model, DataRow $row): bool
    {
        if (isset($row->details->view)) {
            return true;
        }

        if ($row->type == 'image') {
            return true;
        }

        if ($row->type == 'relationship') {
            return true;
        }

        if ($row->type == 'select_multiple') {
            return false;
        }

        if (
            $row->type == 'multiple_checkbox'
            && property_exists($row->details, 'options')
        ) {
            return false;
        }

        if (
            ($row->type == 'select_dropdown' || $row->type == 'radio_btn')
            && property_exists($row->details, 'options')
        ) {
            return false;
        }

        if ($row->type == 'date' || $row->type == 'timestamp') {
            return false;
        }

        if ($row->type == 'checkbox') {
            return false;
        }

        if ($row->type == 'color') {
            return true;
        }

        if ($row->type == 'text') {
            return !!is_field_translatable($model, $row);
        }

        if ($row->type == 'text_area') {
            return !!is_field_translatable($model, $row);
        }

        if ($row->type == 'file') {
            return true;
        }

        if ($row->type == 'rich_text_box') {
            return true;
        }

        if ($row->type == 'coordinates') {
            return true;
        }

        if ($row->type == 'multiple_images') {
            return true;
        }

        if ($row->type == 'media_picker') {
            return true;
        }

        return !!is_field_translatable($model, $row);
    }

    /**
     * Actions
     *
     * @param mixed $data
     */
    protected function actions($data, $dataType, $actions)
    {
        $view = '';
        foreach ($actions as $action) {
            if (!method_exists($action, 'massAction')) {
                $view .= view('voyager::bread.partials.actions', [
                    'action'   => $action,
                    'dataType' => $dataType,
                    'data'     => $data,
                ]);
            }
        }
        return $view;
    }
}
