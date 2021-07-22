<?php

namespace App\Http\Controllers\Client\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ApplicationForm;
use App\Models\Barangay;
use App\Models\Classification;
use App\Models\Municipality;
use App\Models\Province;
use Carbon\Carbon;
use DB;

class PtcAppController extends Controller
{
    public function save(Request $request)
    {

        // try {
        
        if (isset($request->appid)) {
            $appform = ApplicationForm::where('appid', $request->appid)->first();
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
        $appform->assignedRgn           = $request->assignedRgn;
        $appform->hgpid                 = $request->hgpid;//6-22-2021

        // LTO update 5-12-2021
        $appform->ptcCode               = $request->ptcCode;
        $appform->noofmain              = $request->noofmain;
        $appform->noofdialysis          = $request->noofdialysis;
        $appform->noofsatellite         = $request->noofsatellite;
        $appform->savingStat            = $request->saveas;
        $appform->aptid                 = $request->aptid;
        $appform->appComment                 = $request->remarks;
        $appform->con_number                 = $request->connumber;
        // $appform->savingStat            = $request->saveas;

        if($request->saveas == 'final'){
            $appform->draft = null;
        }

    
      

      
        $appform->save();
        
      
       

       

        $chg = DB::table('chgfil')->where([['appform_id', $appform->appid]])->first();
        if (!is_null($chg)) {
            DB::table('chgfil')->where([['appform_id', $appform->appid]])->delete();
        }

        if($request->appcharge != ""){
        // NewGeneralController::appCharge($request->appcharge, $appform->appid, $request->uid);
        $this->appCharge($request->appcharge, $appform->appid, $request->uid);
          }

        if($request->appchargeHgp != ""){
            $this->appCharge($request->appchargeHgp, $appform->appid, $request->uid);
        // NewGeneralController::appCharge($request->appchargeHgp, $appform->appid, $request->uid);
    }
        // $this->appCharge($request->appcharge, $appform->appid, $request->uid);

    
        $this->ltoAppDetSave($request->facid, $appform->appid, $request->uid);
        $this->ptcAppDet($request->ptcdet, $appform->appid);
      
        return response()->json(
            [
                'id' => $appform->appid,
                // 'applicaiton' => $appform,
                // 'provinces'     => Province::where('rgnid', $appform->rgnid)->get(),
                // 'cities'        => Municipality::where('provid', $appform->provid)->get(),
                // 'brgy'          => Barangay::where('cmid', $appform->cmid)->get(),
                // 'classification' => Classification::where('ocid',  $appform->ocid)->where('isSub', null)->get(),
                // 'subclass'      => Classification::where('ocid', $appform->ocid)->where('isSub',  $appform->classid)->get(),
            ],
            200
        );

        
    }


    public function contfromCon(Request $request,  $appid)
    {

        $conapp = ApplicationForm::where('appid', $appid)->first();

        $appform = new ApplicationForm;

        $appform->hfser_id              = 'PTC';
        $appform->facilityname          = $conapp->facilityname;
        $appform->rgnid                 = $conapp->rgnid;
        $appform->provid                = $conapp->provid;
        $appform->cmid                  = $conapp->cmid;
        $appform->brgyid                = $conapp->brgyid;
        $appform->street_number         = $conapp->street_number;
        $appform->street_name           = $conapp->street_name;
        $appform->zipcode               = $conapp->zipcode;
        $appform->contact               = $conapp->contact;
        $appform->areacode              = $conapp->areacode;
        $appform->landline              = $conapp->landline;
        $appform->faxnumber             = $conapp->faxnumber;
        $appform->email                 = $conapp->email;
        $appform->cap_inv               = $conapp->cap_inv;
        $appform->lot_area              = $conapp->lot_area;
        $appform->noofbed               = $conapp->noofbed;
        $appform->uid                   = $conapp->uid;
        $appform->ocid                  = $conapp->ocid;
        $appform->classid               = $conapp->classid;
        $appform->subClassid            = $conapp->subClassid;
        $appform->facmode               = $conapp->facmode;
        $appform->funcid                = $conapp->funcid;
        $appform->owner                 = $conapp->owner;
        $appform->ownerMobile           = $conapp->ownerMobile;
        $appform->ownerLandline         = $conapp->ownerLandline;
        $appform->ownerEmail            = $conapp->ownerEmail;
        $appform->mailingAddress        = $conapp->mailingAddress;
        $appform->approvingauthoritypos = $conapp->approvingauthoritypos;
        $appform->approvingauthority    = $conapp->approvingauthority;
        $appform->hfep_funded           = $conapp->hfep_funded;
        $appform->noofmain              = $conapp->noofmain;
        $appform->noofsatellite         = $conapp->noofsatellite;
        $appform->aptid                 = $conapp->aptid;
        $appform->con_number            = $conapp->hfser_id.'R'.$conapp->rgnid.'-'.$conapp->appid;
        $appform->savingStat            = "partial";
        



        $appform->save();

        DB::insert('insert into ptc (appid, propbedcap, type) values (?, ?, ?)',[$appform->appid, $conapp->noofbed, 0]);

        $chk =  DB::table('x08_ft')->where([['appid', $appid]])->first();

        DB::insert('insert into x08_ft (uid, appid, facid) values (?, ?, ?)', [$conapp->uid, $appform->appid, $chk->facid]);

        return redirect('client1/apply/app/PTC/'.$appform->appid.'?cont=yes');


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
                // DB::insert('insert into ptc (appid,  type, construction_description, propbedcap, renoOption,incbedcapfrom, incbedcapto, conCode,ltoCode, incstationfrom,
                //     incstationto
                //     ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                //      [
                //         $appid, 
                //         $d["type"], 
                //         $d["construction_description"], 
                //         $d["propbedcap"], 
                //         $d["renoOption"], 
                //         $d["incbedcapfrom"], 
                //         $d["incbedcapto"], 
                //         $d["connum"], 
                //         $d["ltonum"], 
                //         $d["incstationfrom"], 
                //         $d["incstationto"]
                        
                         
                //     ]);

                DB::insert('insert into ptc (appid,  type, construction_description, propbedcap, renoOption,incbedcapfrom, incbedcapto, conCode,ltoCode, incstationfrom, incstationto) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',[$appid, $d["type"], $d["construction_description"], $d["propbedcap"], $d["renoOption"], $d["incbedcapfrom"], $d["incbedcapto"], $d["connum"], $d["ltonum"], $d["incstationfrom"], $d["incstationto"]]);

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

     function appCharge($appcharge, $appid, $uid)
    {

        $arrVal = json_decode($appcharge, true);

        $arrSaveChgfil = ['chgapp_id', 'chg_num', 'appform_id', 'chgapp_id_pmt', 'orreference', 'deposit', 'other', 'au_id', 'au_date', 'reference', 'amount', 't_date', 't_time', 't_ipaddress', 'uid'];

        $tPayment = 0;
        foreach ($arrVal as $a) {
            // if ($a["amount"] > 0) {
                $chg_num = (DB::table('chg_app')->where('chgapp_id', $a["chgapp_id"])->first())->chg_num;
                $arrDataChgfil =
                    [
                        $a["chgapp_id"],
                        $chg_num,
                        $appid,
                        NULL,
                        NULL,
                        NULL,
                        NULL,
                        NULL,
                        NULL,
                        $a["reference"],
                        $a["amount"],
                        Carbon::now()->toDateString(),
                        Carbon::now()->toTimeString(),
                        request()->ip(),
                        $uid
                    ];

                if (DB::table('chgfil')->insert(array_combine($arrSaveChgfil, $arrDataChgfil))) {
                    $tPayment +=  $a["amount"];
                    DB::table('chg_app')->where([['chgapp_id', $a["chgapp_id"]]])->update(['chg_num' => ($chg_num + 1)]);
                }
            // }
        }

        $chkGet = DB::table('appform_orderofpayment')->where([['appid', $appid]])->first();
        if(isset($chkGet)) {
            DB::table('appform_orderofpayment')->where([['appop_id', $chkGet->appop_id]])->update(['oop_total' => ($chkGet->oop_total + $tPayment)]);
        } else {
            DB::table('appform_orderofpayment')->insert(['appid' => $appid, 'oop_total' => $tPayment, 'oop_time' => Carbon::now()->toTimeString(), 'oop_date' => Carbon::now()->toDateString(), 'oop_ip' => request()->ip(), 'uid' => $uid]);
        }
    }

    
}
