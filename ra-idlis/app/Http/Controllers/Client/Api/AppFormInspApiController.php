<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\AppFormInsp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AppFormInspApiController extends Controller
{
    public function fetch(Request $request) {
        $apinsp_id = $request->apinsp_id;
        $appform_insp = AppFormInsp::where('apinsp_id', $apinsp_id)->get();
        return response()->json($appform_insp, 200);
    }
}
