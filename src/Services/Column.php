<?php

namespace Joy\VoyagerDatatable\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;

class Column
{
    /**
     * Index column
     *
     * @param mixed $data
     */
    public function indexColumn(
        $data,
        DataType $dataType
    ): string {
        return '<input type="checkbox" name="row_id" id="checkbox_'
            . $data->getKey() . '" title="'
            . ($data->name ?? '') . '" value="' . $data->getKey() . '" />';
    }

    /**
     * Handle
     *
     * @param mixed $data
     */
    public function handle(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
        if (isset($row->details->view)) {
            return $this->columnView(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'image') {
            return $this->columnImage(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'relationship') {
            return $this->columnRelationship(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'select_multiple') {
            return $this->columnSelectMultiple(
                $row,
                $data,
                $dataType
            );
        }

        if (
            $row->type == 'multiple_checkbox'
            && property_exists($row->details, 'options')
        ) {
            return $this->columnMultipleCheckbox(
                $row,
                $data,
                $dataType
            );
        }

        if (
            ($row->type == 'select_dropdown' || $row->type == 'radio_btn')
            && property_exists($row->details, 'options')
        ) {
            return $this->columnSelectDropdown(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'date' || $row->type == 'timestamp') {
            return $this->columnDate(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'checkbox') {
            return $this->columnCheckbox(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'color') {
            return $this->columnColor(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'text') {
            return $this->columnText(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'text_area') {
            return $this->columnTextArea(
                $row,
                $data,
                $dataType
            );
        }

        if (
            $row->type == 'file'
            && !empty($data->{$row->field})
        ) {
            return $this->columnFile(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'rich_text_box') {
            return $this->columnRichTextBox(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'coordinates') {
            return $this->columnCoordinates(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'multiple_images') {
            return $this->columnMultipleImages(
                $row,
                $data,
                $dataType
            );
        }

        if ($row->type == 'media_picker') {
            return $this->columnMediaPicker(
                $row,
                $data,
                $dataType
            );
        }

        return $this->columnDefault(
            $row,
            $data,
            $dataType
        );
    }

    /**
     * Column view
     *
     * @param mixed $data
     */
    protected function columnView(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
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
    protected function columnImage(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
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
    protected function columnRelationship(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
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
    protected function columnSelectMultiple(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
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
    protected function columnMultipleCheckbox(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
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
    protected function columnSelectDropdown(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
        return $row->details->options->{$data->{$row->field}} ?? '';
    }

    /**
     * Column date
     *
     * @param mixed $data
     */
    protected function columnDate(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
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
    protected function columnCheckbox(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
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
    protected function columnColor(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
        return '<span class="badge badge-lg" style="background-color: '
            . $data->{$row->field} . '">' . $data->{$row->field} . '</span>';
    }

    /**
     * Column text
     *
     * @param mixed $data
     */
    protected function columnText(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
        $trimContent = mb_strlen($data->{$row->field}) > 200
            ? mb_substr($data->{$row->field}, 0, 200) . ' ...'
            : $data->{$row->field};

        return $this->translatify(
            (string) $trimContent,
            $row,
            $data,
            $dataType
        );
    }

    /**
     * Column text area
     *
     * @param mixed $data
     */
    protected function columnTextArea(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
        $trimContent = mb_strlen($data->{$row->field}) > 200
            ? mb_substr($data->{$row->field}, 0, 200) . ' ...'
            : $data->{$row->field};

        return $this->translatify(
            (string) $trimContent,
            $row,
            $data,
            $dataType
        );
    }

    /**
     * Column file
     *
     * @param mixed $data
     */
    protected function columnFile(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
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
    protected function columnRichTextBox(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
        $trimContent = mb_strlen(strip_tags($data->{$row->field}, '<b><i><u>')) > 200
            ? mb_substr(strip_tags($data->{$row->field}, '<b><i><u>'), 0, 200) . ' ...'
            : strip_tags($data->{$row->field}, '<b><i><u>');

        return $this->translatify(
            (string) $trimContent,
            $row,
            $data,
            $dataType
        );
    }

    /**
     * Column coordinates
     *
     * @param mixed $data
     */
    protected function columnCoordinates(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
        return (string) view('voyager::partials.coordinates-static-image');
    }

    /**
     * Column multiple images
     *
     * @param mixed $data
     */
    protected function columnMultipleImages(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
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
    protected function columnMediaPicker(
        DataRow $row,
        $data,
        DataType $dataType
    ): string {
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
    protected function columnDefault(
        DataRow $row,
        $data,
        DataType $dataType
    ) {
        return $this->translatify(
            (string) $data->{$row->field},
            $row,
            $data,
            $dataType
        );
    }

    /**
     * Column default
     *
     * @param mixed $data
     */
    protected function translatify(
        string $content,
        DataRow $row,
        $data,
        DataType $dataType
    ) {
        $model = app($dataType->model_name);

        if (!is_field_translatable($model, $row)) {
            return $content;
        }

        $view = (string) view('voyager::multilingual.input-hidden-bread-browse', ['data' => $data, 'row' => $row]);
        $view .= '<span>' . $content . '</span>';

        return $view;
    }

    /**
     * Actions
     *
     * @param mixed $data
     */
    public function actions(
        $data,
        $dataType,
        $actions
    ) {
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
