<?php
namespace App\Http\Controllers\Client\Api;

use Session;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DOHController;
use App\Http\Controllers\FunctionsClientController;
use App\Models\ActivityLog;
use App\Models\ApplicationForm;
use Illuminate\Http\Request;
use App\Models\Regions;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Barangay;
use App\Models\CONCatchment;
use App\Models\CONHospital;
use App\Models\Classification;
use App\Models\FacIds;
use App\Models\x08Ft;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Redirect;

class LtoAppController extends Controller
{
    public function check(Request $request)
    {
        $name = $request->name;
        $applications = ApplicationForm::where('facilityname', $name)->get();
        if (count($applications)) {
            return  response()->json(['message' => 'Facility name no longer available'], 400);
        } else {
            return  response()->json(['message' => 'Facility name is safe to use'], 200);
        }
    }
    public function fetch(Request $request)
    {
        $app = [];
        $cities = [];
        $provinces = [];
        $brgy = [];
        $classification = [];
        $subclass = [];
        if ($id = $request->appid) {
            $app = ApplicationForm::where('appid', $id)->first();
        }
        if (isset($app->rgnid)) {
            $provinces = Province::where('rgnid', $app->rgnid)->get();
        }
        if (isset($app->provid)) {
            $cities = Municipality::where('provid', $app->provid)->get();
        }
        if (isset($app->cmid)) {
            $brgy = Barangay::where('cmid', $app->cmid)->get();
        }
        if (isset($app->ocid)) {
            $classification = Classification::where('ocid',  $app->ocid)->where('isSub', null)->get();
        }
        if (isset($app->ocid) && isset($app->classid)) {
            $subclass = Classification::where('ocid', $app->ocid)->where('isSub',  $app->classid)->get();
        }
        $con_catchment = CONCatchment::where('appid', $id)->get();
        $con_hospital = CONHospital::where('appid', $id)->get();

        return  response()->json(
            [
                'application'   => $app,
                'provinces'     => $provinces,
                'cities'        => $cities,
                'brgy'          => $brgy,
                'classification' => $classification,
                'subclass'      => $subclass,
                'con_catchment' => $con_catchment,
                'con_hospital'  => $con_hospital
            ],
            200
        );
    }
    public function save(Request $request)
    {
        if (isset($request->appid)) {
            $appform = ApplicationForm::where('appid', $request->appid)->first();

            // DB::insert('insert into x08_ft (uid, appid, facid) values (?, ?, ?)', ['fds', 'ff', 'fds']);
        } else {
            $appform = new ApplicationForm;
        }


        $appform->hfser_id              = $request->hfser_id;
        $appform->facilityname          = $request->facilityname;
        $appform->rgnid                 = $request->rgnid;
        $appform->provid                = $request->provid;
        $appform->cmid                  = $request->cmid;
        $appform->brgyid                = $request->brgyid;
        $appform->street_number         = $request->street_number;
        $appform->street_name           = $request->street_name;
        $appform->zipcode               = $request->zipcode;
        $appform->contact               = $request->contact;
        $appform->areacode              = $request->areacode;
        $appform->landline              = $request->landline;
        $appform->faxnumber             = $request->faxnumber;
        $appform->email                 = $request->email;
        // $appform->facid                 = $request->facid;
        $appform->cap_inv               = $request->cap_inv;
        $appform->lot_area              = $request->lot_area;
        $appform->noofbed               = $request->noofbed;
        $appform->uid                   = $request->uid;
        $appform->ocid                  = $request->ocid;
        $appform->classid               = $request->classid;
        $appform->subClassid            = $request->subClassid;
        $appform->facmode               = $request->facmode;
        $appform->funcid                = $request->funcid;
        $appform->owner                 = $request->owner;
        $appform->ownerMobile           = $request->ownerMobile;
        $appform->ownerLandline         = $request->ownerLandline;
        $appform->ownerEmail            = $request->ownerEmail;
        $appform->mailingAddress        = $request->mailingAddress;
        $appform->approvingauthoritypos = $request->approvingauthoritypos;
        $appform->approvingauthority    = $request->approvingauthority;
        $appform->hfep_funded           = $request->hfep_funded;
        $appform->draft                 = $request->draft;

        // LTO update 5-12-2021
        $appform->ptcCode               = $request->ptcCode;
        $appform->noofmain              = $request->noofmain;
        $appform->noofsatellite         = $request->noofsatellite;

        $appform->typeamb               = $request->typeamb;
        $appform->ambtyp                = $request->ambtyp;
        $appform->plate_number          = $request->plate_number;
        $appform->ambOwner              = $request->ambOwner;
        $appform->addonDesc             = $request->addonDesc;
        $appform->savingStat            = $request->saveas;
        $appform->noofdialysis          = $request->noofdialysis;
        
        $appform->assignedRgn           = $request->assignedRgn;
        $appform->aptid                 = $request->aptid;
  
        if($request->saveas == 'final'){
            $appform->draft = null;
        }

        $appform->save();

        $facid = json_decode($request->facid, true);
      
        if(count($facid) > 0){
           $this->ltoAppDetSave($request->facid, $appform->appid, $request->uid);
        }

        $payment = session()->get('payment');
        $appcharge =  session()->get('appcharge');
        $ambcharge   =  session()->get('ambcharge');

        $chg = DB::table('chgfil')->where([['appform_id', $appform->appid]])->first();
        if (!is_null($chg)) {
            DB::table('chgfil')->where([['appform_id', $appform->appid]])->delete();
        }

        if($request->appcharge != ""){
        NewGeneralController::appCharge($request->appcharge, $appform->appid, $request->uid);}

        if($request->appchargeHgp != ""){
        NewGeneralController::appCharge($request->appchargeHgp, $appform->appid, $request->uid);}

        if($request->appChargeAmb != ""){
        NewGeneralController::appChargeAmb($request->appChargeAmb, $appform->appid, $request->uid);}

            return response()->json(
                [
                    'id' => $appform->appid,
                    'applicaiton' => $appform,
                    'payment' => $payment,
                    'appcharge' => $appcharge,
                    'ambcharge' => $ambcharge,
                    // 'con_catchment' => $concatch,
                    'provinces'     => Province::where('rgnid', $appform->rgnid)->get(),
                    'cities'        => Municipality::where('provid', $appform->provid)->get(),
                    'brgy'          => Barangay::where('cmid', $appform->cmid)->get(),
                    'classification' => Classification::where('ocid',  $appform->ocid)->where('isSub', null)->get(),
                    'subclass'      => Classification::where('ocid', $appform->ocid)->where('isSub',  $appform->classid)->get(),
                ],
                200
            );
       
      
       
        



    }
    public function assessmentReady(Request $request, $appid)
	{
		$curForm = FunctionsClientController::getUserDetailsByAppform($appid);
		if(count($curForm) < 1) {
			return redirect('client1/apply')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'No application selected.']);
		}
		try {
			$dohC = new DOHController();
			$toViewArr = $dohC->AssessmentShowPart($request,$appid,false,true);
			$toViewArr['appform'] = $curForm[0];
			return view('client1.assessment.assessmentView',$toViewArr);
		} catch (Exception $e) {
			return $e;
		}
	}

     function ltoAppDetSave($reqfacid, $appid, $uid)
    {

        $facs =  DB::table('x08_ft')->where('appid', $appid)->first();
     
        if (!is_null($facs)) {
            DB::table('x08_ft')->where('appid', $appid)->delete();
        }

        $facid = json_decode($reqfacid, true);

        for ($i = 0; $i < count($facid); $i++) {
            DB::insert('insert into x08_ft (uid, appid, facid) values (?, ?, ?)', [$uid, $appid, $facid[$i]]);
        }
    }
    public function contfromPtc(Request $request,  $appid)
    {

        $ptcapp = ApplicationForm::where('appid', $appid)->first();

        $appform = new ApplicationForm;

        $appform->hfser_id              = 'LTO';
        $appform->facilityname          = $ptcapp->facilityname;
        $appform->rgnid                 = $ptcapp->rgnid;
        $appform->provid                = $ptcapp->provid;
        $appform->cmid                  = $ptcapp->cmid;
        $appform->brgyid                = $ptcapp->brgyid;
        $appform->street_number         = $ptcapp->street_number;
        $appform->street_name           = $ptcapp->street_name;
        $appform->zipcode               = $ptcapp->zipcode;
        $appform->contact               = $ptcapp->contact;
        $appform->areacode              = $ptcapp->areacode;
        $appform->landline              = $ptcapp->landline;
        $appform->faxnumber             = $ptcapp->faxnumber;
        $appform->email                 = $ptcapp->email;
        $appform->cap_inv               = $ptcapp->cap_inv;
        $appform->lot_area              = $ptcapp->lot_area;
        $appform->noofbed               = $ptcapp->noofbed;
        $appform->uid                   = $ptcapp->uid;
        $appform->ocid                  = $ptcapp->ocid;
        $appform->classid               = $ptcapp->classid;
        $appform->subClassid            = $ptcapp->subClassid;
        $appform->facmode               = $ptcapp->facmode;
        $appform->funcid                = $ptcapp->funcid;
        $appform->owner                 = $ptcapp->owner;
        $appform->ownerMobile           = $ptcapp->ownerMobile;
        $appform->ownerLandline         = $ptcapp->ownerLandline;
        $appform->ownerEmail            = $ptcapp->ownerEmail;
        $appform->mailingAddress        = $ptcapp->mailingAddress;
        $appform->approvingauthoritypos = $ptcapp->approvingauthoritypos;
        $appform->approvingauthority    = $ptcapp->approvingauthority;
        $appform->hfep_funded           = $ptcapp->hfep_funded;
        $appform->assignedRgn           = $ptcapp->assignedRgn;
        $appform->ptcCode               = $ptcapp->ptcCode;
        $appform->noofmain              = $ptcapp->noofmain;
        $appform->noofdialysis          = $ptcapp->noofdialysis;
        $appform->noofsatellite         = $ptcapp->noofsatellite;
        $appform->aptid                 = $ptcapp->aptid;

        $appform->save();

        return redirect('client1/apply/app/LTO/'.$appform->appid.'');


    }
}


