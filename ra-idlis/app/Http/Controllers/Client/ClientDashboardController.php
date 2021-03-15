<?php
namespace App\Http\Controllers\Client;
use Session;
use App\Http\Controllers\Controller;
use FunctionsClientController;
use App\Models\ApplicationForm;
use App\Models\Regions;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Barangay;
use App\Models\HFACIGroup;
use App\Models\FACLGroup;

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
            'appFacName'=> FunctionsClientController::getDistinctByFacilityName(),
            'regions'   => Regions::orderBy('sort')->get()
        ];
        return view('dashboard.client.newapplication', $data);
    }
    public function permitToConstruct() {
        $user_data = session()->get('uData');
        $hfser_id = 'PTC';

        $faclArr = [];
        $facl_grp = FACLGroup::where('hfser_id', $hfser_id)->select('hgpid')->get();
        foreach($facl_grp as $f) {
            array_push($faclArr, $f->hgpid);
        }

        $data = [
            'user'                  => $user_data,
            'appFacName'            => FunctionsClientController::getDistinctByFacilityName(),
            'regions'               => Regions::orderBy('sort')->get(),
            'hfaci_service_type'    => HFACIGroup::whereIn('hgpid', $faclArr)->get()
        ];
        // dd($hfaci_service_type);
        return view('dashboard.client.permit-to-construct', $data);
    }

    public function authorityToOperate() {
        $user_data = session()->get('uData');
        $hfser_id = 'ATO';

        $faclArr = [];
        $facl_grp = FACLGroup::where('hfser_id', $hfser_id)->select('hgpid')->get();
        foreach($facl_grp as $f) {
            array_push($faclArr, $f->hgpid);
        }

        $data = [
            'user'                  => $user_data,
            'appFacName'            => FunctionsClientController::getDistinctByFacilityName(),
            'regions'               => Regions::orderBy('sort')->get(),
            'hfaci_service_type'    => HFACIGroup::whereIn('hgpid', $faclArr)->get()
        ];
        // dd($hfaci_service_type);
        return view('dashboard.client.authority-to-operate', $data);
    }
}