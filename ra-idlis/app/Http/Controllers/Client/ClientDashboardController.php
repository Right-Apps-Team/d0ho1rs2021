<?php

namespace App\Http\Controllers\Client;
use Session;
use App\Http\Controllers\Controller;
use FunctionsClientController;
use App\Models\Regions;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Barangay;

class ClientDashboardController extends Controller {
    public function index() {
        $user_data = session()->get('uData');
        $data = [
            'user' => $user_data
        ];
        return view('dashboard.client.home', $data);
    }
    public function apply() {
        $user_data = session()->get('uData');
        $data = [
            'user' => $user_data
        ];
        return view('dashboard.client.apply', $data);
    }
    
    public function newApplication(String $appid = null) {
        $user_data = session()->get('uData');
        $data = [
            'user' => $user_data,
            'appFacName'=> FunctionsClientController::getDistinctByFacilityName(),
            'regions' => Regions::orderBy('sort')->get()
        ];
        return view('dashboard.client.newapplication', $data);
    }

}