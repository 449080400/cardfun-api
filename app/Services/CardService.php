<?php

namespace App\Services;

use App\Http\Requests\Request;
use App\Models\Card;
use App\Models\User;

class CardService
{
    public function store(User $user, Request $request)
    {
        // 开启一个数据库事务
        $card = \DB::transaction(function () use ($user, $request) {
            // 创建一个订单
            $card = new Card([
                'trade_method' => $request->trade_method,
                'payment_type' => $request->payment_type,
                'category_id' => $request->category_id,
                'year' => $request->year,
                'title' => $request->title,
                'long_title' => $request->long_title,
                'description' => $request->description,
                'cover' => $request->cover,
                'price' => $request->price,
                'amount' => $request->amount,
                'bargain' => $request->bargain ?: 1,
                'express_type' => $request->express_type,
                'freight' => $request->freight,
                'start_at' => $request->start_at,
                'cycle' => $request->cycle,
            ]);
            // 卡片关联到当前用户
            $card->user()->associate($user);
            // 写入数据库
            $card->save();

            return $card;
        });

        return $card;
    }

}
