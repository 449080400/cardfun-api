<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Transformers\BannerTransformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use League\Fractal\Manager as FractalManager;

class BannersController extends Controller
{
    use Helpers;

    public function __construct(FractalManager $fractal)
    {
        $this->fractal = $fractal;
    }

    public function index(Request $request)
    {
        $query = Banner::where('is_blocked', 0);
        if ($request->position) {
            $query = $query->where('position', $request->position);
        }
        $query->orderBy('order', 'desc');
        $banners = $query->paginate(per_page(6));
        return $this->paginator($banners, new BannerTransformer());
    }

}