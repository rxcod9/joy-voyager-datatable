<?php

namespace Joy\VoyagerDatatable\Http\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Facades\Voyager;

trait InlineEditAction
{
    //***************************************
    //                ______
    //               |  ____|
    //               | |__
    //               |  __|
    //               | |____
    //               |______|
    //
    //  Edit an item of our Data Type BR(E)AD
    //
    //****************************************

    public function inlineEdit(Request $request, $id)
    {
        $slug  = $this->getSlug($request);
        $field = $request->field;

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $query = $model->query();

            // Use withTrashed() if model uses SoftDeletes and if toggle is selected
            if ($model && in_array(SoftDeletes::class, class_uses_recursive($model))) {
                $query = $query->withTrashed();
            }
            if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope' . ucfirst($dataType->scope))) {
                $query = $query->{$dataType->scope}();
            }
            $dataTypeContent = call_user_func([$query, 'findOrFail'], $id);
        } else {
            // If Model doest exist, get data from table name
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }

        $fieldDataRow = \dataRowByField($field);
        $editRows = $dataType->editRows->filter(function ($editRow) use ($field, $fieldDataRow) {
            if($editRow->field === $field) {
                return true;
            }
            if(optional($fieldDataRow->details)->column === $editRow->field) {
                return true;
            }
            if(optional($fieldDataRow->details)->type_column === $editRow->field) {
                return true;
            }
            return ;
        });
        foreach ($editRows as $key => $row) {
            $editRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'edit');

        // Check permission
        $this->authorize('edit', $dataTypeContent);

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        // Eagerload Relations
        $this->eagerLoadRelations($dataTypeContent, $dataType, 'edit', $isModelTranslatable);

        $view = 'joy-voyager-datatable::bread.inline-edit';

        if (view()->exists("joy-voyager-datatable::$slug.inline-edit")) {
            $view = "joy-voyager-datatable::$slug.inline-edit";
        }

        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'field', 'fieldDataRow'));
    }

    // POST BR(E)AD
    public function inlineUpdate(Request $request, $id)
    {
        $slug  = $this->getSlug($request);
        $field = $request->field;

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof \Illuminate\Database\Eloquent\Model ? $id->{$id->getKeyName()} : $id;

        $model = app($dataType->model_name);
        $query = $model->query();
        if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope' . ucfirst($dataType->scope))) {
            $query = $query->{$dataType->scope}();
        }
        if ($model && in_array(SoftDeletes::class, class_uses_recursive($model))) {
            $query = $query->withTrashed();
        }

        $data = $query->findOrFail($id);

        // Check permission
        $this->authorize('edit', $data);

        $fieldDataRow = \dataRowByField($field);
        $editRows = $dataType->editRows->filter(function ($editRow) use ($field, $fieldDataRow) {
            if($editRow->field === $field) {
                return true;
            }
            if(optional($fieldDataRow->details)->column === $editRow->field) {
                return true;
            }
            if(optional($fieldDataRow->details)->type_column === $editRow->field) {
                return true;
            }
            return ;
        });

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $editRows, $dataType->name, $id)->validate();

        // Get fields with images to remove before updating and make a copy of $data
        $to_remove = $editRows->where('type', 'image')
            ->filter(function ($item, $key) use ($request) {
                return $request->hasFile($item->field);
            });
        $original_data = clone($data);

        $this->insertUpdateData($request, $slug, $editRows, $data);

        // Delete Images
        $this->deleteBreadImages($original_data, $to_remove);

        event(new BreadDataUpdated($dataType, $data));

        if ($request->expectsJson()) {
            return response()->json([
                'canBrowse'  => auth()->user()->can('browse', app($dataType->model_name)),
                'message'    => __('voyager::generic.successfully_updated') . " {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
        }

        if (auth()->user()->can('browse', app($dataType->model_name))) {
            $redirect = redirect()->route("voyager.{$dataType->slug}.index");
        } else {
            $redirect = redirect()->back();
        }

        return $redirect->with([
            'message'    => __('voyager::generic.successfully_updated') . " {$dataType->getTranslatedAttribute('display_name_singular')}",
            'alert-type' => 'success',
        ]);
    }
}
