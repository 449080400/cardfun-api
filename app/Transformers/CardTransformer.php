<?php

namespace App\Transformers;


class CardTransformer extends BaseTransformer
{
    protected $availableIncludes = ['user', 'category', 'tags'];

    public function transformData($model)
    {
        return [
            "id" => $model->id,
            "no" => $model->no,
            "category_id" => $model->category_id,
            "year" => $model->year,
            "title" => $model->title,
            "description" => $model->description,
            'cover' => $model->coverUrl,
            "review_count" => $model->review_count,
            "bid_count" => $model->bid_count,
            "favorite_count" => $model->favorite_count,
            "remind_count" => $model->remind_count,
            "price" => $model->price,
            "amount" => $model->amount,
            "bargain" => $model->bargain,
            "trade_method" => $model->trade_method,
            "payment_type" => $model->payment_type,
            "express_type" => $model->express_type,
            "freight" => $model->freight,
            "cycle" => $model->cycle,
            "user_id" => $model->user_id,
            "start_at" => !empty($model->start_at) ? $model->start_at->toDateTimeString() : '',
            "created_at" => !empty($model->created_at) ? $model->created_at->toDateTimeString() : '',
            "updated_at" => !empty($model->updated_at) ? $model->updated_at->toDateTimeString() : '',

        ];
    }

    public function includeUser($model)
    {
        return $this->item($model->user, new UserTransformer());
    }

    public function includeCategory($model)
    {
        return $this->item($model->category, new CategoryTransformer());
    }

    public function includeTags($model)
    {
        return $this->collection($model->tags, new TagTransformer());
    }

}
