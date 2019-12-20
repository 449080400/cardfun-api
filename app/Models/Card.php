<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Card extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $dates = ['start_at'];

    // 快递类型
    const EXPRESS_TYPE_FREE_SHIPPING = 'free_shipping';
    const EXPRESS_TYPE_SF_COD = 'sf_cod';
    const EXPRESS_TYPE_FREIGHT = 'freight';
    public static $expressTypeMap = [
        self::EXPRESS_TYPE_FREE_SHIPPING => '包邮',
        self::EXPRESS_TYPE_SF_COD => '顺丰到付',
        self::EXPRESS_TYPE_FREIGHT => '运费金额',
    ];

    // 交易方式
    const TRADE_METHOD_FIXED_PRICE = 'fixed_price';
    const TRADE_METHOD_AUCTION = 'auction';
    public static $tradeMethodMap = [
        self::TRADE_METHOD_FIXED_PRICE => '一口价',
        self::TRADE_METHOD_AUCTION => '竞拍',
    ];

    // 支付类型
    const PAYMENT_TYPE_ONLINE = 'online';
    const PAYMENT_TYPE_OFFLINE = 'offline';
    public static $paymentTypeMap = [
        self::PAYMENT_TYPE_ONLINE => '线上担保',
        self::PAYMENT_TYPE_OFFLINE => '线下交易',
    ];

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            // 如果模型的 no 字段为空
            if (!$model->no) {
                // 调用 findAvailableNo 生成订单流水号
                $model->no = static::findAvailableNo();
                // 如果生成失败，则终止创建订单
                if (!$model->no) {
                    return false;
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault();
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function getCoverUrlAttribute()
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['cover'], ['http://', 'https://'])) {
            return $this->attributes['cover'];
        }

        return config('api.img_host') . $this->attributes['cover'];
    }

    public static function findAvailableNo()
    {
        $max_id = Card::withTrashed()->max('id');
        $max_id = $max_id ? ++$max_id : 1;
        $prefix = date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            $no = str_pad($max_id, 8, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
            usleep(100);
        }
        \Log::warning(sprintf('find card no failed'));

        return false;
    }

    public function scopeOnSale($query)
    {
        return $query->where('on_sale', 1);
    }

}
