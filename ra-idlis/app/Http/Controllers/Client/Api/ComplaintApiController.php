<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\Complaints;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ComplaintsApiController extends Controller
{
    public function fetch(Request $request) {
        $cmp_id = $request->cmp_id;
        $complaints = Complaints::where('cmp_id', $cmp_id)->get();
        return response()->json($complaints, 200);
    }
}
