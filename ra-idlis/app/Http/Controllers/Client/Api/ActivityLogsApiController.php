<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use Illuminate\Http\Request;
use Ap\Models\ActivityLog;
use App\Http\Controllers\Controller;

class ActivityLogsApiController extends Controller
{
    public function fetch(Request $request) {
        $actid = $request->actid;
        $activitylogs = ActivityLog::where('actid', $actid)->get();
        return response()->json($activitylogs, 200);
    }
}
