<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // Rest omitted for brevity
    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            // 如果模型的 no 字段为空
            if (!$model->username) {
                // 调用 findAvailableNo 生成订单流水号
                $model->username = static::getAvailableUsername();
                // 如果生成失败，则终止创建订单
                if (!$model->username) {
                    return false;
                }
            }
        });
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'real_name', 'phone', 'gender', 'email', 'password', 'introduction', 'avatar', 'wechat_openid', 'wechat_unionid', 'last_shop_id', 'last_actived_at', 'inviter_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public static function getAvailableUsername()
    {
        do {
            $username =  rand(100000,99999999);

        } while (self::query()->where('username', $username)->exists());

        return $username;
    }

    public function _level()
    {
        return $this->hasOne(Level::class, 'level', 'level');
    }

//    public function cartItems()
//    {
//        return $this->hasMany(CartItem::class);
//    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'user_favorite_products')
            ->withTimestamps()
            ->orderBy('user_favorite_products.created_at', 'desc');
    }


    public function inviteItems()
    {
        return $this->hasMany(InviteItem::class);
    }

}