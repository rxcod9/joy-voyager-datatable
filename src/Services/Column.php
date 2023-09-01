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
        DataType $dataType,
        $content = null
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
        DataType $dataType,
        $content = null
    ): string {
        if (isset($row->details->view)) {
            return $this->columnView(
                $row,
                $data,
                $dataType,
                $content
            );
        }

        switch ($row->type) {
            case 'image':
                return $this->columnImage(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'relationship':
                return $this->columnRelationship(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'select_multiple':
                return $this->columnSelectMultiple(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'multiple_checkbox':
                return $this->columnMultipleCheckbox(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'select_dropdown':
            case 'radio_btn':
                return $this->columnSelectDropdown(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'date':
            case 'timestamp':
                return $this->columnDate(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'checkbox':
                return $this->columnCheckbox(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'color':
                return $this->columnColor(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'text':
                return $this->columnText(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'text_area':
                return $this->columnTextArea(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'file':
                return $this->columnFile(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'rich_text_box':
                return $this->columnRichTextBox(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'coordinates':
                return $this->columnCoordinates(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'multiple_images':
                return $this->columnMultipleImages(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
            case 'media_picker':
                return $this->columnMediaPicker(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;

            default:
                return $this->columnDefault(
                    $row,
                    $data,
                    $dataType,
                    $content
                );
                break;
        }
    }

    /**
     * Column view
     *
     * @param mixed $data
     */
    protected function columnView(
        DataRow $row,
        $data,
        DataType $dataType,
        $content = null
    ): string {
        return (string) view($row->details->view, [
            'row'      => $row,
            'dataType' => $dataType,
            'data'     => $data,
            'content'  => $content,
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
        DataType $dataType,
        $content = null
    ): string {
        return '<img src="' . (
            !filter_var($content, FILTER_VALIDATE_URL)
                ? Voyager::image($content)
                : $content
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
        DataType $dataType,
        $content = null
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
        DataType $dataType,
        $content = null
    ): string {
        $view = '';
        if (property_exists($row->details, 'relationship')) {
            foreach ($content as $item) {
                $view .= $item->{$row->field};
            }
            return $view;
        }

        if (property_exists($row->details, 'options')) {
            if (!empty(json_decode($content))) {
                $keys    = array_keys(json_decode($content, true));
                $lastKey = end($keys);
                foreach (json_decode($content) as $key => $item) {
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
        DataType $dataType,
        $content = null
    ): string {
        $view = '';
        if ($content && @count(json_decode($content, true)) > 0) {
            $keys    = array_keys(json_decode($content, true));
            $lastKey = end($keys);
            foreach (json_decode($content) as $key => $item) {
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
        DataType $dataType,
        $content = null
    ): string {
        return $row->details->options->{$content} ?? '';
    }

    /**
     * Column date
     *
     * @param mixed $data
     */
    protected function columnDate(
        DataRow $row,
        $data,
        DataType $dataType,
        $content = null
    ): string {
        if (
            property_exists($row->details, 'format')
            && null !== $content
        ) {
            return Carbon::parse($content)->formatLocalized($row->details->format);
        }

        return (string) $content;
    }

    /**
     * Column checkbox
     *
     * @param mixed $data
     */
    protected function columnCheckbox(
        DataRow $row,
        $data,
        DataType $dataType,
        $content = null
    ): string {
        if (
            property_exists($row->details, 'on')
            && property_exists($row->details, 'off')
        ) {
            if ($content) {
                return '<span class="label label-info">' . $row->details->on . '</span>';
            }
            return '<span class="label label-primary">' . $row->details->off . '</span>';
        }

        return (string) $content;
    }

    /**
     * Column color
     *
     * @param mixed $data
     */
    protected function columnColor(
        DataRow $row,
        $data,
        DataType $dataType,
        $content = null
    ): string {
        return '<span class="badge badge-lg" style="background-color: '
            . $content . '">' . $content . '</span>';
    }

    /**
     * Column text
     *
     * @param mixed $data
     */
    protected function columnText(
        DataRow $row,
        $data,
        DataType $dataType,
        $content = null
    ): string {
        $trimContent = mb_strlen($content) > 200
            ? mb_substr($content, 0, 200) . ' ...'
            : $content;

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
        DataType $dataType,
        $content = null
    ): string {
        $trimContent = mb_strlen($content) > 200
            ? mb_substr($content, 0, 200) . ' ...'
            : $content;

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
        DataType $dataType,
        $content = null
    ): string {
        if (!$content || json_decode($content) === null) {
            return '';
        }

        $view = (string) view('voyager::multilingual.input-hidden-bread-browse', [
            'data' => $data,
            'row'  => $row
        ]);

        if (json_decode($content) !== null) {
            foreach (json_decode($content) as $file) {
                $view .= '<a href="' . (
                    Storage::disk(config('voyager.storage.disk'))->url($file->download_link) ?: ''
                ) . '" target="_blank">' . ($file->original_name ?: '') . '</a><br/>';
            }
            return $view;
        }

        return '<a href="' . (
            Storage::disk(config('voyager.storage.disk'))->url($content)
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
        DataType $dataType,
        $content = null
    ): string {
        $trimContent = mb_strlen(strip_tags($content, '<b><i><u><a>')) > 200
            ? mb_substr(strip_tags($content, '<b><i><u><a>'), 0, 200) . ' ...'
            : strip_tags($content, '<b><i><u><a>');

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
        DataType $dataType,
        $content = null
    ): string {
        return (string) view('voyager::partials.coordinates-static-image', ['data' => $data, 'row' => $row]);
    }

    /**
     * Column multiple images
     *
     * @param mixed $data
     */
    protected function columnMultipleImages(
        DataRow $row,
        $data,
        DataType $dataType,
        $content = null
    ): string {
        $view   = '';
        $images = json_decode($content);
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
        DataType $dataType,
        $content = null
    ): string {
        $view = '';
        if (is_array($content)) {
            $files = $content;
        } else {
            $files = json_decode($content);
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

        if ($content != '') {
            if (
                property_exists($row->details, 'show_as_images')
                && $row->details->show_as_images
            ) {
                return '<img src="' . (
                    !filter_var($content, FILTER_VALIDATE_URL)
                        ? Voyager::image($content)
                        : $content
                ) . '" style="width:50px" />';
            }
            return (string) $content;
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
        DataType $dataType,
        $content = null
    ) {
        return $this->translatify(
            (string) $content,
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
        DataType $dataType,
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
        $view = 'joy-voyager-datatable::bread.partials.single-group-actions';

        if (view()->exists('joy-voyager-datatable::' . $dataType->slug . '.partials.single-group-actions')) {
            $view = 'joy-voyager-datatable::' . $dataType->slug . '.partials.single-group-actions';
        }

        return view($view, [
            'actions'  => $actions,
            'dataType' => $dataType,
            'data'     => $data,
        ]);
    }
}
