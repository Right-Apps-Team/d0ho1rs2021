<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryApiController extends Controller
{
    public function fetch(Request $request) {
        $cat_id = $request->cat_id;
        $category = Category::where('cat_id', $cat_id)->get();
        return response()->json($category, 200);
    }
}
