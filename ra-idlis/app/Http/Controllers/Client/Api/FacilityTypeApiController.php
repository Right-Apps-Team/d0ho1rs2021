<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\FacilityType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FacilityTypeApiController extends Controller
{
    public function fetch(Request $request) {
        $facid = $request->facid;
        $facilitytyp = FacilityType::where('facid', $facid)->get();
        return response()->json($facilitytyp, 200);
    }
}
