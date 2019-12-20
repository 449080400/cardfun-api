<?php

namespace App\Http\Requests;


use App\Models\Card;
use Illuminate\Validation\Rule;

class CardRequest extends Request
{
    public function rules()
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_directory', 0)],
            'year' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'max:100'],
            'description' => ['required', 'max:10000'],
            'cover' => 'required',
            'price' => ['required', 'numeric', 'min:0.01'],
            'amount' => ['required', 'integer', 'min:1'],
            'bargain' => ['required', Rule::in([0, 1])],
            'express_type' => ['required', Rule::in(array_flip(Card::$expressTypeMap))],
            'freight' => ['numeric', 'min:0.01'],
            'trade_method' => ['required', Rule::in(array_flip(Card::$tradeMethodMap))],
            'payment_type' => ['required', Rule::in(array_flip(Card::$paymentTypeMap))],
            'start_at' => ['required', 'date', 'after_or_equal:today'],
            'cycle' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes()
    {
        return [
            'category_id' => '分类',
            'year' => '年份',
            'title' => '卡片标题',
            'cover' => '封面图',
            'price' => '价格',
            'amount' => '数量',
            'express_type' => '快递类型',
            'trade_method' => '交易方式',
            'payment_type' => '支付类型',
            'start_at' => '开始时间',
            'cycle' => '周期',
        ];
    }

    /*public function messages()
    {
        return [
            'contact_phone.required' => '请填写电话'
        ];
    }*/
}
