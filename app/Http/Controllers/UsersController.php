<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Transformers\UserTransformer;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Intervention\Image\Facades\Image;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    use Helpers;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->response()->item(auth('api')->user(), new UserTransformer());
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request)
    {
        //验证表单
        $user = auth('api')->user();
        if (!empty($request->input('name'))) $user->name = $request->input('name');
        if (!empty($request->input('gender')) || $request->input('gender') === '0') $user->gender = intval($request->input('gender'));
        if (!empty($request->input('phone'))) $user->phone = $request->input('phone');
        if (!empty($request->input('avatar'))) $user->avatar = $request->input('avatar');
        if (!empty($request->input('introduction'))) $user->introduction = $request->input('introduction');
        $ret = $user->save();
        if ($ret){
            $user = User::findOrFail($user->id);
        }
        return $this->response()->item($user, new UserTransformer());
    }


}
