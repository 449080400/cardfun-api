<?php

namespace App\Transformers;
use App\Models\BannerPosition;

class BannerPositionTransformer extends BaseTransformer
{

    public function transformData($model)
    {
        return [
            'name' => $model['name'],
            'code' => $model['code'],
        ];
    }
}
