<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\AppType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AppTypeApiController extends Controller
{
    public function fetch(Request $request) {
        $aptid = $request->aptid;
        $apptype = AppType::where('aptid', $aptid)->get();
        return response()->json($apptype, 200);
    }
}
