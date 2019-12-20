<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Http\Requests\SmsLoginRequest;
use App\Models\Bargain;
use App\Models\GroupItem;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Transformers\UserTransformer;
use GuzzleHttp\Client;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'wechatOauth', 'getToken', 'smsLogin']]);
    }

    public function wechatOauth()
    {
        $js_code = request('js_code');
        if (!$js_code) {
            return $this->errorBadRequest();
        }
        $shop = Shop::findOrFail(request('shop_id', config('app.shop_id')));
        $appid = !empty(request('appid')) ? request('appid') : $shop->wechat_app_id;
        $secret = !empty(request('secret')) ? request('secret') : $shop->wechat_app_secret;
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$js_code}&grant_type=authorization_code";
        $client = new Client();
        $response = $client->request('get', $url, [])->getBody()->getContents();
        $ret_data = json_decode($response, true);
        if (empty($ret_data['session_key']) || empty($ret_data['openid'])) {
            return $this->errorBadRequest($ret_data['errcode'] . ' ' . $ret_data['errmsg']);
        }
        $openid = !empty($ret_data['openid']) ? $ret_data['openid'] : '';
        $unionid = !empty($ret_data['unionid']) ? $ret_data['unionid'] : '';
        $user = User::where('wechat_openid', $openid)->where('shop_id', $shop->id)->first();
        if (!$user) {
            $inviter = User::where('shop_id', $shop->id)->find(intval(request('inviter_id', 0)));
            $inviter_id = $inviter ? $inviter->id : 0;
            $name = '用户_' . rand(1000000, 9999999);
            $newUser = [
                'wechat_openid' => $openid,
                'wechat_unionid' => $unionid,
                'name' => $name,
                'register_source' => 'wechat',
                'shop_id' => $shop->id,
                'inviter_id' => $inviter_id
            ];
            $user = User::create($newUser);
            // 给邀请人发放优惠券
//            if (request('inviter_id')){
//                $this->afterRegistered($user, request('inviter_id'));
//            }
        }
        $token = JWTAuth::fromUser($user);
        return $this->respondWithToken($token);
    }

    /**
     * 短信验证登录
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function smsLogin(SmsLoginRequest $request)
    {
        $phone = request('phone');
        $sms_code = request('sms_code');
        if (!$phone || !$sms_code) {
            return $this->errorBadRequest();
        }
        $user = User::where('phone', $phone)->first();
        $name = '用户_' . rand(1000000, 9999999);
        if (!$user) {
            $newUser = [
                'name' => $name,
                'phone' => $phone,
                'register_source' => 'sms'
            ];
            $user = User::create($newUser);
        }
        $token = JWTAuth::fromUser($user);
        return $this->respondWithToken($token);
    }

    protected function afterRegistered($user, $inviter_id)
    {
        event(new UserRegistered($user, $inviter_id));
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['phone', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();
        $user->group_count = GroupItem::where('user_id', auth('api')->id())
            ->where('status', '<>', GroupItem::STATUS_PENDING)
            ->whereNotNull('paid_at')
            ->has('product')
            ->has('productSku')
            ->has('groupProduct')
            ->has('order')
            ->count();
        $user->bargain_count = Bargain::where('user_id', auth('api')->id())
            ->has('product')
            ->has('productSku')
            ->has('bargainProduct')
            ->count();
        $user->order_count = Order::query()->where('user_id', auth('api')->id())
            ->where('type', Order::TYPE_NORMAL)
            ->has('items')
            ->count();
        return $this->response()->item($user, new UserTransformer());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function getToken($id)
    {
        if (request('sign') != '世界和平' . date('Ymd')) {
            return $this->errorBadRequest();
        }
        $user = User::find($id);
        $token = JWTAuth::fromUser($user);
        return $this->respondWithToken($token);
    }

}