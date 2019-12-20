<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UserAddressRequest extends Request
{
    public function rules()
    {
        return [
            'province' => 'required',
            'city' => 'required',
            //'district'      => 'required',
            'address' => [
                'required',
                'regex:/^[A-Za-z0-9_ \x{4e00}-\x{9fa5}]+$/u',
            ],
            'zip' => [
                'required',
                'regex:/^\d{6}$/',
            ],
            'contact_name' => [
                'required',
                'regex:/^[A-Za-z0-9 \x{4e00}-\x{9fa5}]+$/u',
            ],
            'contact_phone' => [
                'required',
                'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\d{8}$/',
            ],
//            'real_name' => [
//                'regex:/^[A-Za-z\x{4e00}-\x{9fa5}]+$/u',
//            ],
//            'idcard_no' => [
//                'regex:/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/',
//            ]
        ];
    }

    public function attributes()
    {
        return [
            'province' => '省',
            'city' => '城市',
            'district' => '地区',
            'address' => '详细地址',
            'zip' => '邮编',
            'contact_name' => '收件人',
            'contact_phone' => '电话',
            'real_name' => '真实姓名',
            'idcard_no' => '身份证号',
        ];
    }

    /*public function messages()
    {
        return [
            'contact_phone.required' => '请填写电话'
        ];
    }*/
}
