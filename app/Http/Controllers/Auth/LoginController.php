<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Dingo\Api\Routing\Helpers;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    use Helpers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
//    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout', 'me');
    }

    public function login(){
        $credentials = request(['phone', 'password']);

        if (! $token = auth('api')->attempt($credentials)) {
            return $this->sendFailResponse('µÇÂ¼Ê§°Ü');
        }
        return $this->response->array(['token'=>$token]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }


    protected function sendFailResponse($message){
        return $this->response->error($message, 400);
    }

    protected function respondWithToken($token, $message, $status)
    {
        return $this->response->array([
            'token' => $token,
            'message' => $message,
            'status_code' => $status
        ]);
    }
}
