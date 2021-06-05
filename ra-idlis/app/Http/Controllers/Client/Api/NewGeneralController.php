<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FunctionsClientController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Str;

class NewGeneralController extends Controller
{
    public static function appCharge($appcharge, $appid, $uid)
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

    public static function appChargeAmb($appcharge, $appid, $uid)
    {

        $arrVal = json_decode($appcharge, true);

        $arrSaveChgfil = ['chgapp_id', 'chg_num', 'appform_id', 'chgapp_id_pmt', 'orreference', 'deposit', 'other', 'au_id', 'au_date', 'reference', 'amount', 't_date', 't_time', 't_ipaddress', 'uid'];

        $tPayment = 0;
        foreach ($arrVal as $a) {
            // if ($a["amount"] > 0) {
                // $chg_num = (DB::table('chg_app')->where('chgapp_id', $a["chgapp_id"])->first())->chg_num;
                $arrDataChgfil =
                    [
                        NULL,
                        NULL,
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

    public function uploadProofofPay(Request $request) {

        $msg = 0;

        $app =  DB::table('appform')->where('appid',$request->appid)->first();

        if($request->upproof){
            $data = $request->input('upproof');
            $fname = $request->file('upproof')->getClientOriginalName();
            $fileExtension = $request->file('upproof')->getClientOriginalExtension();
            $fileNameToStore = (session()->has('employee_login') ? FunctionsClientController::getSessionParamObj("employee_login", "uid") : FunctionsClientController::getSessionParamObj("uData", "uid")).'_'.Str::random(10).'_'.date('Y_m_d_i_s').'.'.$fileExtension;
            $request->file('upproof')->storeAs('public/uploaded', $fileNameToStore);


          
            $val =  DB::table('appform')->where('appid',$request->appid)->update(['payProofFilen' => $fileNameToStore,'isPayProofFilen' => 1 ]);
        
            if($val){
                $msg += 1;
            }
        }

        if($request->upmach){
            $data = $request->input('upmach');
            $fname = $request->file('upmach')->getClientOriginalName();
            $fileExtension = $request->file('upmach')->getClientOriginalExtension();
            $fileNameToStore = (session()->has('employee_login') ? FunctionsClientController::getSessionParamObj("employee_login", "uid") : FunctionsClientController::getSessionParamObj("uData", "uid")).'_'.Str::random(10).'_'.date('Y_m_d_i_s').'.'.$fileExtension;
            $request->file('upmach')->storeAs('public/uploaded', $fileNameToStore);


            
            $valmch =  DB::table('appform')->where('appid',$request->appid)->update(['payProofFilenMach' => $fileNameToStore,'ispayProofFilenMach' => 1 ]);
        
            if($valmch){
                $msg += 1;
            }

            if(is_null($app->proofpaystatMach)){
                DB::table('appform')->where('appid',$request->appid)->update(['proofpaystatMach' => 'posting']);
            }
        }

        if($request->upphar){
            $data = $request->input('upphar');
            $fname = $request->file('upphar')->getClientOriginalName();
            $fileExtension = $request->file('upphar')->getClientOriginalExtension();
            $fileNameToStore = (session()->has('employee_login') ? FunctionsClientController::getSessionParamObj("employee_login", "uid") : FunctionsClientController::getSessionParamObj("uData", "uid")).'_'.Str::random(10).'_'.date('Y_m_d_i_s').'.'.$fileExtension;
            $request->file('upphar')->storeAs('public/uploaded', $fileNameToStore);


            
            $valmch =  DB::table('appform')->where('appid',$request->appid)->update(['payProofFilenPhar' => $fileNameToStore,'ispayProofFilenPhar' => 1 ]);
        
            if($valmch){
                $msg += 1;
            }

            if(is_null($app->proofpaystatMach)){
                DB::table('appform')->where('appid',$request->appid)->update(['proofpaystatPhar' => 'posting']);
            }
        }

        if(is_null($app->proofpaystat)){
            DB::table('appform')->where('appid',$request->appid)->update(['proofpaystat' => 'posting']);
        }
       

        return  response()->json([
            'msg' => 'success' ,
            'id' => $request->appid
          
        ]);
    }
  

    
}
