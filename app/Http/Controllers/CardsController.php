<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardRequest;
use App\Http\Requests\UserAddressRequest;
use App\Models\Card;
use app\models\User;
use App\Models\UserAddress;
use App\Services\CardService;
use App\Transformers\CardTransformer;
use App\Transformers\UserAddressTransformer;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    public function index(Request $request)
    {
        $keyword = strval($request['keyword']);
        $category_id = intval($request['category_id']);
        $tag_id = intval($request['tag_id']);
        $order = strval($request['order']);
        $min_price = strval($request['min_price']);
        $max_price = strval($request['max_price']);
        $type = !empty($request['type']) ? strval($request['type']) : 'normal';
        $query = Card::onSale();
        if ($min_price) {
            $query->where('price', '>=', $min_price);
        }
        if ($max_price) {
            $query->where('price', '<=', $max_price);
        }
        if ($category_id) {
            $categories = Category::where('parent_id', $category_id)->get();
            $collection = collect($categories);
            $collection = $collection->pluck(['id']);
            $collection->push($category_id);
            $query = $query->whereIn('category_id', $collection);
        }

        if ($tag_id) {
            $query = $query->whereHas('tags', function ($query) use ($tag_id) {
                $query->where('id', $tag_id);
            });
        }

        if ($keyword) {
            $query = $query->where('title', 'like', '%' . $keyword . '%');
        }

        if ($order) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 调用查询构造器的排序
                    $query->orderBy($m[1], $m[2]);
                }
            }
        }
        $cards = $query->paginate(per_page());
        return $this->response()->paginator($cards, new CardTransformer());
    }

    public function show($id)
    {
        $card = Card::findOrFail($id);
        return $this->response()->item($card, new CardTransformer());
    }

    public function store(CardRequest $request, CardService $cardService)
    {
        $user = User::find(auth('api')->id());
        $card = $cardService->store($user, $request);
        $card = Card::findOrFail($card->id);
        $cardTransformer = new CardTransformer();
        return $this->response()->item($card, $cardTransformer);
    }

    public function update($id, CardRequest $request)
    {
        // todo
        // return $this->response()->item();
    }

    public function destroy($id)
    {
        $card = Card::findOrFail($id);
        $this->authorize('own', $card);
        $card->delete();
        return $this->response->noContent();
    }


}
