<?php 
namespace App\Http\Controllers;
use Mail;
use Session;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use AjaxController;
class FunctionsClientController extends Controller {
	protected static $curUser; protected static $curToken = "o76u6HJnfaJtzVms1hEK9WStbUWtHWxhZKtlySsO";
	protected static $moneyString = [
		"first" => [
			"0" => "", "1" => "one", "2" => "two", "3" => "three", "4" => "four", "5" => "five", "6" => "six", "7" => "seven", "8" => "eight", "9" => "nine"
		],
		"second" => [
			"10" => "ten", "11" => "eleven", "12" => "twelve", "13" => "thirteen", "14" => "fourteen", "15" => "fifteen", "16" => "sixteen", "17" => "seventeen", "18" => "eighteen", "19" => "nineteen", "20" => "twenty", "30" => "thirty", "40" => "fourty", "50" => "fifty", "60" => "sixty", "70" => "seventy", "80" => "eighty", "90" => "ninety"
		],
		"every" => [
			3 => "hundred"
		],
		"count" => [
			"", "thousand", "million", "billion", "trillion", "quadrillion", "quintillion", "sextillion", "septillion", "octillion", "nonillion", "decillion", "undecillion", "duodecillion", "tredecillion", "quatttuor-decillion", "quindecillion", "sexdecillion", "septen-decillion", "octodecillion", "novemdecillion", "vigintillion", "centillion"
		]
	];
	public static function setToken() { static::$curToken = "o76u6HJnfaJtzVms1hEK9WStbUWtHWxhZKtlySsO"; }
	public static function getToken() { return static::$curToken; }
	public static function setUser() { static::$curUser = session()->get('uData'); }
	public static function getUser() { return static::$curUser; }
	public static function moneyToString($money = "") {
		try {
			$sMoney = intval($money); $mString = static::$moneyString; $retData = "";
			if(! empty($sMoney)) {
				$nMoney = strrev($sMoney); $spMoney = str_split($nMoney, 3);
				for($i = 0; $i < count($spMoney); $i++) {
					$thStr = strrev($spMoney[$i]); $mLen = strlen($thStr); $curStr = "";
					switch($mLen) {
						case 1:
							$curStr = $curStr . ' ' . $mString["first"][strval(substr($thStr, 0, 1))];
							break;
						case 2:
							if(isset($mString["second"][strval(substr($thStr, 0, 2))])) {
								$curStr = $curStr . ' ' . $mString["second"][strval(substr($thStr, 0, 2))];
							} else {
								$curStr = $curStr . ' ' . $mString["second"][strval(intval(substr($thStr, 0, 1)) * 10)] . ' ' . $mString["first"][strval(substr($thStr, 1, 1))];
							}
							break;
						case 3:
							if(isset($mString["every"][$mLen])) {
								$sStr = $mString["first"][strval(substr($thStr, 0, 1))];
								if(! empty($sStr)) {
									$curStr = $curStr . ' ' . $sStr . ' ' . $mString["every"][$mLen];
								}
							}
							if(isset($mString["second"][strval(substr($thStr, 1, 2))])) {
								$curStr = $curStr . ' ' . $mString["second"][strval(substr($thStr, 1, 2))];
							} else {
								$curStr = $curStr . ' ' . ((isset($mString["second"][strval(intval(substr($thStr, 1, 1)) * 10)])) ? $mString["second"][strval(intval(substr($thStr, 1, 1)) * 10)] : ' ') . ' ' . ((isset($mString["first"][strval(substr($thStr, 2, 1))])) ? $mString["first"][strval(substr($thStr, 2, 1))] : ' ');
							}
							break;
						default:
							break;
					}
					$retData = $curStr . ' ' . $mString["count"][$i] . $retData;
				}
			}
			return trim($retData);
		} catch (Exception $e) {
			return $e;
		}
	}
	public static function sMailVer($location, $sData, $request) {
		self::sMailSendRetBool($location, $sData, $request, 'Verify Email Account', 'doholrs@gmail.com', 'DOH Support');
	}
	public static function sMailVerRetBool($location, $sData, $request) {
		return self::sMailSendRetBool($location, $sData, $request, 'Verify Email Account', 'doholrs@gmail.com', 'DOH Support');
	}
	public static function sMailSendRetBool($location, $sData, $request, $sSubject, $sFrom, $sName) {
		try {
			Mail::send($location, $sData, function($message) use ($request, $sSubject, $sFrom, $sName) {
	           	$message->to($request->text6, $request->text2)->subject($sSubject);
	           	$message->from($sFrom, $sName);
	        });
	        return true;
		} catch (Exception $e) {
			// return $e;
			return false;
		}
	}
	public static function __sendMesssage($errMsg, $errClr, $retUri) {
		dd(redirect($retUri)->with($errClr, $errMsg));
	}
	public static function getUserDetails($curUid = "") {
		try {
			$curUser = (!empty($curUid)) ? $curUid : self::getSessionParamObj("uData", "uid");
			if(isset($curUser)) {
				$sql = "SELECT x08.rgnid, x08.assign, x08.nameofcompany, x08.uid, x08.province, x08.city_muni, x08.barangay, x08.email, x08.zipcode, x08.contact, x08.houseno, x08.streetname, x08.authorizedsignature, region.rgn_desc, province.provname, city_muni.cmname, barangay.brgyname FROM x08 LEFT JOIN region ON region.rgnid = x08.rgnid LEFT JOIN province ON province.provid = x08.province LEFT JOIN city_muni ON city_muni.cmid = x08.city_muni LEFT JOIN barangay ON barangay.brgyid = x08.barangay WHERE x08.uid = '$curUser'";
				return DB::select($sql);
			}
			return [];
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function getUserDetailsByAppform($appid = "", $curUid = "",$choice = 'default') {
		try {
			$retArr = [];
			// if(! empty($appid)) {
				$curUser = (! empty($curUid)) ? $curUid : (session()->has('uData') ? self::getSessionParamObj("uData", "uid") : DB::table('appform')->where('appid',$appid)->select('uid')->first()->uid);
				switch ($choice) {
					case 1:
						$sql = "SELECT 
							appid,
							uid, 
							facilityname, 
							serv_capabilities, 
							owner, 
							email, 
							contact, 
							appform.hfser_id, 
							hfaci_serv_type.hfser_desc, 
							appform.facid, 
							appform.ocid, 
							appform.ocdesc as appformocdesc, 
							appform.aptid, 
							appform.ptcCode, 
							appform.classid, 
							classdesc, 
							subClassid, 
							subClassdesc, 
							appform.funcid, 
							appform.facmode, 
							noofbed, 
							draft, 
							appid_payment, 
							t_date, 
							t_time, 
							region.rgnid, 
							region.rgn_desc, 
							region.office,
							region.address,
							region.iso_desc,
							province.provid, 
							province.provname, 
							city_muni.cmid, 
							city_muni.cmname, 
							barangay.brgyid, 
							barangay.brgyname, 
							status, 
							trans_status.trns_desc, 
							trans_status.allowedpayment, 
							trans_status.canapply, 
							facmode.facmdesc, 
							funcapf.funcdesc, 
							ownership.ocdesc, 
							class.classname, 
							apptype.aptdesc, 
							appform.rgnid, 
							noofsatellite, 
							clab, 
							cap_inv, 
							lot_area, 
							typeamb, 
							noofamb, 
							plate_number, 
							typeamb, 
							ambOwner, 
							street_name, 
							zipcode, 
							landline, 
							validDate, 
							documentSent, 
							isApproveFDA, 
							isNotified, 
							isPayEval, 
							isCashierApprove, 
							isrecommended, 
							isReadyForInspec, 
							street_number, 
							isReadyForInspecFDA, 
							isrecommendedFDA, 
							FDAstatus, 
							pharCOC, 
							xrayCOC, 
							landline, 
							faxNumber, 
							ownerMobile, 
							ownerLandline, 
							ownerEmail, 
							mailingAddress, 
							faxnumber, 
							validDateFrom, 
							licenseNo, 
							HFERC_swork, 
							payEvalbyFDA, /*HFERC_comments,*/ 
							assRgn.rgn_desc AS assRgnDesc, 
							ishfep, 
							noofmain, 
							ambtyp, 
							areacode, 
							isAcceptedFP, 
							fpcomment, 
							others_oanc, 
							hfep_funded, 
							proposedWeek, 
							approvingauthority, 
							approvingauthoritypos ,
							addonDesc,
							savingStat,
							noofdialysis,
							con_number
							
						FROM appform 
						LEFT JOIN region ON region.rgnid = appform.rgnid 
						LEFT JOIN class ON class.classid = appform.classid 
						LEFT JOIN province ON province.provid = appform.provid 
						LEFT JOIN ownership ON ownership.ocid = appform.ocid 
						LEFT JOIN funcapf ON funcapf.funcid = appform.funcid 
						LEFT JOIN apptype ON apptype.aptid = appform.aptid 
						LEFT JOIN city_muni ON city_muni.cmid = appform.cmid 
						LEFT JOIN barangay ON barangay.brgyid = appform.brgyid 
						LEFT JOIN hfaci_serv_type ON hfaci_serv_type.hfser_id = appform.hfser_id 
						LEFT JOIN trans_status ON trans_status.trns_id = appform.status 
						LEFT JOIN facmode ON facmode.facmid = appform.facmode 
						LEFT JOIN region AS assRgn ON assRgn.rgnid = appform.assignedRgn 
					WHERE 
						appform.uid = '$curUser' AND appform.draft = 1"; // AND appform.uid = '$curUser' // LEFT JOIN (SELECT facname, servtype_id FROM facilitytyp WHERE facid IN (SELECT facid FROM x08_ft WHERE appid = '$appid')) facilitytyp ON 1=1 LEFT JOIN (SELECT GROUP_CONCAT(hgpdesc) AS hgpdesc FROM hfaci_grp WHERE hgpid IN (SELECT hgpid FROM facilitytyp WHERE facid IN (SELECT facid FROM x08_ft WHERE appid = '$appid'))) hfaci_grp ON 1=1 LEFT JOIN serv_type ON serv_type.servtype_id = facilitytyp.servtype_id
						break;
					
					default:
						$sql = "SELECT 
							autoTimeDate, 
							appid, 
							uid, 
							facilityname, 
							serv_capabilities, 
							owner, 
							email, 
							contact, 
							appform.hfser_id, 
							hfaci_serv_type.hfser_desc, 
							appform.facid, 
							appform.ocid, 
							appform.ocdesc as appformocdesc, 
							appform.aptid, 
							appform.ptcCode, 
							appform.classid, 
							classdesc, 
							subClassid, 
							subClassdesc, 
							appform.funcid, 
							appform.facmode, 
							noofbed, 
							draft, 
							appid_payment, 
							t_date, 
							t_time, 
							region.rgnid, 
							region.rgn_desc, 
							region.office,
							region.address,
							region.iso_desc,
							province.provid, 
							province.provname, 
							city_muni.cmid, 
							city_muni.cmname, 
							barangay.brgyid, 
							barangay.brgyname, 
							status, 
							trans_status.trns_desc, 
							trans_status.allowedpayment, 
							trans_status.canapply, 
							facmode.facmdesc, 
							funcapf.funcdesc, 
							ownership.ocdesc, 
							class.classname, 
							apptype.aptdesc, 
							appform.rgnid, 
							noofsatellite, 
							clab, cap_inv, 
							lot_area, 
							typeamb, 
							noofamb, 
							plate_number, 
							typeamb, 
							ambOwner, 
							street_name, 
							zipcode, 
							landline, 
							validDate, 
							documentSent, 
							isApproveFDA, 
							isNotified, 
							isPayEval, 
							isCashierApprove, 
							isrecommended, 
							isReadyForInspec, 
							street_number, 
							isReadyForInspecFDA, 
							isrecommendedFDA, 
							FDAstatus, 
							pharCOC, 
							xrayCOC, 
							landline, 
							faxNumber, 
							ownerMobile, 
							ownerLandline, 
							ownerEmail, 
							mailingAddress, 
							faxnumber, 
							validDateFrom, 
							licenseNo, 
							HFERC_swork, 
							payEvalbyFDA, /*HFERC_comments,*/ 
							assRgn.rgn_desc AS assRgnDesc, 
							ishfep, 
							noofmain, 
							ambtyp, 
							areacode, 
							isAcceptedFP, 
							fpcomment, 
							others_oanc, 
							hfep_funded, 
							proposedWeek, 
							approvingauthority, 
							approvingauthoritypos ,
							addonDesc,
							savingStat,
							noofdialysis,
							con_number
						FROM appform 
						LEFT JOIN region ON region.rgnid = appform.rgnid 
						LEFT JOIN class ON class.classid = appform.classid 
						LEFT JOIN province ON province.provid = appform.provid 
						LEFT JOIN ownership ON ownership.ocid = appform.ocid 
						LEFT JOIN funcapf ON funcapf.funcid = appform.funcid 
						LEFT JOIN apptype ON apptype.aptid = appform.aptid 
						LEFT JOIN city_muni ON city_muni.cmid = appform.cmid 
						LEFT JOIN barangay ON barangay.brgyid = appform.brgyid 
						LEFT JOIN hfaci_serv_type ON hfaci_serv_type.hfser_id = appform.hfser_id 
						LEFT JOIN trans_status ON trans_status.trns_id = appform.status 
						LEFT JOIN facmode ON facmode.facmid = appform.facmode 
						LEFT JOIN region AS assRgn ON assRgn.rgnid = appform.assignedRgn 
					WHERE appform.appid = '$appid' AND appform.uid = '$curUser'"; // AND appform.uid = '$curUser' // LEFT JOIN (SELECT facname, servtype_id FROM facilitytyp WHERE facid IN (SELECT facid FROM x08_ft WHERE appid = '$appid')) facilitytyp ON 1=1 LEFT JOIN (SELECT GROUP_CONCAT(hgpdesc) AS hgpdesc FROM hfaci_grp WHERE hgpid IN (SELECT hgpid FROM facilitytyp WHERE facid IN (SELECT facid FROM x08_ft WHERE appid = '$appid'))) hfaci_grp ON 1=1 LEFT JOIN serv_type ON serv_type.servtype_id = facilitytyp.servtype_id
						break;
				}
				
				$retArr = DB::select($sql);
			// }
			return $retArr;
		} catch(Exception $e) {
			dd($e);
			return $e;
		}
	}

	public static function getAllAssessment($where = []) {
		return DB::table('x08_ft')
	        ->leftJoin('appform', 'appform.appid', '=', 'x08_ft.appid')
	        ->leftJoin('hfaci_serv_type', 'appform.hfser_id', '=', 'hfaci_serv_type.hfser_id')
	        ->leftJoin('facilitytyp', 'x08_ft.facid', '=', 'facilitytyp.facid')
	        ->leftJoin('hfaci_grp', 'facilitytyp.hgpid', '=', 'hfaci_grp.hgpid')
	        ->leftJoin('serv_asmt', 'x08_ft.facid', '=', 'serv_asmt.facid')
			->leftJoin('asmt_title', 'serv_asmt.part', '=', 'asmt_title.title_code')
			->leftJoin('asmt2', 'serv_asmt.asmt2_id', '=', 'asmt2.asmt2_id')
			->leftJoin('asmt2_loc', 'asmt2.asmt2_loc', '=', 'asmt2_loc.asmt2l_id')
	        ->leftJoin('asmt2_sdsc', 'asmt2.asmt2sd_id', '=', 'asmt2_sdsc.asmt2sd_id')
	        ->select(
				'appform.appid',
				'appform.uid',
				'appform.hfser_id as appformhfser_id',
				'appform.aptid',
				'appform.facilityname',
				'serv_asmt.*',
				'asmt2_loc.*',
				'facilitytyp.facname',
				'hfaci_serv_type.hfser_desc',
				'hfaci_serv_type.terms_condi',
				'asmt2.*',
				'asmt2_sdsc.asmt2sd_desc',
				'serv_asmt.srvasmt_seq',
				'asmt2_loc.asmt2l_sdesc',
				'asmt_title.filename',
				'serv_asmt.facid as hospitalType',
				'asmt_title.title_name',
				'asmt_title.title_code as headCode',
				'asmt2_loc.header_lvl1 as headers'
			)
	        ->orderBy('asmt_title.title_name','ASC')->orderBy('serv_asmt.srvasmt_seq','ASC')
	        ->where($where)
	        ->distinct();
	}
	public static function fGetAppformIdLatest($curUid = "") {
		try {
			$retArr = [];
			$curUser = (!empty($curUid)) ? $curUid : self::getSessionParamObj("uData", "uid");
			$sql = "SELECT appid, hfser_id FROM appform WHERE appform.uid = '$curUser' ORDER BY appid DESC LIMIT 1";
			$retArr = DB::select($sql);
			return $retArr; //array_chunk(DB::select($sql), 7);
		} catch(Exception $e) {
			return $e;
		}
	}

	public static function getTrans($trans){
		if(isset($trans)){
			return DB::table('trans_status')->where('trns_id',$trans)->select('trns_desc')->first()->trns_desc;
		} else {
			return 'Not Avaialable';
		}
	}

	public static function getApplicationDetailsWithTransactions($curUid = "", $forLicensed = "NOT IN", $forRenewal = false) {
		try {
			$retArr = []; $_where = "";
			$curUser = (!empty($curUid)) ? $curUid : self::getSessionParamObj("uData", "uid");
			if($forRenewal) {
				// $_where = "AND appform.appid NOT IN (SELECT appid FROM appform_r WHERE isrenewed = 1)";
			}
			// $sql = "SELECT appid, proofpaystat, machinestat.trns_desc AS FDAStatMach_desc, pharmastat.trns_desc AS FDAStatPhar_desc , FDAstatus, uid, licenseNo, approvedDate, facilityname, hfser_desc, hfaci_serv_type.hfser_id, owner, DATE_FORMAT(t_date, '%b %d, %Y') AS t_date, trans_status.trns_desc, trans_status.color as dohcolor, FDA.color as fdacolor, FDA.trns_desc as FDAstat, trans_status.allowedpayment, trans_status.canapply, trans_status.isapproved, rgnid, DATE_FORMAT(validDate, '%b %d, %Y') AS validDate, DATE_FORMAT(documentSent, '%b %d, %Y') AS documentSent, aptid, isNotified, noofsatellite, isPayEval, noofsatellite, pharCOC, xrayCOC, pharValidity, xrayVal, noofmain FROM appform LEFT JOIN hfaci_serv_type ON appform.hfser_id = hfaci_serv_type.hfser_id 
			// LEFT JOIN trans_status ON appform.status = trans_status.trns_id 
			// LEFT JOIN trans_status machinestat ON appform.FDAStatMach = trans_status.trns_id 
			// LEFT JOIN trans_status pharmastat ON appform.FDAStatPhar = trans_status.trns_id 
			
			// LEFT JOIN trans_status as FDA ON appform.FDAstatus = FDA.trns_id WHERE appform.uid = '$curUser' AND appform.appid $forLicensed (SELECT DISTINCT appid FROM `licensed`) $_where ORDER BY t_date DESC"; $appSql = DB::select($sql);
		
		
		$sql = "SELECT isRecommended, appid, proofpaystat, FDAStatMach, FDAStatPhar , FDAstatus, uid, licenseNo, approvedDate, facilityname, hfser_desc, hfaci_serv_type.hfser_id, owner, DATE_FORMAT(t_date, '%b %d, %Y') AS t_date, trans_status.trns_desc, trans_status.color as dohcolor, FDA.color as fdacolor, FDA.trns_desc as FDAstat, trans_status.allowedpayment, trans_status.canapply, trans_status.isapproved, rgnid, DATE_FORMAT(validDate, '%b %d, %Y') AS validDate, DATE_FORMAT(documentSent, '%b %d, %Y') AS documentSent, aptid, isNotified, noofsatellite, isPayEval, noofsatellite, pharCOC, xrayCOC, pharValidity, xrayVal, noofmain FROM appform LEFT JOIN hfaci_serv_type ON appform.hfser_id = hfaci_serv_type.hfser_id LEFT JOIN trans_status ON appform.status = trans_status.trns_id LEFT JOIN trans_status as FDA ON appform.FDAstatus = FDA.trns_id WHERE appform.uid = '$curUser' AND appform.appid $forLicensed (SELECT DISTINCT appid FROM `licensed`) $_where ORDER BY t_date DESC"; $appSql = DB::select($sql);
			foreach($appSql AS $each) {
				if(! isset($each->isNotified)) { if(isset($each->validDate)) {
					self::setEmailExpired($each->appid);
				} }
				$nCompl = self::getAssessmentTotalPercentage($each->appid, ''.$each->uid.'_'.$each->hfser_id.'_'.$each->aptid.'_'.$each->appid.'');
				array_push($retArr, [$each, self::getChgfilTransactions($each->appid), [number_format($nCompl[0], 2), number_format($nCompl[1], 2), $nCompl[0]]]);
			}
			return $retArr; //array_chunk(DB::select($sql), 7);
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function getApplicationDetailsFEmployee($appid = "") {
		$retArr = [];
		if(! empty($appid)) {
			$sql = "SELECT appid, uid, facilityname, serv_capabilities, owner, email, contact, appform.hfser_id, hfaci_serv_type.hfser_desc, facid, ocid, ocdesc, aptid, classid, classdesc, subClassid, subClassdesc, funcid, appform.facmode, noofbed, draft, appid_payment, t_date, t_time, region.rgnid, region.rgn_desc, province.provid, province.provname, city_muni.cmid, city_muni.cmname, barangay.brgyid, barangay.brgyname, status, trans_status.trns_desc, trans_status.allowedpayment, trans_status.canapply, facmode.facmdesc, noofsatellite, clab, cap_inv, lot_area, typeamb, noofamb, street_name, zipcode, landline, validDate, documentSent, isApproveFDA, isNotified, ambtyp, areacode, others_oanc, hfep_funded FROM appform LEFT JOIN region ON region.rgnid = appform.rgnid LEFT JOIN province ON province.provid = appform.provid LEFT JOIN city_muni ON city_muni.cmid = appform.cmid LEFT JOIN barangay ON barangay.brgyid = appform.brgyid LEFT JOIN hfaci_serv_type ON hfaci_serv_type.hfser_id = appform.hfser_id LEFT JOIN trans_status ON trans_status.trns_id = appform.status LEFT JOIN facmode ON facmode.facmid = appform.facmode WHERE appform.appid = '$appid'"; // AND appform.uid = '$curUser' LEFT JOIN (SELECT GROUP_CONCAT(facname) AS facname FROM facilitytyp WHERE facid IN (SELECT facid FROM x08_ft WHERE appid = '$appid')) facilitytyp ON 1=1 LEFT JOIN (SELECT GROUP_CONCAT(hgpdesc) AS hgpdesc FROM hfaci_grp WHERE hgpid IN (SELECT hgpid FROM facilitytyp WHERE facid IN (SELECT facid FROM x08_ft WHERE appid = '$appid'))) hfaci_grp ON 1=1
			$retArr = DB::select($sql);
		}
		return $retArr;
	}
	public static function getAssessmentTotalPercentage($appid = "", $custAppid = "") {
		$retArr = []; $whatsNotThere = ['CLM000'];
		if(! empty($appid) && ! empty($custAppid)) {
			$joinedData = FunctionsClientController::getAllAssessment([['appform.appid', $appid]])->get(); $arrDisp = DB::table('app_assessment')->where([['appid', $custAppid]])->first();
			$newJoinedData = []; $newcount = []; $newasdf = []; $insNum = []; $chkNum = []; $newComp = 0; $newFalse = 0; $newTotal = 0; $newasdfasdf = [];
			if(isset($arrDisp)) {
				$asdf = json_decode($arrDisp->selfAssessment); $newasdfasdf = $asdf;
				if(isset($asdf)) { foreach($asdf AS $each) {
					foreach($each AS $key => $value) {
						if($key != "filename") {
							$aKey = explode("/", $key); $newKey = ((count($aKey) < 1) ? $aKey[0] : $aKey[0].'/'.$aKey[1]);
							if(!(strpos($aKey[1], "remarks") > -1)) {
								if(json_decode($value) == true) {
									array_push($newasdf, $key);
								}
								array_push($newcount, [$key, $key]);
							}
						}
					}
				} }
			}
			foreach($joinedData AS $eachJoinedData) {
				$nArrServ = json_decode($eachJoinedData->srvasmt_col);
				if(isset($nArrServ)) { foreach($nArrServ AS $nArrServEach) { if(! in_array($nArrServEach, $whatsNotThere)) {
					array_push($newJoinedData, $eachJoinedData->asmt2_id);
				} } } else { }
			}
			$newTotal = ((count($newJoinedData) > 0) ? count($newJoinedData) : 0);
			$newComp = ((count($newasdf) > 0) ? ((count($newasdf)/(($newTotal > 0) ? $newTotal : 1))*100) : 0);
			$newFalse = ((count($newcount) > 0) ? (((count($newcount) - count($newasdf))/(($newTotal > 0) ? $newTotal : 1))*100) : 0);
			$retArr = [$newComp, $newFalse, $newTotal];
		}
		return $retArr;
	}
	public static function getChgfilTransactions($appid = "", $cat_type = "") {
		try {
			$retArr = []; $checkArr = self::getUserDetailsByAppform($appid);
			if(! empty($appid)) {
				if(count($checkArr) > 0) {
					$category = ((! empty($cat_type)) ? "SELECT * FROM category WHERE cat_type = '$cat_type'" : "SELECT * FROM category");
					$sql = "SELECT chgfil.* FROM chgfil LEFT JOIN (SELECT chg_app.* FROM chg_app INNER JOIN charges ON chg_app.chg_code = charges.chg_code INNER JOIN ($category) category ON category.cat_id = charges.cat_id) chg_app ON chg_app.chgapp_id = chgfil.chgapp_id WHERE appform_id = '$appid' AND chgfil.amount IS NOT NULL ORDER BY amount DESC";
					$retArr = DB::select($sql);
				}
			}
			return $retArr; //array_chunk(DB::select($sql), 7);
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function getUserDetailsByAppformWithTransactions($appid = "") {
		try {
			$retArr = [];
			if(! empty($appid)) {
				$retArr = [self::getUserDetailsByAppform($appid), self::getChgfilTransactions($appid, 'C')];
			}
			return $retArr;
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function isExistOnAppform($appid = ""){
		if(!empty($appid)/* && session()->has('uData')*/){
			// $curUser = session()->get('uData');
			if(DB::table('appform')->where(/*[['uid',$curUser->uid],[*/'appid',$appid/*]]*/)->count() <= 0){
				return false;
			} 
				return true;
		} else {
			return "error";
		}
	}

	public static function hasRequirementsFor($checkFor, $appid){
		if(isset($checkFor) && isset($appid) && self::existOnDB('appform',[['appid',$appid]])){
			switch ($checkFor) {
				// pharmacy
				case 'cdrr':
					if(DB::table('appform')->where([['appid',$appid],['noofstation','<>',null]])->orWhere([['appid',$appid],['noofmain','<>',null]])->exists()){
						return true;
					}
					break;
				case 'cdrrhr':
				// machines
					if(self::existOnDB('cdrrhrxraylist',[['appid',$appid]])){
						return true;
					}
					break;
				
				default:
					return false;
					break;
			}
		}
		return false;
	}

	public static function getDetailsFDA($appid = ""){
		try {
			$total = $lrf = 0;
			$price  = null;
			$retArr = $prices = [];
			if(! empty($appid)) {
				$appform = self::getUserDetailsByAppform($appid)[0];
				$datasOfMachine = DB::table('fda_chgfil')
				->leftJoin('cdrrhrxraylist','fda_chgfil.xray_listID','cdrrhrxraylist.id')
				->leftJoin('fdarange','fda_chgfil.fchg_code','fdarange.id')
				->join('appform','fda_chgfil.appid','appform.appid')
				->where([['fda_chgfil.appid',$appid],['fda_chgfil.amount', '>', 1]])
				->whereNotNull('fda_chgfil.xray_listID')
				->select('appform.*','fda_chgfil.*')
				->get();
				if(!empty(count($datasOfMachine) >= 1)){
					if(in_array(strtolower($appform->hfser_id), ['lto','coa'])){
						$findIn = 'initial_amnt';
						if(isset($appform->aptid)){
							switch (true) {
								case ($appform->aptid == 'IN'):
									$findIn = 'initial_amnt';
									break;
								case ($appform->aptid == 'R'):
									$findIn = 'renewal_amnt';
									break;
							}
						}
						foreach($datasOfMachine as $machine){
							$price = $machine->amount;
							$total += $price;
							array_push($prices, $price);
							// if(strtolower($machine->uid) == 'SYSTEM'){
							// 	$lrf = $machine->amount;
							// }
						}
						$lrf = (DB::table('fda_chgfil')->where([['appid',$appid],['uid','SYSTEM'],['lrfFor','cdrrhr']])->select('amount')->first()->amount ?? 0);
						// dd($total);
						$wOLrf = $total;
						$total += $lrf;
						$retArr = [$datasOfMachine,$appform,count($datasOfMachine),$wOLrf,$total,$lrf];
					} else {
						return 'FDA is not allowed on this Application';
					}
					return $retArr;
				} else {
					return 'You have no machines Registered!';
				}
			} else {
				return 'noappid';
			}
			return $datasOfMachine;
		} catch (Exception $e) {
			return $e;
		}
	}


	public static function getDetailsFDACDRR($appid = ""){
		try {
			$total = 0;
			$retArr = $prices = [];
			if(! empty($appid)) {
				$datasOfMachine = self::getUserDetailsByAppform($appid)[0];
				if(!empty($datasOfMachine->noofmain) || !empty($datasOfMachine->noofsatellite)){
					$prices = DB::table('fda_pharmacycharges')->get();
					if(!empty($datasOfMachine->noofmain)){
						$total += $prices[0]->price * $datasOfMachine->noofmain;
					}
					if(!empty($datasOfMachine->noofsatellite)){
						$total += ($prices[1]->price * $datasOfMachine->noofsatellite);
					}
					$lrf = (DB::table('fda_chgfil')->where([['appid',$appid],['uid', 'SYSTEM'],['amount','>', 0],['lrfFor','cdrr']])->whereNull('MAvalue')->sum('amount') ?? 0);
					return [$datasOfMachine,$datasOfMachine->noofmain + $datasOfMachine->noofsatellite,$total,[$datasOfMachine->noofmain,$datasOfMachine->noofsatellite],$lrf];
				} else {
					return 'You Don\'t have any pharmacies Registered!';
				}
			} else {
				return 'noappid';
			}
			return $datasOfMachine;
		} catch (Exception $e) {
			return $e;
		}
	}
	public static function getDistinctByFacilityName() {
		try {
			$sql = "SELECT facilityname, region.rgn_desc, province.provname, city_muni.cmname, barangay.brgyname FROM appform LEFT JOIN region ON region.rgnid = appform.rgnid LEFT JOIN province ON province.provid = appform.provid LEFT JOIN city_muni ON city_muni.cmid = appform.cmid LEFT JOIN barangay ON barangay.brgyid = appform.brgyid WHERE appid IN (SELECT MAX(appid) AS appid FROM appform GROUP BY UPPER(facilityname))";
			return DB::select($sql); //array_chunk(DB::select($sql), 5);
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function getDistinctByFacilityNameRegFac() {
		try {
			$sql = "SELECT facilityname, 
			region.rgn_desc, 
			province.provname, 
			city_muni.cmname, 
			barangay.brgyname 
			FROM registered_facility 
			LEFT JOIN region ON region.rgnid = registered_facility.rgnid 
			LEFT JOIN province ON province.provid = registered_facility.provid 
			LEFT JOIN city_muni ON city_muni.cmid = registered_facility.cmid 
			LEFT JOIN barangay ON barangay.brgyid = registered_facility.brgyid 
			";

			return DB::select($sql); //array_chunk(DB::select($sql), 5);
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function getPTCDetails($appid = "") {
		try {
			$arrRet = [];
			if(! empty($appid)) {
				$arrRet = DB::table('ptc')->where([['appid', $appid]])->get();
			}
			return $arrRet;
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function getServFaclDetails($appid = "") {
		try {
			$arrRet = [];
			if(! empty($appid)) {
				$sql2 = "SELECT DISTINCT facid FROM `x08_ft` WHERE appid = '$appid'";
				$sql1 = "SELECT DISTINCT hgpid FROM facilitytyp WHERE facid IN ($sql2)";
				$sql3 = "SELECT facid, facname FROM facilitytyp WHERE facid IN ($sql2)";
				$sql4 = "SELECT hgpid, hgpdesc FROM hfaci_grp WHERE hgpid IN ($sql1)";
				$arrRet = [DB::select($sql1), DB::select($sql2), DB::select($sql3), DB::select($sql4)];
			}
			return $arrRet;
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function getServiceCharge($arrVal = [], $hfser = "", $facmode = "", $extraHgpid = "", $aptid = "") {
		try {
			$retArr = [];
			if(count($arrVal) > 0) {
				// $hospitalsFacid = ['H','H2','H3'];
				// foreach($arrVal as $arr){
				// 	if(in_array($arr, $hospitalsFacid)){
				// 		session()->forget('ambcharge');
				// 	}
				// }
				if(isset($facmode) && isset($extraHgpid)){
					$retArr = DB::table('serv_chg')->leftJoin('facilitytyp', 'facilitytyp.facid', '=', 'serv_chg.facid')->leftJoin('chg_app', 'chg_app.chgapp_id', '=', 'serv_chg.chgapp_id')->whereIn('serv_chg.facid', $arrVal)->where([['serv_chg.hfser_id', $hfser],['serv_chg.facmid',$facmode], ['extrahgpid', $extraHgpid]])
					// ->where('serv_chg.aptid', $aptid) // 6-4-2021
					->select('facilitytyp.facname', 'chg_app.amt', 'chg_app.chgapp_id', 'serv_chg.facid')
					->get();
					// $retArr = DB::table('serv_chg')->leftJoin('facilitytyp', 'facilitytyp.facid', '=', 'serv_chg.facid')->leftJoin('chg_app', 'chg_app.chgapp_id', '=', 'serv_chg.chgapp_id')->whereIn('serv_chg.facid', $arrVal)->where([['serv_chg.hfser_id', $hfser],['serv_chg.facmid',$facmode], ['extrahgpid', $extraHgpid]])->select('facilitytyp.facname', 'chg_app.amt', 'chg_app.chgapp_id')->get();
				}
				if(count($retArr) <= 0){
					// $retArr = DB::table('serv_chg')->leftJoin('facilitytyp', 'facilitytyp.facid', '=', 'serv_chg.facid')->leftJoin('chg_app', 'chg_app.chgapp_id', '=', 'serv_chg.chgapp_id')->whereIn('serv_chg.facid', $arrVal)->where([['serv_chg.hfser_id', $hfser], ['serv_chg.facmid',null], ['extrahgpid', null]])->select('facilitytyp.facname', 'chg_app.amt', 'chg_app.chgapp_id')->get();
					$retArr = DB::table('serv_chg')->leftJoin('facilitytyp', 'facilitytyp.facid', '=', 'serv_chg.facid')->leftJoin('chg_app', 'chg_app.chgapp_id', '=', 'serv_chg.chgapp_id')->whereIn('serv_chg.facid', $arrVal)->where([['serv_chg.hfser_id', $hfser], ['serv_chg.facmid',null], ['extrahgpid', null]])
					// ->where('serv_chg.aptid', $aptid) //6-4-2021
					->select('facilitytyp.facname', 'chg_app.amt', 'chg_app.chgapp_id', 'serv_chg.facid')
					->get();
				}

			}
			return $retArr;
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function getAncillaryServices($hgpid,$selected = null, $hfserid){
		try {
			$retArr = [];
			if(!empty($hgpid)){
				if(!isset($selected)){
					return json_encode(DB::table('facilitytyp')
					->where([['facilitytyp.servtype_id', 2],['facilitytyp.hgpid',$hgpid]])->get());
				} else {
					$toReturn = DB::table('facilitytyp')
					->where([['facilitytyp.servtype_id', '>' , 1],['facilitytyp.specified',$selected]]);
					if(isset($hfserid)){
						$toReturn = $toReturn->where('hfser_id',$hfserid)->get();
					} else {
						$toReturn = $toReturn->get();
					}
					return json_encode($toReturn);
				}
			}
		} catch (Exception $e) {
			return $e;
		}
	}
	public static function getGoAncillary($facid = []) {
		$retArr = [];
		if(count($facid) > 0) {
			for($i = 0; $i < count($facid); $i++) { $inFacid = $facid[$i]; $facid[$i] = "'$inFacid'"; } $impFacid = implode(', ', $facid);
			$sql = "SELECT * FROM facilitytyp WHERE servtype_id IN (SELECT servtype_id FROM serv_type, (SELECT grp_name, seq FROM serv_type WHERE facid IN (SELECT facid FROM facilitytyp WHERE facid IN ($impFacid) AND servtype_id = 1)) grpseq WHERE serv_type.grp_name IN (grpseq.grp_name) AND serv_type.seq > (grpseq.seq - 1)) ORDER BY grphrz_name, facname ASC";
			$sql1 = "SELECT * FROM serv_type, (SELECT grp_name, seq FROM serv_type WHERE facid IN (SELECT facid FROM facilitytyp WHERE facid IN ($impFacid) AND servtype_id = 1)) grpseq WHERE serv_type.grp_name IN (grpseq.grp_name) AND serv_type.seq > (grpseq.seq - 1)";
			$sql2 = "SELECT grphrz_name FROM `facilitytyp` WHERE servtype_id IN (SELECT servtype_id FROM serv_type, (SELECT grp_name, seq FROM serv_type WHERE facid IN (SELECT facid FROM facilitytyp WHERE facid IN ($impFacid) AND servtype_id = 1)) grpseq WHERE serv_type.grp_name IN (grpseq.grp_name) AND serv_type.seq > (grpseq.seq - 1)) GROUP BY grphrz_name";
			$retArr = [DB::select(DB::raw($sql)), DB::select(DB::raw($sql1)), DB::select(DB::raw($sql2))];
		}
		return $retArr;
	}
	public static function getApplyLocation($apptype,$hgpid){
		try {
			$retArr = [];
			if(!empty($hgpid) && !empty($apptype)){
				return json_encode(DB::table('chg_loc')
						->join('chg_faci','chg_loc.paymentLoc','chg_faci.applylocid')
						->join('chg_applyto','chg_loc.applyLoc','chg_applyto.applytoid')
						->join('facilitytyp','chg_loc.hgpid','facilitytyp.facid')
						->select('chg_faci.applytofaci','chg_applyto.applytoLoc')
						->where([['chg_loc.hfser_id', '=', $apptype],['chg_loc.hgpid', '=', $hgpid]])
						->get());
			}
		} catch (Exception $e) {
			return $e;
		}
	}
	public static function getReqUploads($hfser_id = "", $appid = "", $office = "hfsrb") {
		try {
			$retArr	= [];
			if((! empty($hfser_id)) && (! empty($appid))) {
				// $sql = "SELECT upload.upid, upload.updesc, upload.isRequired, app_upload.filepath, app_upload.evaluation, app_upload.remarks, app_upload.upDesc, app_upload.upDescRemarks FROM (SELECT * FROM upload WHERE upid IN (SELECT upid FROM app_upload WHERE app_id = '$appid' UNION ALL SELECT facility_requirements.upid FROM facility_requirements INNER JOIN type_facility ON type_facility.tyf_id = facility_requirements.typ_id WHERE hfser_id = '$hfser_id')) upload LEFT JOIN (SELECT * FROM app_upload WHERE app_upload.app_id = '$appid') app_upload ON app_upload.upid = upload.upid";
				$sql = "SELECT upload.upid, upload.updesc, upload.isRequired, app_upload.filepath, app_upload.evaluation, app_upload.remarks, app_upload.upDesc, app_upload.upDescRemarks FROM (SELECT * FROM upload WHERE upid IN (SELECT upid FROM app_upload WHERE app_id = '$appid' UNION ALL SELECT facility_requirements.upid FROM facility_requirements INNER JOIN type_facility ON type_facility.tyf_id = facility_requirements.typ_id WHERE hfser_id = '$hfser_id') AND office = '$office') upload LEFT JOIN (SELECT * FROM app_upload WHERE app_upload.app_id = '$appid') app_upload ON app_upload.upid = upload.upid"; 
				// "SELECT facility_requirements.fr_id, upload.upid, upload.updesc, upload.isRequired, type_facility.hfser_id, app_upload.filepath, app_upload.evaluation, app_upload.remarks, app_upload.upDesc FROM (SELECT * FROM app_upload WHERE app_upload.app_id = '$appid') app_upload LEFT JOIN facility_requirements ON app_upload.upid = facility_requirements.upid LEFT JOIN upload ON upload.upid = facility_requirements.upid LEFT JOIN type_facility ON type_facility.tyf_id = facility_requirements.typ_id WHERE type_facility.hfser_id = '$hfser_id'";
				$retArr = DB::select($sql);
			}
			return $retArr;
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function getChgfilCharges($appid = "", $cat_id = "", $chg_pmt_id = NULL) {
		$retArr = [];
		if(! empty($appid)) {
			$cat_id = ((! empty($cat_id)) ? $cat_id : 'C');
			$chg_pmt_id = ((isset($chg_pmt_id)) ? " AND chgfil.chgapp_id_pmt = '$chg_pmt_id'" : " AND (chgfil.chgapp_id_pmt IS NULL OR chgfil.chgapp_id_pmt = '')");
			$sql = "SELECT chgfil.* FROM chgfil INNER JOIN chg_app ON chg_app.chgapp_id = chgfil.chgapp_id INNER JOIN charges ON charges.chg_code = chg_app.chg_code INNER JOIN category ON category.cat_id = charges.cat_id WHERE (chgfil.chgapp_id_pmt IS NULL OR chgfil.chgapp_id_pmt = '') AND chgfil.appform_id = '$appid' AND category.cat_type = '$cat_id'$chg_pmt_id";
			$retArr = DB::select($sql);
		}
		return $retArr;
	}
	public static function getCONDetails($appid = "") {
		try {
			$retArr	= [[], []];
			if(! empty($appid)) {
				$retArr	= [DB::table('con_catch')->where([['appid', $appid], ['isfrombackend',NULL]])->get(), DB::table('con_hospital')->where([['appid', $appid]])->get()];
			}
			return $retArr;
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function getNOVRecords($appid = "") {
		$retArr = [];
		if(! empty($appid)) {
			$sql = "SELECT nov_issued.* FROM (SELECT * FROM (SELECT appid, hfsrbno FROM surv_form WHERE appid = '$appid') surv_form UNION ALL SELECT * FROM (SELECT appid, novid FROM mon_form WHERE appid = '$appid') mon_form) _all LEFT JOIN nov_issued ON _all.hfsrbno = nov_issued.novid";
			$retArr = DB::select($sql);
		}
		return $retArr;
	}
	public static function getChargesPerApplication($hgpid = [], $aptid = "", $hfser_id = "") {
		$retArr = []; $hgpidIn = "";
		if(!empty($hgpid) || !empty($aptid)) {
			foreach($hgpid AS $hgpidEach) { $hgpidIn .= ((! empty($hgpidIn)) ? ", '$hgpidEach'" : "'$hgpidEach'"); }

			$retArr = DB::select(DB::raw("SELECT chg_app.chgapp_id, charges.chg_desc, chg_app.amt FROM chg_app INNER JOIN charges ON chg_app.chg_code = charges.chg_code WHERE chg_app.chg_code IN (SELECT chg_code FROM charges WHERE hgpid IN ($hgpidIn)) AND chg_app.aptid = '$aptid' AND chg_app.hfser_id = '$hfser_id'"));
		}
		// else{
		// 	 $retArr = DB::select(DB::raw("SELECT chg_app.chgapp_id, charges.chg_desc, chg_app.amt FROM chg_app INNER JOIN charges ON chg_app.chg_code = charges.chg_code WHERE chg_app.chg_code IN (SELECT chg_code FROM charges WHERE hgpid IN ($hgpidIn))  AND chg_app.hfser_id = '$hfser_id'"));
		// }
		return $retArr;
	}
	public static function checkExpiryDate($dateCheck = NULL, $dateFrom = NULL, $addDaysNow = 0) {
		if(isset($dateCheck)) { 
			$dateFrom = ((isset($dateFrom)) ? Carbon::parse($dateFrom)->addDays($addDaysNow) : Carbon::now());
			return $dateFrom->greaterThanOrEqualTo(Carbon::parse($dateCheck));
		} return false;
	}
	public static function checkSession($isSession = true) {
		try {
			// session()->forget('uData');
			self::setUser();
			$curSession = self::getUser();
			if($isSession) {
				if(!isset($curSession)) {
					return ['client1', 'errRet', ['errAlt'=>'warning', 'errMsg'=>'Login first!']];
				} else {
					return [];
				}
			} else {
				if(isset($curSession)) {
					return ['client1/home', 'errRet', ['errAlt'=>'warning', 'errMsg'=>'Already logged in.']];
				} else {
					return [];
				}
			}
		} catch(Exception $e) {
			return ['client1', 'errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on checking session. Contact the admin']];
		}
	}
	public static function getSessionParamObj($typeSession = "", $param = "") {
		try {
			if(!empty($typeSession)) {
				$retData = session()->get($typeSession);
				if(!empty($param)) {
					$retData = session()->get($typeSession)->$param;
				}
				return $retData;
			}
			return "";
		} catch(Exception $e) {
			return "";
		}
	}
	public static function procLogin($username = "", $password = "") {
		// try {
			if(!empty($username) && !empty($password)) {
				$username = strtoupper($username);
				$_checkUser = DB::table('x08')->where([['uid', $username], ['grpid', 'C']])->first();
				if($_checkUser != null) {
					if(Carbon::now()->greaterThanOrEqualTo(Carbon::parse($_checkUser->lastTry)->addDays(1))) {
						if(DB::table('x08')->where([['uid', $username], ['grpid', 'C']])->update(['isTempBanned'=>NULL, 'tries'=>0, 'lastTry'=>NULL])) {
							return self::procVald(DB::table('x08')->where([['uid', $username], ['grpid', 'C']])->first(), $username, $password);
						}
						return self::procVald($_checkUser, $username, $password);
					}
					return self::procVald($_checkUser, $username, $password);
				} else {
					session()->forget('uData');
					return "Username does not exist or Incorrect. Please Check username or Register.";
				}
			} else {
				session()->forget('uData');
				return "Username and/or password is empty";
			}
		// } catch(Exception $e) {
		// 	return $e;
		// }
	}
	public static function procVald($_checkUser, $username = "", $password = "") {
		try {
			if(isset($_checkUser)) {
				if(!empty($username) && !empty($password)) {
					if(!AjaxController::isPasswordExpired($username)){
						$m99 = DB::table('m99')->first(); 
						$arrTemp = [$m99->pass_temp, $m99->pass_ban]; 
						$isNowBanned = [false, true];
						$cTCheck = ((isset($_checkUser->isTempBanned)) ? Carbon::parse($_checkUser->isTempBanned) : ((isset($_checkUser->lastTry)) ? Carbon::parse($_checkUser->lastTry)->addMinutes($m99->pass_min) : Carbon::now())); 
						$tTries = $_checkUser->tries + 1; 
						$indOf = array_search($tTries, $arrTemp); 
						$strMsg = ["Your account is temporarily banned, please try to login <strong>".(Carbon::parse($_checkUser->lastTry)->addMinutes($m99->pass_min))->diffForHumans(Carbon::now())."</strong> or Reset Password.", "Your account is permanently banned!"]; $isBanned = [0, 1]; 
						$isTempBanned = [Carbon::parse($_checkUser->lastTry)->addMinutes($m99->pass_min), $_checkUser->isTempBanned]; 
						$canProcPass = [Carbon::now()->greaterThanOrEqualTo($cTCheck), false]; 
						$__bool = Hash::check($password, $_checkUser->pwd);
						if($__bool) {
							if(isset($_checkUser->token)) {
								session()->forget('uData');
								return "Account still not verified! Please check email or <a href='".URL::asset('client1/resend_mail')."/".$_checkUser->uid."'>resend verification.</a>";
							} else {
								if(in_array($tTries, $arrTemp)) {
									session()->forget('uData');
									if(! $canProcPass[$indOf]) {
										return $strMsg[$indOf];
									}
									if($isNowBanned[$indOf]) {
										return $strMsg[$indOf];
									}
								}
								session()->put('uData', $_checkUser);
								DB::table('x08')->where([['uid', $username], ['grpid', 'C']])->update(['isTempBanned'=>NULL, 'tries'=>0, 'lastTry'=>NULL]);
								return true;
							}
						} else {
							session()->forget('uData');
							if(in_array($tTries, $arrTemp)) {
								if(! $canProcPass[$indOf]) {
									return $strMsg[$indOf];
								}
								if($isNowBanned[$indOf]) {
									return $strMsg[$indOf];
								}
								DB::table('x08')->where([['uid', $username], ['grpid', 'C']])->update(['tries'=>$tTries, 'isBanned'=>$isBanned[$indOf], 'isTempBanned'=>$isTempBanned[$indOf], 'lastTry'=>Carbon::now()]);
								return $strMsg[$indOf];
							}
							DB::table('x08')->where([['uid', $username], ['grpid', 'C']])->update(['tries'=>$tTries, 'lastTry'=>Carbon::now()]);
							return "Password incorrect. Please be notified that after three (3) tries, your account will be temporarily banned for ".$m99->pass_min." minute(s). <strong>Tries: ".$tTries."</strong>";
						}
					} else {
						return 'Your password has Expired. Please follow <a href="'.asset('client1/reset/').'/'.$username.'">this</a> link to create new password';
					}
				}
				return "No username and/or password.";
			}
			return "No user selected.";
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function findColGC($col = "", $val = "") {
		if(!empty($val) && !empty($col)) {
			return DB::table('x08')->where([[$col, $val], ['grpid', 'C']])->get();
		}
		return [];
	}
	public static function fPassword($arrCur = [], $token = "") {
		try {
			if(count($arrCur) > 0) {
				if(count($arrCur) > 0) {
					if(count($arrCur) > 1) {
						return "Email bound to many accounts!";
					}
					$dRequest = new \stdClass();
					$dRequest->text2 = $arrCur[0]->facilityname; $dRequest->text6 = $arrCur[0]->email;
					$sData = ['uid'=>$arrCur[0]->uid, 'token'=>$token];
					self::sMailVer('mail4FPUsers', $sData, $dRequest);
					return true;
				} else {
					return "No account bound to this email.";
				}
			}
			return "No email given.";
		} catch(Exception $e) {
			return $e;
		}
	}
	public static function fSave($request, $arrData = [], $arrCM = [], $makeHash = [], $haveAdd = [], $sMail = [], $validate = [], $tbl = "") {
		if(isset($request)) {
			$arrSave = [];
			foreach($request AS $rKey => $rValue) {
				$cValue = $rValue;
				if(in_array($rKey, $arrData)) {
					if(count($arrCM) > 0) { if(is_array($arrCM[0]) && is_array($arrCM[1])) {
						if(in_array($rKey, $arrCM[0])) {
							$arrThis = self::findColGC($rKey, $rValue);
							if(count($arrThis) > 0) {
								return $arrCM[1][$rKey];
							}
						}
					} }
					if(in_array($rKey, $makeHash)) {
						$cValue = Hash::make($rValue);
					}
					if(count($validate) > 1) { 
						if(is_array($validate[0])) {
							if(in_array($rKey, $validate[0])) {
								if(!isset($rValue)) {
									return $validate[1][$rKey];
								}
							}
						} 
					}
					array_push($arrSave, $cValue);
				}
			}
			foreach($haveAdd AS $hKey => $hValue) {
				array_push($arrData, $hKey);
				array_push($arrSave, $hValue);
			}
			if(count($arrSave) == count($arrData)) {
				$insData = array_combine($arrData, $arrSave);


				if(count($sMail) > 0) {
					$dRequest = new \stdClass();
					$dRequest->text2 = $insData[$sMail[2][0]]; $dRequest->text6 = $insData[$sMail[2][1]];
					if(self::sMailVerRetBool($sMail[0], $sMail[1], $dRequest)) {
						return $insData;
					} else {
						// qwe
						return $insData;
						return "Error on sending Email Request.[".json_encode($sMail[0]).", ".json_encode($sMail[1]).", ".json_encode($dRequest)."]";
					}
				} else {
					return $insData;
				}


			}
			return "Data provided is not enough.";
		}
		return "No data provided.";
	}
	public static function fInsSel($col1 = "", $tbl1 = "", $col2 = "", $tbl2 = "", $where = "") {
		if(! empty($tbl1) && ! empty($tbl2) && ! empty($col2)) {
			$where = ((! empty($where)) ? "WHERE $where" : ""); $col1 = ((! empty($col1)) ? "($col1)" : ""); 
			$sql = "INSERT INTO $tbl1 $col1 SELECT $col2 FROM $tbl2 $where";
			DB::statement($sql); // DB::raw()
			return true;
		}
		return "Column and/or tablename is empty. Columns: [$col1, $tbl1, $col2, $tbl2]";
	}
	public static function fInsData($request, $arrData = [], $arrCM = [], $makeHash = [], $haveAdd = [], $sMail = [], $validate = [], $tbl = "", $isFromBasicDetails = false) {
		if(! empty($tbl)) {
			$insData = self::fSave($request, $arrData, $arrCM, $makeHash, $haveAdd, $sMail, $validate, $tbl);
			if(is_array($insData)) {
				if(strtolower($tbl) == 'appform' && $isFromBasicDetails){
					$dataToCheck = [['rgnid', $request['rgnid']],['brgyid', $request['brgyid']],['facilityname', $request['facilityname']]];
					// if(self::existOnDB($tbl,$dataToCheck)){
					// 	return 'Facility Already Registered.';
					// }
				}
				DB::table($tbl)->insert($insData);
				return true;
			}
			return $insData;
		}
		return "No table provided.";
	}
	public static function fUpdData($request, $arrData = [], $arrCM = [], $makeHash = [], $haveAdd = [], $sMail = [], $validate = [], $tbl = "", $where = [], $recordToHistory = false) {
		if(! empty($tbl)) {
			if(count($where) > 0) {
				$insData = self::fSave($request, $arrData, $arrCM, $makeHash, $haveAdd, $sMail, $validate, $tbl);
				if(is_array($insData)) {
					if($recordToHistory){
						$fieldsToInsert = json_encode(DB::table($tbl)->where($where)->get());
						DB::table('table_history')->insert(['fieldvalue' => $fieldsToInsert, 'uid' => (AjaxController::getCurrentUserAllData()['cur_user'] ?? (session()->get('uData')->uid ?? 'NO DATA') ), 'ip' => request()->ip(), 'whereclause' => json_encode($where), 'tablename' => $tbl, 'id' => ($request['appid'] ?? 'NO-DATA')]);
					}
					DB::table($tbl)->where($where)->update($insData);
					return true;
				}
				return $insData;
			}
			return "No condition(s) provided.";
		}
		return "No table provided.";
	}
	public static function uploadFile($dFile) {
		$retArr = [];
		if(isset($dFile)) {
			$_file = $dFile;
			$filename = $_file->getClientOriginalName(); 
	        $filenameOnly = pathinfo($filename,PATHINFO_FILENAME); 
	        $fileExtension = $_file->getClientOriginalExtension();
	        $fileNameToStore = (session()->has('employee_login') ? self::getSessionParamObj("employee_login", "uid") : self::getSessionParamObj("uData", "uid")).'_'.Str::random(10).'_'.date('Y_m_d_i_s').'.'.$fileExtension;
	        $filemMIME = $_file->getMimeType();
	        $path = $_file->storeAs('public/uploaded', $fileNameToStore);
	        $fileSize = $_file->getClientSize();
	        $retArr = ['fileExtension'=>$fileExtension, 'fileNameToStore'=>$fileNameToStore, 'fileSize'=>$fileSize, 'mime'=> $filemMIME];
	    }
        return $retArr;
	}

	public static function hasEmptyDBFields($table = null, $where = [], $fields = []){
		$haslist = false;
		$arrEmpty = array();
		if(isset($table) && isset($fields) && isset($where)){
			// $test = DB::table($table)->where($where)->get();
			// foreach ($test as $key) {
			// 	foreach ($fields as $field) {
			// 		if(empty($key->$field)){
			// 			if(!in_array($field, $arrEmpty)){
			// 				array_push($arrEmpty, $field);
			// 			}
			// 		}
			// 	}
			// }
			// return [(empty($arrEmpty) ? false : true),$arrEmpty];
			$res = DB::table($table)->where($where);
			
			if($res->first()){
				$test = DB::table($table)->where($where)->get();
				foreach ($test as $key) {
					foreach ($fields as $field) {
						if(empty($key->$field)){
							if(!in_array($field, $arrEmpty)){
								array_push($arrEmpty, $field);
							}
						}
					}
				}

				$haslist = true;
				
			}else{
				$arrEmpty = array();
				$haslist = false;
			}
			return [(empty($arrEmpty) ? false : true),$arrEmpty, $haslist];
			// return [(empty($arrEmpty) ? false : true),$arrEmpty, $haslist];
		}
	}

	public static function insPaymentCharges() { 
		$cAppid = ""; $retArr = true; $tPayment = 0; $arrSaveChgfil = ['chgapp_id', 'chg_num', 'appform_id', 'chgapp_id_pmt', 'orreference', 'deposit', 'other', 'au_id', 'au_date', 'reference', 'amount', 't_date', 't_time', 't_ipaddress', 'uid'];
		$payment = self::getSessionParamObj("payment"); 
		$appcharge = self::getSessionParamObj("appcharge"); 
		$ambcharge = self::getSessionParamObj("ambcharge");
		
		if(isset($payment) || isset($appcharge) || isset($ambcharge)) {
			if(isset($payment)) { 
				$pArr = $payment[self::getSessionParamObj("uData", "uid")]; 
				if(isset($pArr[0])) {
					$cAppid = $pArr[1];
					foreach($pArr[0] AS $each) {
						$ifexist = DB::table('chgfil')->where([['chgapp_id', $each->chgapp_id], ['appform_id', $pArr[1]]])->first();
						if(isset($ifexist)) { $each->amt = $each->amt - $ifexist->amount; }
						if($each->amt > 0) {
							$chg_num = (DB::table('chg_app')->where([['chgapp_id', $each->chgapp_id]])->first())->chg_num;
							$arrDataChgfil = [$each->chgapp_id, $chg_num, $pArr[1], NULL, NULL, NULL, NULL, NULL, NULL, $each->facname, $each->amt, Carbon::now()->toDateString(), Carbon::now()->toTimeString(), request()->ip(), self::getSessionParamObj("uData", "uid")];
							if(DB::table('chgfil')->insert(array_combine($arrSaveChgfil, $arrDataChgfil))) {
								$tPayment +=  $each->amt;
								DB::table('chg_app')->where([['chgapp_id', $each->chgapp_id]])->update(['chg_num' => ($chg_num + 1)]);
							}
						}
					}
				} 
			}
			if(isset($appcharge)) { $appcharge1 = $appcharge[self::getSessionParamObj("uData", "uid")]; if(isset($appcharge1[0])) {
				$cAppid = $appcharge1[1];
				foreach($appcharge1[0] AS $each1) {
					$ifexist = DB::table('chgfil')->where([['chgapp_id', $each1->chgapp_id], ['appform_id', $appcharge1[1]]])->first();
					if(isset($ifexist)) { $each1->amt = $each1->amt - $ifexist->amount; }
					if($each1->amt > 0) {
						$chg_num = (DB::table('chg_app')->where([['chgapp_id', $each1->chgapp_id]])->first())->chg_num;
						$arrDataChgfil = [$each1->chgapp_id, $chg_num, $appcharge1[1], NULL, NULL, NULL, NULL, NULL, NULL, $each1->chg_desc, $each1->amt, Carbon::now()->toDateString(), Carbon::now()->toTimeString(), request()->ip(), self::getSessionParamObj("uData", "uid")];
						if(DB::table('chgfil')->insert(array_combine($arrSaveChgfil, $arrDataChgfil))) {
							$tPayment +=  $each1->amt;
							DB::table('chg_app')->where([['chgapp_id', $each1->chgapp_id]])->update(['chg_num' => ($chg_num + 1)]);
						}
					}
				}
			} }
			if(isset($ambcharge)) { $ambcharge1 = $ambcharge[self::getSessionParamObj("uData", "uid")]; if(isset($ambcharge1[0])) {
				$cAppid = $ambcharge1[1];
				foreach($ambcharge1[0] AS $each2) {
					$ifexist = DB::table('chgfil')->where([['reference', $each2->chg_desc], ['appform_id', $ambcharge1[1]]])->first();
					if(isset($ifexist)) { $each2->amt = $each2->amt - $ifexist->amount; }
					if($each2->amt > 0) {
						$arrDataChgfil = [$each2->chgapp_id, NULL, $ambcharge1[1], NULL, NULL, NULL, NULL, NULL, NULL, $each2->chg_desc, $each2->amt, Carbon::now()->toDateString(), Carbon::now()->toTimeString(), request()->ip(), self::getSessionParamObj("uData", "uid")];
						if(DB::table('chgfil')->insert(array_combine($arrSaveChgfil, $arrDataChgfil))) {
							$tPayment +=  $each2->amt;
						}
					}
				}
			} }
			$chkGet = DB::table('appform_orderofpayment')->where([['appid', $cAppid]])->first();
			if(isset($chkGet)) {
				DB::table('appform_orderofpayment')->where([['appop_id', $chkGet->appop_id]])->update(['oop_total' => ($chkGet->oop_total + $tPayment)]);
			} else {
				DB::table('appform_orderofpayment')->insert(['appid' => $cAppid, 'oop_total' => $tPayment, 'oop_time' => Carbon::now()->toTimeString(), 'oop_date' => Carbon::now()->toDateString(), 'oop_ip' => request()->ip(), 'uid' => self::getSessionParamObj("uData", "uid")]);
			}
			session()->forget('payment'); session()->forget('appcharge'); session()->forget('ambcharge');
			if(self::setEmailPayment($cAppid)) {
				$retArr = true;
			} else {
				$retArr = "Error on sending email verification. But your data has been saved.";
			}
		}
		// if(isset($payment)) { $pArr = $payment[self::getSessionParamObj("uData", "uid")]; if(isset($pArr[0])) { if(count($pArr[0]) > 0) {
		// } else { $retArr = true; } } else { $retArr = true; } } else { $retArr = true; }
		return $retArr;
	}

	public static function isFacilityFor($appid){
		$appdata = DB::table('appform')->where('appid',$appid)->first();
		// dd($appdata);
		return ($appdata->assignedRgn ?? $appdata->rgnid);
		$primaryApp = DB::table('x08_ft')->where('appid',$appid)->join('facilitytyp','facilitytyp.facid','x08_ft.facid')->where('facilitytyp.servtype_id',1)->first();
		if(isset($primaryApp)){
			if($primaryApp->assignrgn == 'hfsrb' && $appdata->hfep_funded != 1){
				return 'hfsrb';
			}
		}
		return $appdata->rgnid;
	}

	public static function insPayment($request, $ip, $chgapp_id = "", $appid = "", $totalAmount = 0) {
		$chg_app = DB::table('chg_app')->where([['chgapp_id', $chgapp_id]])->first();
		$retArr = []; $arrSaveChgfil = ['chgapp_id', 'chg_num', 'appform_id', 'chgapp_id_pmt', 'orreference', 'deposit', 'other', 'au_id', 'au_date', 'reference', 'amount', 't_date', 't_time', 't_ipaddress', 'uid']; $tPayment = $totalAmount; $tDesc = "Application Payment"; $tChg_num = (DB::table('chg_app')->where([['chgapp_id', $chgapp_id]])->first())->chg_num; $chkGet = DB::table('appform_orderofpayment')->where([['appid', $appid]])->first();
		if(isset($request->au_file)) {
	        $reData = self::uploadFile($request->au_file);
			$arrDataUpl = ['app_id', 'upid', 'filepath', 'fileExten', 'fileSize', 't_date', 't_time', 'ipaddress'];
			$arrSaveUpl = [$appid, NULL, $reData['fileNameToStore'], $reData['fileExtension'], $reData['fileSize'], Carbon::now()->toDateString(), Carbon::now()->toTimeString(), $ip];
			if(DB::table('app_upload')->insert(array_combine($arrDataUpl, $arrSaveUpl))) {
				$updId = DB::table('app_upload')->where([['app_id', $appid]])->orderBy('apup_id', 'desc')->first();
				$arrDataChgfil = [$chgapp_id, $tChg_num, $appid, $chgapp_id, $request->au_ref, NULL, NULL, $updId->apup_id, $updId->t_date, $request->au_ref, ($request->au_amount * -1), Carbon::now()->toDateString(), Carbon::now()->toTimeString(), $ip, self::getSessionParamObj("uData", "uid")];
				if(DB::table('chgfil')->insert(array_combine($arrSaveChgfil, $arrDataChgfil))) {
					DB::table('chg_app')->where([['chgapp_id', $each->chgapp_id]])->update(['chg_num' => ($chg_num + 1)]);
					if(isset($chkGet)) {
						DB::table('appform_orderofpayment')->where([['appop_id', $chkGet->appop_id]])->update(['oop_paid' => ($chkGet->oop_paid + $request->au_amount), 'oop_total' => ($chkGet->oop_total + $tPayment)]);
						DB::table('appform')->where([['appid', $appid]])->update(['status'=>'PP']);
						// DB::table('appform')->where([['appid', $appid]])->update(['status'=>'PP', 	'isrecommended'=>1,'isPayEval' => 1,]);
					} else {
						DB::table('appform_orderofpayment')->insert(['appid' => $appid, 'oop_paid' => $request->au_amount, 'oop_total'	=> $tPayment, 'oop_time' => Carbon::now()->toTimeString(), 'oop_date' => Carbon::now()->toDateString(), 'oop_ip' => $ip, 'uid' => self::getSessionParamObj("uData", "uid")]);
						DB::table('appform')->where([['appid', $appid]])->update(['status'=>'PP']);
						// DB::table('appform')->where([['appid', $appid]])->update(['status'=>'PP', 	'isrecommended'=>1,'isPayEval' => 1,]);
					}
				}
			}
		} else {
			DB::table('chgfil')->whereIn('id', self::retArrFromTbl('id', self::getChgfilCharges($appid)))->update(['chgapp_id_pmt'=>$chgapp_id]);
			// DB::table('appform')->where([['appid', $appid]])->update(['status'=>'PP']);
			if(isset($chkGet)) {
				DB::table('appform_orderofpayment')->where([['appop_id', $chkGet->appop_id]])->update(['oop_total' => ($chkGet->oop_total + $tPayment)]);
			} else {
				DB::table('appform_orderofpayment')->insert(['appid' => $appid, 'oop_total'	=> $tPayment, 'oop_time' => Carbon::now()->toTimeString(), 'oop_date' => Carbon::now()->toDateString(), 'oop_ip' => $ip, 'uid' => self::getSessionParamObj("uData", "uid")]);
			}
		}
		$retArr = ['errRet', ['errAlt'=>'success', 'errMsg'=>'Successfully submitted application form and updated payment information. ']];
		return $retArr;
	}
	public static function setEmailPayment($appid = "") {
		$amount = 0; foreach(self::getChgfilTransactions($appid, 'C') AS $each) { $amount += $each->amount; }
		$arrRet = [
			'userInf'=>self::getUserDetails(),
			'appDet'=>self::getUserDetailsByAppformWithTransactions($appid),
			'totalWord'=>[$amount, self::moneyToString($amount)]
		]; $appform = self::getUserDetailsByAppform($appid,DB::table('appform')->where('appid',$appid)->select('uid')->first()->uid);
		if(!empty($appform)){
			$dRequest = new \stdClass();
			$dRequest->text2 = $appform[0]->owner; $dRequest->text6 = $appform[0]->email;
			return self::sMailSendRetBool('client1.mail4Payment', $arrRet, $dRequest, 'Order of Payment', 'doholrs@gmail.com', 'DOH Support');
		}
		return true;
	}
	public static function setEmailExpired($appid = "") {
		$appform = self::getUserDetailsByAppform($appid);
		if(count($appform) > 0) { $appform = $appform[0]; if(isset($appform->validDate)) { if(self::checkExpiryDate($appform->validDate, date('Y-m-d'), 90)) {
			$arrRet = [
				'userInf'=>self::getUserDetails(),
				'appDet'=>$appform,
				'expireDate'=>Carbon::parse($appform->validDate)->format('M d, Y')
			];
			$dRequest = new \stdClass();
			$dRequest->text2 = $appform->owner; $dRequest->text6 = $appform->email;
			if(self::sMailSendRetBool('client1.mail4Expire', $arrRet, $dRequest, 'License Expiration', 'doholrs@gmail.com', 'DOH Support')) {
				DB::table('appform')->where([['appid', $appid]])->update(['isNotified'=>Carbon::now()->toDateString()]);
				DB::table('notificiationlog')->insert([
					'notftime'=>Carbon::now()->toTimeString(),
					'notfdate'=>Carbon::now()->toDateString(),
					'uid'=>$appform->uid,
					'message'=>'Your application on '.$appform->facilityname.' ('.$appform->hfser_id.'R'.$appform->rgnid.'-'.$appform->appid.') has expired.',
					'status'=>1
				]);
				return true;
			} else {
				return false;
			}
		} } }
		return false;
	}
	public static function procInsCharges($arrMsg = []) {
		$bolRet = true; $errMsg = "";
		foreach($arrMsg AS $each) {
			if($each != true) {
				$bolRet = false; $errMsg = $each;
			}
		}
		if($bolRet) {
			return [self::insPaymentCharges()];
		} else {
			return [$errMsg];
		}
	}
	public static function getTotalAmount($col = "", $arr = []) {
		$retDouble = 0;
		if(! empty($col)) { if(count($arr) > 0) {
			foreach($arr AS $each) {
				$tDouble = ((isset($each->$col)) ? $each->$col : ((isset($each[$col])) ? $each[$col] : 0));
				$retDouble += $tDouble;
			}
		} }
		return $retDouble;
	}
	public static function retArrFromTbl($col = "", $arr = []) {
		$retArr = [];
		if(! empty($col)) { if(count($arr) > 0) {
			foreach($arr AS $each) {
				$tVal = self::retColArr($each, $col);
				array_push($retArr, $tVal);
			}
		} }
		return $retArr;
	}
	public static function retColArr($arr = [], $col = "", $default = "") {
		return ((isset($arr) && isset($col)) ? ((gettype($arr) == "array") ? $arr[$col] : ((gettype($arr) == "object") ? $arr->$col : $default)) : $default);
	}
	public static function forExtraIncrement() {
		$forInc = self::returnColumns("COLUMNS", "TABLE_SCHEMA = 'rightapp_doholrs' AND COLUMN_KEY = 'PRI' AND COLUMN_TYPE = 'int(11)' AND (EXTRA IS NULL OR EXTRA = '')");
		foreach($forInc AS $forIncEach) {
			DB::select("ALTER TABLE `$forIncEach->TABLE_NAME` CHANGE `$forIncEach->COLUMN_NAME` `$forIncEach->COLUMN_NAME` INT(11) NOT NULL AUTO_INCREMENT;");
		}
		return true;
	}
	public static function getTablesConnectedAppform($appid = "") {
		$retArr = [];
		$sql = self::returnColumns("COLUMNS", "COLUMN_NAME IN ('appid', 'app_id', 'appform_id', 'appformid')");
		if(! empty($appid)) {
			foreach($sql AS $sqlEach) {
				$tbl = $sqlEach->TABLE_NAME; $col = $sqlEach->COLUMN_NAME;
				$retSql = "SELECT * FROM $tbl WHERE $col = '$appid'";
				$retArr[$tbl] = $col; //DB::select(DB::raw($retSql));
			}
		}
		return $retArr;
	}
	public static function returnColumns($tbl = "TABLES", $where = "") {
		$where = ((! empty($where)) ? "WHERE $where" : "");
		return DB::select(DB::raw("SELECT * FROM INFORMATION_SCHEMA.$tbl $where"));
	}
	public static function arrayString_agg($col = "", $arr = []) {
		$retStr = ""; $nArr = self::retArrFromTbl($col, $arr);
		foreach($nArr AS $key => $value) {
			$retStr .= ((! empty($retStr)) ? ", $value" : "$value");
		}
		return $retStr;
	}
	public static function getCol($tbl = "", $where = [], $col = "") {
		if(! empty($tbl)) {
			$return = DB::table(DB::raw($tbl));
			if(count($where) > 0) {
				$bool = true; foreach($where AS $whereEach) { if(! is_array($whereEach)) { $bool = false; } }
				if($bool) {
					$return = $return->where($where);
				}
			}
			if(! empty($col)) { $return = $return->select($col); }
			return $return->get();
		}
		return [];
	}
	// syrel added
	// @param string
	// @param array
	public static function existOnDB($table, $checkFor = []){
		if(!empty($checkFor) && !empty($table)){
			$checkedData = DB::table($table)->where($checkFor)->count();
			return ($checkedData >= 1 ? true : false);
		} else if(empty($checkFor)) {
			return 'empty table to check';
		} else {
			return 'empty item(s) to check';
		}
	}

	public static function isUserApplication($appid){
		if(isset($appid) && session()->has('uData')){
			$uid = DB::table('appform')->select('uid')->where('appid',$appid)->first();
			if(isset($uid->uid)){
				if(session()->get('uData')->uid == $uid->uid){
					return true;
				}
			}
		}
		return false;

	}

	public static function notifyForChange($appid){
		if($appid && !session()->has('uData')){
			$uid = AjaxController::getUidFrom($appid);
			AjaxController::notifyClient($appid,$uid,68);
			return true;
		}	
		return false;
	}

}