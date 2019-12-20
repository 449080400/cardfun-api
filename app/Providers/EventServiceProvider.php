<?php

namespace App\Providers;

use App\Events\OrderPaid;
use App\Events\UserRegistered;
use App\Listeners\SendInviteCoupon;
use App\Listeners\UpdataProductSkuStock;
use App\Listeners\UpdateProductSoldCount;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
