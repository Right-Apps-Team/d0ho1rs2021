<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\CONEnvaluate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CONEnvaluateApiController extends Controller
{
    public function fetch(Request $request) {
        $id = $request->id;
        $con_evaluate = CONEnvaluate::where('id', $id)->get();
        return response()->json($con_evaluate, 200);
    }
}
