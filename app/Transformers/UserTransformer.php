<?php

namespace App\Transformers;

class UserTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'username' => $model->username,
            'phone' => $model->phone,
            'email' => $model->email,
            'gender' => $model->gender,
            'avatar' => $model->avatar,
            'introduction' => $model->introduction,
            'last_actived_at' => $model->last_actived_at,
            'created_at' => $model->created_at->toDateTimeString(),
            'updated_at' => $model->updated_at->toDateTimeString(),
        ];
    }
}