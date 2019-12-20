<?php

namespace App\Providers;

use App\Models\Bargain;
use App\Models\Card;
use App\Models\Group;
use App\Models\GroupItem;
use App\Models\Order;
use App\Models\UserAddress;
use App\Models\UserIdentity;
use App\Policies\BargainPolicy;
use App\Policies\CardPolicy;
use App\Policies\GroupItemPolicy;
use App\Policies\GroupPolicy;
use App\Policies\OrderPolicy;
use App\Policies\UserAddressPolicy;
use App\Policies\UserIdentityPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model'         => 'App\Policies\ModelPolicy',
        Card::class        => CardPolicy::class,
        UserAddress::class  => UserAddressPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
