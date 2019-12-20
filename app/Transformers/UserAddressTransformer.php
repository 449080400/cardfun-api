<?php

namespace App\Transformers;

class UserAddressTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        return [
            'id' => $model->id,
            'province' => $model->province,
            'city' => $model->city,
            'district' => $model->district,
            'address' => $model->address,
            'full_address' => $model->fullAddress,
            'zip' => $model->zip,
            'contact_name' => $model->contact_name,
            'contact_phone' => $model->contact_phone,
            'real_name' => $model->real_name,
            'idcard_no' => $model->idcard_no,
            'last_used_at' => !empty($model->last_used_at) ? $model->last_used_at->toDateTimeString() : '',
            'is_default' => $model->is_default,
            'created_at' => !empty($model->created_at) ? $model->created_at->toDateTimeString() : '',
            'updated_at' => !empty($model->updated_at) ? $model->updated_at->toDateTimeString() : '',
        ];
    }
}