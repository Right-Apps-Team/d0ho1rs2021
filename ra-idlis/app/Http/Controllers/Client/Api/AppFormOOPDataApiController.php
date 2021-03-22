<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\AppFormOOPData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AppFormOOPDataApiController extends Controller
{
    public function fetch(Request $request) {
        $appfoop_id = $request->appfoop_id;
        $appform_oopdata = AppFormOOPData::where('appfoop_id', $appfoop_id)->get();
        return response()->json($appform_oopdata, 200);
    }
}
