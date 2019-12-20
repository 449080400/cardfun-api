<?php

namespace App\Transformers;


class CategoryTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'logo' => !empty($model->logo) ? config('api.img_host').$model->logo : '',
            'parent_id' => $model->parent_id,
            'description' => $model->description,
            'created_at' => $model->created_at ? $model->created_at->toDateTimeString() : '',
            'updated_at' => $model->updated_at ? $model->updated_at->toDateTimeString() : '',
        ];
    }
}