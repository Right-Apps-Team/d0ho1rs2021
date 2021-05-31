<?php

namespace App\Http\Controllers\Client\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ApplicationForm;
use App\Models\Barangay;
use App\Models\Classification;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\PTC;
use DB;

class PtcAppController extends Controller
{
    public function save(Request $request)
    {

        // try {
        
        if (isset($request->appid)) {
            $appform = ApplicationForm::where('appid', $request->appid)->first();
          

            // DB::insert('insert into x08_ft (uid, appid, facid) values (?, ?, ?)', ['fds', 'ff', 'fds']);
        } else {
            $appform = new ApplicationForm;
            // $ptcform = new PTC;

            // $ptcform->appid  = $appform->appid;
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
        $appform->noofdialysis          = $request->noofdialysis;
        $appform->noofsatellite         = $request->noofsatellite;
        $appform->savingStat            = $request->saveas;
        // $appform->savingStat            = $request->saveas;

        if($request->saveas == 'final'){
            $appform->draft = null;
        }

        // $ptcform->type                      = $request->type;
        // $ptcform->construction_description  = $request->construction_description;
        // $ptcform->propbedcap                = $request->propbedcap;
        // $ptcform->renoOption                = $request->renoOption;
        // $ptcform->incbedcapfrom             = $request->incbedcapfrom;
        // $ptcform->incbedcapto               = $request->incbedcapto;
        // $ptcform->ltonum                    = $request->ltonum;
        // $ptcform->coanum                    = $request->coanum;
        // $ptcform->noofdialysis              = $request->noofdialysis;
        // $ptcform->incstationfrom            = $request->incstationfrom;
        // $ptcform->incstationto              = $request->incstationto;
      

        // $ptcform->save();
        $appform->save();
        
        $facid = json_decode($request->facid, true);
        $this->ltoAppDetSave($request->facid, $appform->appid, $request->uid);
        $this->ptcAppDet($request->ptcdet, $appform->appid);

        // if(count($facid) > 0){
        //    $this->ltoAppDetSave($request->facid, $appform->appid, $request->uid);
        // }

        $chg = DB::table('chgfil')->where([['appform_id', $appform->appid]])->first();
        if (!is_null($chg)) {
            DB::table('chgfil')->where([['appform_id', $appform->appid]])->delete();
        }

        NewGeneralController::appCharge($request->appcharge, $appform->appid, $request->uid);
        NewGeneralController::appCharge($request->appchargeHgp, $appform->appid, $request->uid);
        // $this->appCharge($request->appcharge, $appform->appid, $request->uid);

    

      
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

        // } catch (Exception $e) {
        //     return response()->json(
        //         [
        //             'error' => $e,
        //              ],
        //         200
        //     );
        // }
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

    function ptcAppDet($ptcDet, $appid){

        $dets = json_decode($ptcDet, true);

        $ptc =  DB::table('ptc')->where('appid', $appid)->first();

        foreach($dets as $d){
            if (is_null($ptc)) {
                DB::insert('insert into ptc (appid,  type, construction_description, propbedcap, renoOption,incbedcapfrom, incbedcapto, conCode,ltoCode, incstationfrom,
                    incstationto
                    ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                     [
                        $appid, 
                        $d["type"], 
                        $d["construction_description"], 
                        $d["propbedcap"], 
                        $d["renoOption"], 
                        $d["incbedcapfrom"], 
                        $d["incbedcapto"], 
                        $d["connum"], 
                        $d["ltonum"], 
                        $d["incstationfrom"], 
                        $d["incstationto"]
                        
                         
                    ]);

            }else{
                DB::table('ptc')
                ->where('appid', $appid)
                ->update([
                    'type' => $d["type"],
                    'construction_description' => $d["construction_description"],
                    'propbedcap' => $d["propbedcap"],
                    'renoOption' => $d["renoOption"],
                    'incbedcapfrom' => $d["incbedcapfrom"],
                    'incbedcapto' => $d["incbedcapto"],
                    'conCode' => $d["connum"],
                    'ltoCode' => $d["ltonum"],
                    'incstationfrom' => $d["incstationfrom"],
                    'incstationto' => $d["incstationto"]
                ]);

            }
        }
    }

    
}
