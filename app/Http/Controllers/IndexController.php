<?php

namespace App\Http\Controllers;

use App\Handlers\AmqpPublishHandler;
use App\Handlers\RabbitMqPublishHandler;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Tag;
use App\Services\CustomsService;
use App\Services\OrderCustomsService;
use App\Transformers\BannerTransformer;
use App\Transformers\CategoryTransformer;
use App\Transformers\ProductTransformer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use League\Fractal\Manager as FractalManager;
use League\Fractal\Resource\Collection;

class IndexController extends Controller
{
    use Helpers;
    public function __construct(FractalManager $fractal) {
        parent::__construct();
        $this->fractal = $fractal;
    }
    public function index()
    {
        $ret_data = [];
        $shop_id = intval(request('shop_id', config('app.shop_id')));
        // 首页分类
        $categories = Category::where('shop_id', $shop_id)->where('is_blocked', 0)->where('parent_id', null)->orderBy('order', 'desc')->get();
        $ret_data['categories'] = $this->fractal->createData(new Collection($categories, new CategoryTransformer()))->toArray();

        // 获取banners
        $banner_positions = [
            'index_top_banners'=>'index_top', // 首页-顶部
            'index_hot_sale_banners'=>'index_hot_sale', // 首页-热门畅销
            'index_snack_banners'=>'index_snack' // 首页-美味零食
        ];
        foreach ($banner_positions as $k=>$position){
            $banners = Banner::where('shop_id', $shop_id)->where('is_blocked', 0)->where('position', $position)->orderBy('order', 'desc')->take(6)->get();
            $ret_data[$k] = $this->fractal->createData(new Collection($banners, new BannerTransformer()))->toArray();
        }
        // 根据推荐标签获取商品
        $tag_positions = ['index_snack_products','index_new_products','index_hot_sale_products'];
        $tags = Tag::where('shop_id', $shop_id)->where('recommend', 1)->orderBy('order', 'desc')->take(3)->get();
        foreach ($tags as $k=>$tag){
            $tag_position = $tag_positions[$k];
            if (!$tag_position) break;
            $tag_products = Tag::find($tag->id)
                ->products()
                ->onSale()
                ->where('type', 'normal')
                ->orderBy('taggables.updated_at', 'desc')
                ->take(8)
                ->get();
            $ret_data[$tag_position] = $this->fractal->createData(new Collection($tag_products, new ProductTransformer()))->toArray();
            $ret_data[$tag_position]['tag_name'] = $tag->name;
        }
        return $ret_data;
    }

    public function index2(CustomsService $customsService)
    {
        $user = Auth::user();
        if ($user){
            $user->update([
                'last_actived_at'        => Carbon::now()
            ]);

        }
        $ret_data = [];
        $shop_id = intval(request('shop_id', config('app.shop_id')));
        // 首页分类
        $categories = Category::whereIn('shop_id', [$shop_id, Shop::MASTER_SHOP_ID])->where('is_blocked', 0)->where('parent_id', null)->orderBy('order', 'desc')->get();
        $ret_data['categories'] = $this->fractal->createData(new Collection($categories, new CategoryTransformer()))->toArray();

        // 获取banners
        $banner_positions = [
            'index_top'         => 'index_top', // 首页-顶部
            'index_position1'   => 'index_position1', // 首页-位置1
            'index_position2'   => 'index_position2', // 首页-位置2
            'index_position3'   => 'index_position3', // 首页-位置3
            'index_group'       => 'index_group', // 首页-团购
            'index_bargain'     => 'index_bargain', // 首页-砍价
        ];
        $index_banners = [];
        foreach ($banner_positions as $k=>$position){
            $position_banners = [];
            $position_banners['position'] = $position;
            $banners = Banner::whereIn('shop_id', [$shop_id, Shop::MASTER_SHOP_ID])->where('is_blocked', 0)->where('position', $position)->orderBy('order', 'desc')->take(6)->get();
            $position_banners['banners'] = $this->fractal->createData(new Collection($banners, new BannerTransformer()))->toArray();
            $index_banners[] = $position_banners;
        }
        $ret_data['banners'] = $index_banners;
        // 根据推荐标签获取商品
        $index_products = [];
        $tags = Tag::whereIn('shop_id', [$shop_id, Shop::MASTER_SHOP_ID])->where('recommend', 1)->orderBy('order', 'desc')->take(3)->get();
        foreach ($tags as $k=>$tag){
            $position_products = [];
            $position_products['tag_id'] = $tag->id;
            $position_products['tag_name'] = $tag->name;
            $products = Tag::find($tag->id)
                ->products()
                ->onSale()
                ->where('type', 'normal')
                ->whereIn('products.shop_id', [$shop_id, Shop::MASTER_SHOP_ID])
                ->orderBy('taggables.updated_at', 'desc')
                ->take(8)
                ->get();
            $position_products['products'] = $this->fractal->createData(new Collection($products, new ProductTransformer()))->toArray();
            $index_products[] = $position_products;
        }
        $ret_data['products'] = $index_products;
        return $ret_data;
    }
}
