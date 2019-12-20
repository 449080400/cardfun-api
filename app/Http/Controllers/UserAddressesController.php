<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressRequest;
use App\Models\UserAddress;
use App\Transformers\UserAddressTransformer;
use Illuminate\Http\Request;
use App\Models\User;

class UserAddressesController extends Controller
{
    public function index(Request $request)
    {
        $userAddress = UserAddress::where('user_id', auth('api')->id());
        if ($request['is_default'] == 1){
            $userAddress->where('is_default',1);
        }
        $userAddress->orderBy('is_default', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->orderBy('created_at', 'desc');
        $userAddress = $userAddress->paginate(per_page());
        return $this->response()->paginator($userAddress, new UserAddressTransformer());
    }
    public function show($id)
    {
        $user_address = UserAddress::findOrFail($id);
        $this->authorize('own', $user_address);
        return $this->response()->item($user_address, new UserAddressTransformer());
    }

    public function store(UserAddressRequest $request)
    {

        $user_address = $request->user()->addresses()->create($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
            'real_name',
            'idcard_no',
            'is_default',
        ]));
        if ($request['is_default'] == 1 && $user_address){
            $request->user()->addresses()->where('id','!=',$user_address->id)->update(
                ['is_default' => 0]
            );
        }
        return $this->response()->item($user_address, new UserAddressTransformer());
    }

    public function update($id, UserAddressRequest $request)
    {
        $user_address = UserAddress::findOrFail($id);
        $this->authorize('own', $user_address);
        $user_address->update($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
            'real_name',
            'idcard_no',
            'is_default',
        ]));
        if ($request['is_default'] == 1 && $user_address){
            $request->user()->addresses()->where('id','!=',$user_address->id)->update(
                ['is_default' => 0]
            );
        }
        return $this->response()->item($user_address, new UserAddressTransformer());
    }

    public function destroy($id)
    {
        $user_address = UserAddress::findOrFail($id);
        $this->authorize('own', $user_address);
        $user_address->delete();
        return $this->response->noContent();
    }


}
