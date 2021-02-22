<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;

class MunicipalityApiController extends Controller {
    public function fetch(Request $request) {
        $rgnid = $request->rgnid;
        $provid = $request->provid;
        $municipality = Municipality::where('rgnid', $rgnid)->where('provid', $provid)->get();
        return response()->json($municipality, 200);
    }
}