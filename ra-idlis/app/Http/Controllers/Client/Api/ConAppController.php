<?php

namespace App\Http\Controllers\Client\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ApplicationForm;
use App\Models\Barangay;
use App\Models\Classification;
use App\Models\CONCatchment;
use App\Models\CONHospital;
use App\Models\Municipality;
use App\Models\Province;
use DB;

class ConAppController extends Controller
{
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
        $appform->savingStat            = $request->saveas;
        // $appform->savingStat            = $request->saveas;

       
      

        $appform->save();
        
        $facid = json_decode($request->facid, true);
        $this->ltoAppDetSave($request->facid, $appform->appid, $request->uid);

        // if(count($facid) > 0){
        //    $this->ltoAppDetSave($request->facid, $appform->appid, $request->uid);
        // }

        $con_catch = [];
        $con_hospital = [];

        foreach ($request->con_catch  as $cc) {
            // dd($cc['type']);
            $arr = [
                'appid'         => $appform->appid,
                'type'          => $cc['type'],
                'location'      => $cc['location'],
                'population'    => $cc['population'],
                'isfrombackend' => null
            ];
            array_push($con_catch, $arr);
        }
        foreach ($request->con_hospital  as $ch) {
            // dd($cc['type']);
            $arr = [
                'appid'         => $appform->appid,
                'facilityname'  => $ch['facilityname'],
                'location1'     => $ch['location1'],
                'cat_hos'       => $ch['cat_hos'],
                'noofbed1'      => $ch['noofbed1'],
                'license'       => $ch['license'],
                'validity'      => $ch['validity'],
                'date_operation' => $ch['date_operation'],
                'remarks'       => $ch['remarks']
            ];
            array_push($con_hospital, $arr);
        }
        $conhospital = CONHospital::where('appid', $request->appid)->delete();
        $concatch = CONCatchment::where('appid', $request->appid)->delete();
        CONHospital::insert($con_hospital);
        CONCatchment::insert($con_catch);

      
        return response()->json(
            [
                'id' => $appform->appid,
                'applicaiton' => $appform,
                'provinces'     => Province::where('rgnid', $appform->rgnid)->get(),
                'cities'        => Municipality::where('provid', $appform->provid)->get(),
                'brgy'          => Barangay::where('cmid', $appform->cmid)->get(),
                'classification' => Classification::where('ocid',  $appform->ocid)->where('isSub', null)->get(),
                'subclass'      => Classification::where('ocid', $appform->ocid)->where('isSub',  $appform->classid)->get(),
            ],
            200
        );
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
}
