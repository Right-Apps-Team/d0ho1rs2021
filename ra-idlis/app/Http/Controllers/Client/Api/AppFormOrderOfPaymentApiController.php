<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\AppFormOrderOfPayment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AppFormOrderOfPaymentApiController extends Controller
{
    public function fetch(Request $request) {
        $appop_id = $request->appop_id;
        $appform_orderofpayment = AppFormOrderOfPayment::where('appop_id', $appop_id)->get();
        return response()->json($appform_orderofpayment, 200);
    }
}
