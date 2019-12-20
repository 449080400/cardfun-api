<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'App\Http\Controllers'], function ($api) {

        $api->group(['middleware' => 'api.auth'], function ($api) {
            // 地址管理
            $api->get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
            $api->post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
            $api->get('user_addresses/{id}', 'UserAddressesController@show')->name('user_addresses.show');
            $api->put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
            $api->delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');

            // 卡片管理
            $api->post('cards', 'CardsController@store')->name('cards.store');
            $api->put('cards/{card}', 'CardsController@update')->name('cards.update');
            $api->delete('cards/{card}', 'CardsController@destroy')->name('cards.destroy');

            // 订单模块
            $api->get('orders/confirm', 'OrdersController@confirm')->name('orders.confirm');
            $api->get('orders/confirm/groups', 'OrdersController@confirmGroups')->name('orders.confirm.groups');
            $api->get('orders/confirm/bargains', 'OrdersController@confirmBargains')->name('orders.confirm.bargains');
            $api->post('orders', 'OrdersController@store')->name('orders.store');
            $api->get('orders', 'OrdersController@index')->name('orders.index');
            $api->get('orders/{id}', 'OrdersController@show')->name('orders.show');
            $api->get('orders/{id}/closed', 'OrdersController@closed')->name('orders.closed');
            $api->delete('orders/{id}', 'OrdersController@destroy')->name('orders.destroy');
            $api->post('group_orders', 'OrdersController@group')->name('group_orders.store');
            $api->post('bargain_orders', 'OrdersController@bargain')->name('bargain_orders.store');
            $api->post('orders/{id}/apply_refund', 'OrdersController@applyRefund')->name('orders.apply_refund');

            // 支付模块
            $api->get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
            $api->get('payment/{order}/wechat', 'PaymentController@payByWechat')->name('payment.wechat');
            $api->get('payment/{order}/baofu', 'PaymentController@payByBaofu')->name('payment.baofu');


            // 用户模块
            $api->get('users', 'UsersController@index');
            $api->put('users', 'UsersController@update');

            // 用户授权
            $api->get('me', 'AuthController@me');
            $api->get('logout', 'AuthController@logout');
            $api->get('refresh', 'AuthController@refresh');

            // 图片上传
            $api->post('uploads/image', 'UploadsController@image')->name('uploads.image');

        });
        // 用户认证
        $api->post('auth/sms_login', 'AuthController@smsLogin')->name('auth.sms_login');
        $api->get('register', 'AuthController@register');
        $api->get('wechat_oauth', 'AuthController@wechatOauth');
        $api->get('login', 'AuthController@login');
        $api->get('token/{id}', 'AuthController@getToken');

        // 首页
        $api->get('index', 'IndexController@index')->name('index');

        // 商品展示
        $api->get('products', 'ProductsController@index')->name('products.index');
        $api->get('products/{id}', 'ProductsController@show')->name('products.show');
        $api->get('products/{id}/similars', 'ProductsController@similars')->name('products.similars');
        $api->get('groups', 'GroupsController@index')->name('groups.index');
        $api->get('groups/{id}', 'GroupsController@show')->name('groups.show');

        // 分类
        $api->get('categories', 'CategoriesController@index');
        $api->get('categories/{id}', 'CategoriesController@show');

        // 卡片展示
        $api->get('cards', 'CardsController@index')->name('cards.index');
        $api->get('cards/{id}', 'CardsController@show')->name('cards.show');

        // banner
        $api->get('banners', 'BannersController@index')->name('banners.index');

        // 支付回调
        $api->post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');
        $api->post('payment/wechat/notify', 'PaymentController@wechatNotify')->name('payment.wechat.notify');
        $api->get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
        $api->post('payment/wechat/refund_notify', 'PaymentController@wechatRefundNotify')->name('payment.wechat.refund_notify');
        $api->post('payment/baofu/notify', 'PaymentController@baofuNotify')->name('payment.baofu.notify');
        $api->post('orders/{id}/refund', 'OrdersController@handleRefund')->name('orders.handle_refund');

    });

});