<?php

namespace App\Transformers;
use App\Models\Banner;

class BannerTransformer extends BaseTransformer
{

    public function transformData($model)
    {
        return [
            'id'   => $model['id'],
            'title'   => $model['title'],
            'cover' => !empty($model->cover) ? config('api.img_host').$model->cover : '',
            'jump_type' => $model['jump_type'],
            'jump_link' => $model['jump_link'],
        ];
    }

    public function includePosition($model)
    {
        return $this->item($model->position, new BannerPositionTransformer());
    }
}
