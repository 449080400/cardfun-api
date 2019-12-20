<?php

namespace App\Http\Controllers;

use App\Transformers\CategoryTransformer;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        $category_id = intval($request['category_id']);
        if (!empty($category_id)){
            $categories = Category::where('is_blocked', 0)
                ->where('parent_id', $category_id)
                ->orderBy('order', 'desc')
                ->get();
        }else{
            $categories = Category::where('is_blocked', 0)
                ->whereNull('parent_id')
                ->orderBy('order', 'desc')
                ->get();
        }
        return $this->response()->collection($categories, new CategoryTransformer());

    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return $this->response()->item($category, new CategoryTransformer());
    }
}