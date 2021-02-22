<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Http\Controllers\Controller;
use App\Models\ApplicationForm;
use Illuminate\Http\Request;

class ApplicationApiController extends Controller {
    public function check(Request $request) {
        $name = $request->name;
        $applications = ApplicationForm::where('facilityname', $name)->get();
        if(count($applications)) {
            return  response()->json(['message' => 'Facility name no longer available'], 400);
        }
        else {
            return  response()->json(['message' => 'Facility name is safe to use'], 200);
        }
        
    }
}