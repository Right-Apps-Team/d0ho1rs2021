<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\Charge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChargeApiController extends Controller
{
    public function fetch(Request $request) {
        $chg_code = $request->chg_code;
        $charge = Charge::where('chg_code', $chg_code)->get();
        return response()->json($charge, 200);
    }
}
