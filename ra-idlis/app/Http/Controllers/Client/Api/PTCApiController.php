<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\PTC;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PTCApiController extends Controller
{
    public function fetch(Request $request) {
        $id = $request->id;
        $ptc = PTC::where('id', $id)->get();
        return response()->json($ptc, 200);
    }
}
