<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Models\CONEnvalSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CONEnvalSaveApiController extends Controller
{
    public function fetch(Request $request) {
        $id = $request->id;
        $con_evalsave = CONEnvalSave::where('id', $id)->get();
        return response()->json($con_evalsave, 200);
    }
}
