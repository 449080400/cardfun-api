<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Models\User;

class UserRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'between:1,25',
            'introduction' => 'max:255',
            'avatar' => 'max:500',
            'phone' => [
                'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\d{8}$/',
            ]
        ];
    }

    public function attributes()
    {
        return [
            'name' => '用户名',
            'introduction' => '简介',
            'avatar' => '头像',
            'phone' => '手机号',
        ];
    }
}
