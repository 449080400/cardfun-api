<?php

namespace App\Http\Controllers;

use App\Jobs\CloseGroup;
use App\Models\Bargain;
use App\Models\Group;
use App\Models\GroupItem;
use App\Models\User;
use App\Services\BaofuService;
use App\Services\CustomsService;
use App\Services\OrderCustomsService;
use App\Services\SplitOrderService;
use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use App\Events\OrderPaid;
use Yansongda\Pay\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        $except = ['payment.wechat.notify', 'payment.wechat.refund_notify', 'payment.baofu.notify'];
        if (!in_array(request()->route()->getAction()['as'], $except)){

        }
    }
    public function payByAlipay($id, Request $request)
    {

        // 订单信息
        $order = Order::findOrFail($id);
        // 校验权限
        $this->authorize('own', $order);

        // 判断订单的发货状态是否未付款
        if ($order->paid_at) {
            return $this->errorBadRequest('订单已付款');
        }
        if ($order->closed) {
            return $this->errorBadRequest('订单已关闭');
        }

        $order->update([
            'payment_method' => 'alipay', // 支付方式
        ]);

        // 调用支付宝的网页支付
        return app('alipay')->web([
            'out_trade_no' => $order->no, // 订单编号，需保证在商户端不重复
            'total_amount' => $order->total_amount, // 订单金额，单位元，支持小数点后两位
            'subject'      => '支付 飞熊学院 的订单：'.$order->no, // 订单标题
        ]);
    }

    public function payByWechat($id, Request $request)
    {
        // 订单信息
        $order = Order::findOrFail($id);
        $user = User::findOrFail($order->user_id);
        // 校验权限
        $this->authorize('own', $order);

        // 判断订单的发货状态是否未付款
        if ($order->paid_at || $order->closed) {
            return $this->errorBadRequest('订单状态不正确');
        }
        $order->update([
            'payment_method' => 'wechat', // 支付方式
            'suffix' => time(), // 订单号
        ]);
        // 之前是直接返回，现在把返回值放到一个变量里
        $wechatOrder = app('wechat_pay')->miniapp([
            'out_trade_no' => $order->no.'_'.$order->suffix,
            'total_fee'    => $order->total_amount * 100,
            'body'         => '支付跨境直通车订单：'.$order->no,
            'openid' => $user->wechat_openid,
        ]);
        return response($wechatOrder);
    }

    public function payByBaofu($id, Request $request, BaofuService $baofuService)
    {
        // 订单信息
        $order = Order::findOrFail($id);
        $user = User::findOrFail($order->user_id);
        // 校验权限
        $this->authorize('own', $order);

        // 判断订单的发货状态是否未付款
        if ($order->paid_at || $order->closed) {
            return $this->errorBadRequest('订单状态不正确');
        }
        $order->update([
            'payment_method' => 'baofu_wechat', // 支付方式
            'suffix' => time(), // 订单号
        ]);
        //$order->total_amount = 0.01;
        $wechatOrder = $baofuService->miniapp([
            'trade_no'      => $order->no,
            'out_trade_no'  => $order->no.'_'.$order->suffix,
            'total_fee'     => $order->total_amount,
            'body'          => '支付Mobius的订单：'.$order->no,
            'openid'        => $user->wechat_openid,
            'user_id'       => $user->id,
            'username'      => $user->username,
            'wechat_app_id' => $user->shop->wechat_app_id,
        ]);
        return response($wechatOrder);
    }

    public function alipayReturn()
    {
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        //return view('pages.success', ['msg' => '付款成功']);
        return redirect('http://web.feixiongedu.com/User/MyOrder');
    }

    public function alipayNotify()
    {
        // 校验输入参数
        $data  = app('alipay')->verify();
        // $data->out_trade_no 拿到订单流水号，并在数据库中查询
        $order = Order::where('no', $data->out_trade_no)->first();
        // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
        if (!$order) {
            return 'fail';
        }
        // 如果这笔订单的状态已经是已支付
        if ($order->paid_at) {
            // 返回数据给支付宝
            return app('alipay')->success();
        }

        $order->update([
            'paid_at'        => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no'     => $data->trade_no, // 支付宝订单号
            'status'              => 'completed',
            'refund_status'       => 'completed',
            'closed'       => true,
        ]);
        $this->afterPaid($order);

        return app('alipay')->success();
    }

    public function wechatNotify(SplitOrderService $splitOrderService, CustomsService $customsService)
    {
        // 校验回调参数是否正确
        $data  = app('wechat_pay')->verify();
        $out_trade_no = explode('_', $data->out_trade_no);
        $no = $out_trade_no[0];
        $suffix = !empty($out_trade_no[1]) ? $out_trade_no[1] : '';
        // 找到对应的订单
        $order = Order::where('no', $no)->first();
        // 订单不存在则告知微信支付
        if (!$order) {
            return 'fail';
        }
        // 订单已支付
        if ($order->paid_at) {
            // 告知微信支付此订单已处理
            return app('wechat_pay')->success();
        }
        // 将订单标记为已支付
        $order->update([
            'paid_at'        => Carbon::now(),
            'payment_method' => 'wechat',
            'payment_no'     => $data->transaction_id,
            'suffix' => $suffix,
            'pay_notify_data' => $data,
        ]);
        $order = Order::find($order->id);
        $customsService->payClearance($order);
        // 处理支付成功之后的逻辑
        if ($order->type == 'normal'){
            $this->afterPaid($order);
        }else if($order->type == 'group'){
            // 更新开团记录
            $group_item = GroupItem::where('user_id', $order->user_id)
                ->where('order_id',$order->id)
                ->first();
            if ($group_item->status == GroupItem::STATUS_PENDING){
                $group_item->update([
                    'status'    => GroupItem::STATUS_WAITING,
                    'paid_at'   => Carbon::now(),
                ]);
                $order->update([
                    'group_id' => $group_item->id,
                ]);
            }

            // 开团信息
            $group = Group::where('id', $group_item->group_id)->first();
            if ($group){
                $data = [];
                if ($group->user_id == $order->user_id && $group->status == Group::STATUS_PENDING){
                    $data['paid_at'] = Carbon::now();
                    $data['status'] = GroupItem::STATUS_WAITING;
                    // 定时关闭砍价
                    dispatch(new CloseGroup($group, config('app.group_ttl')));
                }
                $group_items = GroupItem::where('group_id', $group->id)
                    ->whereNotNull('paid_at')
                    ->where('closed',false)
                    ->get();
                $data['user_count'] = count($group_items);
                if (count($group_items) == $group->target_count){
                    $data['status'] = GroupItem::STATUS_SUCCESS;
                    $data['finished_at'] = Carbon::now();
                    // 开团成功
                    foreach ($group_items as $group_item){
                        $group_item->update([
                            'status'    => GroupItem::STATUS_SUCCESS,
                        ]);
                        $order = Order::find($group_item->order_id);
                        $this->afterPaid($order);
                    }
                }
                $group->update($data);
            }
        }else if ($order->type == 'bargain'){
            // 更新砍价信息
            $bargain = Bargain::where('user_id', $order->user_id)
                ->where('order_id',$order->id)
                ->first();
            $bargain->update([
                'paid_at'   => Carbon::now(),
                'finished_at'   => Carbon::now(),
            ]);
            $this->afterPaid($order);
        }
        // 订单拆分
        //$splitOrderService->split($order);
        // 报关
        //$customsService->clearance($order);
        return app('wechat_pay')->success();
    }

    public function baofuNotify(BaofuService $baofuService, SplitOrderService $splitOrderService)
    {
        // 校验回调参数是否正确
        $data = $baofuService->verify();
        $no = $data['trans_id'];
        // 找到对应的订单
        $order = Order::where('no', $no)->first();
        // 订单不存在则告知微信支付
        if (!$order) {
            return 'fail';
        }
        // 订单已支付
        if ($order->paid_at) {
            // 告知微信支付此订单已处理
            return $baofuService->success();
        }
        // 将订单标记为已支付
        $order->update([
            'paid_at'        => Carbon::now(),
            'payment_method' => 'baofu_wechat',
            'payment_no'     => $data['bank_voucher_no'],
        ]);

        // 处理支付成功之后的逻辑
        if ($order->type == 'normal'){
            $this->afterPaid($order);
        }else if($order->type == 'group'){
            // 更新开团记录
            $group_item = GroupItem::where('user_id', $order->user_id)
                ->where('order_id',$order->id)
                ->first();
            if ($group_item->status == GroupItem::STATUS_PENDING){
                $group_item->update([
                    'status'    => GroupItem::STATUS_WAITING,
                    'paid_at'   => Carbon::now(),
                ]);
                $order->update([
                    'group_id' => $group_item->id,
                ]);
            }

            // 开团信息
            $group = Group::where('id', $group_item->group_id)->first();
            if ($group){
                $data = [];
                if ($group->user_id == $order->user_id && $group->status == Group::STATUS_PENDING){
                    $data['paid_at'] = Carbon::now();
                    $data['status'] = GroupItem::STATUS_WAITING;
                    // 定时关闭砍价
                    dispatch(new CloseGroup($group, config('app.group_ttl')));
                }
                $group_items = GroupItem::where('group_id', $group->id)
                    ->whereNotNull('paid_at')
                    ->where('closed',false)
                    ->get();
                $data['user_count'] = count($group_items);
                if (count($group_items) == $group->target_count){
                    $data['status'] = GroupItem::STATUS_SUCCESS;
                    $data['finished_at'] = Carbon::now();
                    // 开团成功
                    foreach ($group_items as $group_item){
                        $group_item->update([
                            'status'    => GroupItem::STATUS_SUCCESS,
                        ]);
                        $order = Order::find($group_item->order_id);
                        $this->afterPaid($order);
                    }
                }
                $group->update($data);
            }
        }else if ($order->type == 'bargain'){
            // 更新砍价信息
            $bargain = Bargain::where('user_id', $order->user_id)
                ->where('order_id',$order->id)
                ->first();
            $bargain->update([
                'paid_at'   => Carbon::now(),
                'finished_at'   => Carbon::now(),
            ]);
            $this->afterPaid($order);
        }
        // 订单拆分
        $splitOrderService->split($order);
        return $baofuService->success();
    }

    protected function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }

    public function wechatRefundNotify(Request $request)
    {
        // 给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        $data = app('wechat_pay')->verify(null, true);

        // 没有找到对应的订单，原则上不可能发生，保证代码健壮性
        $out_trade_no = explode('_', $data['out_trade_no']);
        $no = $out_trade_no[0];
        if(!$order = Order::where('no', $no)->first()) {
            return $failXml;
        }

        if ($data['refund_status'] === 'SUCCESS') {
            // 退款成功，将订单退款状态改成退款成功
            $order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
            ]);
        } else {
            // 退款失败，将具体状态存入 extra 字段，并表退款状态改成失败
            $extra = $order->extra;
            $extra['refund_failed_code'] = $data['refund_status'];
            $order->update([
                'refund_status' => Order::REFUND_STATUS_FAILED,
            ]);
        }

        return app('wechat_pay')->success();
    }

}
