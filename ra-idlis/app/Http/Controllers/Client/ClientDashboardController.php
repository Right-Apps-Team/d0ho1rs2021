<?php

namespace App\Http\Controllers\Client;
use Session;
use App\Http\Controllers\Controller;
use FunctionsClientController;

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
    
    public function newApplication() {
        $user_data = session()->get('uData');
        $data = [
            'user' => $user_data,
            'appFacName'=>FunctionsClientController::getDistinctByFacilityName()
        ];
        return view('dashboard.client.newapplication', $data);
    }
}