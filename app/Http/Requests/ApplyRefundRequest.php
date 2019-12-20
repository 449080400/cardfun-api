<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ApplyRefundRequest extends Request
{
    public function rules()
    {
        return [
            'reason'      => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'reason'      => '原因',
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => '请填写退款原因'
        ];
    }

}
