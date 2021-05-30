<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;


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
  
}
