<?php

namespace App\Transformers;

class TagTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
        ];
    }
}