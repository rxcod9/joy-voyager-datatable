<?php

namespace Joy\VoyagerDatatable\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;

class GlobalFilter extends Filter
{
    /**
     * Filter belongsTo relationship
     *
     * @param Builder|QueryBuilder $query   Query
     * @param mixed                $keyword Keyword
     */
    protected function filterRelationshipBelongsTo(
        $query,
        $keyword,
        DataRow $row,
        DataType $dataType,
        Request $request
    ): void {
        $keywords = explode(',', $keyword);
        $query->where(function ($query) use ($row, $keyword, $keywords) {
            $query->whereIn($row->details->column, $keywords);
            $options = $row->details;
            $model   = $query->getModel();

            $query->orWhereExists(function ($query) use ($row, $options, $model, $keyword) {
                $tableAlias = $row->field . '_belongs_to';
                $query->from($options->table, $tableAlias)
                ->whereColumn($tableAlias . '.' . $options->key, $model->getTable() . '.' . $options->column)
                ->where($tableAlias . '.' . $options->label, 'LIKE', '%' . $keyword . '%');
            });
        });
    }

    /**
     * Filter belongsToMany relationship
     *
     * @param Builder|QueryBuilder $query   Query
     * @param mixed                $keyword Keyword
     */
    protected function filterRelationshipBelongsToMany(
        $query,
        $keyword,
        DataRow $row,
        DataType $dataType,
        Request $request
    ): void {
        $keywords              = explode(',', $keyword);
        $model                 = $query->getModel();
        $options               = $row->details;
        $belongsToManyRelation = $model->belongsToMany($options->model, $options->pivot_table, $options->foreign_pivot_key ?? null, $options->related_pivot_key ?? null, $options->parent_key ?? null, $options->key);

        $query->whereExists(function ($query) use ($model, $belongsToManyRelation, $row, $options, $keyword, $keywords) {
            $tableAlias = $row->field . '_belongs_to_many';

            $related = $belongsToManyRelation->getRelated();
            $query->from($options->pivot_table, $tableAlias)
                ->whereColumn($tableAlias . '.' . $belongsToManyRelation->getForeignPivotKeyName(), $model->getTable() . '.' . $model->getKeyName())
                ->where(function ($query) use ($belongsToManyRelation, $related, $row, $options, $keyword, $keywords, $tableAlias) {
                    $query->whereIn($tableAlias . '.' . $belongsToManyRelation->getRelatedPivotKeyName(), $keywords)
                    ->orWhereExists(function ($query) use ($belongsToManyRelation, $related, $row, $options, $keyword, $tableAlias) {
                        $relatedTableAlias = $row->field . '_belongs_to_many_related';
                        $query->from($related->getTable(), $relatedTableAlias)
                        ->whereColumn($relatedTableAlias . '.' . $options->key, $tableAlias . '.' . $belongsToManyRelation->getRelatedPivotKeyName())
                        ->where($relatedTableAlias . '.' . $options->label, 'LIKE', '%' . $keyword . '%');
                    });
                });
        });
    }

    /**
     * Filter morphTo relationship
     *
     * @param Builder|QueryBuilder $query   Query
     * @param mixed                $keyword Keyword
     */
    protected function filterRelationshipMorphTo(
        $query,
        $keyword,
        DataRow $row,
        DataType $dataType,
        Request $request
    ): void {
        $peices      = explode(',,', $keyword);
        $morphToType = $peices[0] ?? null;
        $options     = $row->details;
        $typeColumn  = $options->type_column;
        $types       = $options->types ?? [];

        $query->when(
            $morphToType && in_array($morphToType, collect($types)->pluck('model')->toArray()),
            function ($query) use ($typeColumn, $morphToType) {
                $query->where($typeColumn, $morphToType);
            }
        );
    }

    /**
     * Filter select dropdown
     *
     * @param Builder|QueryBuilder $query   Query
     * @param mixed                $keyword Keyword
     */
    protected function filterSelectDropdown(
        $query,
        $keyword,
        DataRow $row,
        DataType $dataType,
        Request $request
    ): void {
        $keywords = explode(',', $keyword);
        $query->whereIn($row->field, $keywords);
        // @TODO Or filter in label and map options
    }

    /**
     * Filter checkbox
     *
     * @param Builder|QueryBuilder $query   Query
     * @param mixed                $keyword Keyword
     */
    protected function filterCheckbox(
        $query,
        $keyword,
        DataRow $row,
        DataType $dataType,
        Request $request
    ): void {
        // @TODO map on/off
        $options = $row->details;
        $query->when($keyword === '1' || $keyword === 'Yes', function ($query) use ($row, $options) {
            $query->where($row->field, $options->on ?? '1')->whereNotNull($row->field);
        })->when($keyword === '0' || $keyword === 'No', function ($query) use ($row, $options) {
            $query->where($row->field, $options->on ?? '0')->orWhereNull($row->field);
        });
    }
}
