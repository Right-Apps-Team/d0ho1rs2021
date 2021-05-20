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
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\EvaluationController;
use FunctionsClientController;
use DOHController;
use AjaxController;
use App\Models\FACLGroup;
use App\Models\HFACIGroup;
use App\Models\Regions;
use QrCode;
class NewClientController extends Controller {
	protected static $curUser;
	public function __index(Request $request) {
		try {
			$cSes = FunctionsClientController::checkSession(false);
			if(count($cSes) > 0) {
				return redirect($cSes[0])->with($cSes[1], $cSes[2]);
			}
			AjaxController::getHeaderSettings();
			$arrRet = [
				'region'=>DB::table('region')->whereNotIn('rgnid', ['HFSRB','FDA'])->get()
			];
			return view('client1.login', $arrRet);
		} catch(Exception $e) {
			dd($e);
			// return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Login. Contact the admin']);
		}
	}
	
	public function __newlogout(Request $request) {
		try {
			session()->forget('uData'); 
			session()->forget('payment');
			session()->forget('appcharge');
			session()->forget('ambcharge');
			session()->forget('directorSettings');
			
			return view('client1.login');
		} catch(Exception $e) {
			dd($e);
			// return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Login. Contact the admin']);
		}
	}

	public function faq(){
		return view('client1.faq');
	}

	public function __forgot(Request $request, $uid, $token) {
		try {
			$cSes = FunctionsClientController::checkSession(false);
			if(count($cSes) > 0) {
				return redirect($cSes[0])->with($cSes[1], $cSes[2]);
			}
			$appDet = FunctionsClientController::getUserDetails($uid);
			$arrRet = [
				'appDet'=>json_encode($appDet)
			];
			if(count($appDet) < 1) {
				return redirect('client1')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'No user associated with that user id.']);
			}
			return view('client1.forgot', $arrRet);
		} catch(Exception $e) {
			return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Forgot. Contact the admin']);
		}
	}

	public function __reset(Request $request, $uid) {
		// if(session()->has('uData') && session()->get('uData')->uid == $uid){
			try {
				$chck = $pwd = null;
				if ($request->isMethod('get')) 
				{
					try 
					{
						if (DB::table('x08')->where('uid',$uid)->exists()) {
							if(AjaxController::isPasswordExpired($uid)){
								return view('client1.reset');
							} else {
								return redirect('client1')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'Password is not yet expired!']);
							}
						} else {
							return redirect('client1')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'User not found!']);
						}
					} 
					catch (Exception $e) 
					{
						dd($e);
					}
				}
				if ($request->isMethod('post')) 
				{
					return AjaxController::processExpired($uid,$request->pwd,$request->pass);
				}
			} catch(Exception $e) {
				return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Forgot. Contact the admin']);
			}
		// } else {
		// 	return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Action not allowed']);
		// }
	}

	public function __changePass(Request $request) {
		if(session()->has('uData')){
			try {
				$uDetails = session()->get('uData');
				$chck = $pwd = null;
				if ($request->isMethod('get')) 
				{
					try 
					{
						if (DB::table('x08')->where('uid',$uDetails->uid)->exists()) {
							return view('client1.reset');
						}
					} 
					catch (Exception $e) 
					{
						dd($e);
					}
				}
				if ($request->isMethod('post')) 
				{
					return AjaxController::processExpired($uDetails->uid,$request->pwd,$request->pass);
				}
			} catch(Exception $e) {
				return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Forgot. Contact the admin']);
			}
		} else {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Action not allowed']);
		}
	}

	public function __home(Request $request) {
		try {
			// dd(Route::getFacadeRoot()->current()->uri());
			$cSes = FunctionsClientController::checkSession(true);
			if(count($cSes) > 0) {
				return redirect($cSes[0])->with($cSes[1], $cSes[2]);
			}
			$data = FunctionsClientController::getUserDetails();
			if(!session()->has('fornav')){
				session()->put('fornav',$data);
			}
			$uid = $data[0]->uid;
			$appGet = FunctionsClientController::getApplicationDetailsWithTransactions(FunctionsClientController::getSessionParamObj("uData", "uid"), "IN", true);
			foreach ($appGet as $key => $value) {
				switch ($value[0]->hfser_id) {
					case 'PTC':
						$appGet[$key][4] = DB::table('hferc_evaluation')->where([['appid',$value[0]->appid],['HFERC_eval',1]])->first();
						break;

					case 'LTO':
						$appGet[$key][4] = DB::table('assessmentrecommendation')->where([['appid',$value[0]->appid],['choice','issuance']])->first();
						break;

					case 'CON':
						$appGet[$key][4] = DB::table('con_evaluate')->where('appid',$value[0]->appid)->first();
						break;
				}
			}
			$arrRet = [
				'appDet'=>$appGet,
				'userInf'=>$data,
				'year' => DB::select("SELECT * FROM `appform` LEFT JOIN `hfaci_serv_type` on hfaci_serv_type.hfser_id = appform.hfser_id WHERE (appform.uid = '$uid' AND year(t_date) < year(CURRENT_TIMESTAMP) )")
			];
			return view('client1.home', $arrRet);
		} catch(Exception $e) {
			dd($e);
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Apply. Contact the admin']);
		}
	}
	public function __apply(Request $request) {
		try {
			$cSes = FunctionsClientController::checkSession(true);
			if(count($cSes) > 0) {
				return redirect($cSes[0])->with($cSes[1], $cSes[2]);
			}
			$arrRet = [
				'appDet'=>FunctionsClientController::getApplicationDetailsWithTransactions(FunctionsClientController::getSessionParamObj("uData", "uid")),
				'userInf'=>FunctionsClientController::getUserDetails(),
				'legends' => DB::table('trans_status')->where([['allowedlegend',1],['color','<>',null]])->get()
			];
			// dd($arrRet);
			return view('client1.apply', $arrRet);
		} catch(Exception $e) {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Apply. Contact the admin']);
		}
	}
	public function __applyNew(Request $request) {
		try {
			$cSes = FunctionsClientController::checkSession(true);
			if(count($cSes) > 0) {
				return redirect($cSes[0])->with($cSes[1], $cSes[2]);
			}
			$appGet = FunctionsClientController::getUserDetailsByAppform(null, null,1);
			unset($appGet[0]->areacode);
			// $curForm = FunctionsClientController::getUserDetailsByAppform($appid);
			$arrRet = [
				'curUserDet'=>json_encode(FunctionsClientController::getUserDetails()),
				'hfaci'=>DB::table('hfaci_serv_type')->orderBy('seq_num', 'ASC')->get(),
				'appFacName'=>FunctionsClientController::getDistinctByFacilityName(),
				'region'=>DB::table('region')->whereNotIn('rgnid', ['HFSRB','FDA'])->orderBy('sort','asc')->get(),
				'curForm'=>(isset($appGet[0]->appid) ? json_encode(FunctionsClientController::getUserDetailsByAppform($appGet[0]->appid)) : json_encode([])),
				'userInf'=>FunctionsClientController::getUserDetails(),
				//new
				'ownership'=>DB::table('ownership')->get(),
				'function'=>DB::table('funcapf')->get(),
				'facmode'=>DB::table('facmode')->get(),
				'class'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NULL OR isSub = '')")),
				'subclass'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NOT NULL OR isSub != '')")),
				'fAddress'=>(isset($appGet[0]->appid) ? json_encode($appGet) : json_encode([])),
				'servfac'=>(isset($appGet[0]->appid) ? json_encode(FunctionsClientController::getServFaclDetails($appGet[0]->appid)) : json_encode([])),
				'ptcdet'=>(isset($appGet[0]->appid) ? json_encode(FunctionsClientController::getPTCDetails($appGet[0]->appid)) : json_encode([])),
			];
			
			return view('client1.applynew', $arrRet);
		} catch(Exception $e) {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Add new Application. Contact the admin']);
		}
	}
	public function __applyEdit(Request $request, $appid) {
		try {
			$cSes = FunctionsClientController::checkSession(true);
			if(count($cSes) > 0) {
				return redirect($cSes[0])->with($cSes[1], $cSes[2]);
			}
			$appGet = FunctionsClientController::getUserDetailsByAppform($appid, null);
			unset($appGet[0]->areacode);
			$curForm = FunctionsClientController::getUserDetailsByAppform($appid);
			if(count($curForm) < 1) {
				return redirect('client1/apply')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'No application selected.']);
			}
			$arrRet = [
				'applicationType' => 'edit',
				'curUserDet'=>json_encode(FunctionsClientController::getUserDetails()),
				'hfaci'=>DB::table('hfaci_serv_type')->orderBy('seq_num', 'ASC')->get(),
				'appFacName'=>FunctionsClientController::getDistinctByFacilityName(),
				'region'=>DB::table('region')->whereNotIn('rgnid', ['HFSRB','FDA'])->get(),
				'curForm'=>json_encode($curForm),
				'userInf'=>FunctionsClientController::getUserDetails(),
				//new
				'ownership'=>DB::table('ownership')->get(),
				'function'=>DB::table('funcapf')->get(),
				'facmode'=>DB::table('facmode')->get(),
				'class'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NULL OR isSub = '')")),
				'subclass'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NOT NULL OR isSub != '')")),
				'fAddress'=>json_encode($appGet),
				'servfac'=>json_encode(FunctionsClientController::getServFaclDetails($appid)),
				'ptcdet'=>json_encode(FunctionsClientController::getPTCDetails($appid)),
			];
			return view('client1.applynew', $arrRet);
		} catch(Exception $e) {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Add new Application. Contact the admin']);
		}
	}
	public function __applyAttach(Request $request, $hfser, $appid, $office = 'hfsrb') {
		// try {
			$office = AjaxController::listsofapproved(['hfsrb','xray','pharma'],strtolower($office),'hfsrb');
			$arrFaci = array();
			$req = null;
			$submitted = false;
			$lookFor = array(null,3);
			$cSes = FunctionsClientController::checkSession(true);

			if(count($cSes) > 0) {
				return redirect($cSes[0])->with($cSes[1], $cSes[2]);
			}

			$curForm = FunctionsClientController::getUserDetailsByAppform($appid);
			// dd($request);
			if(count($curForm) < 1) {
				return redirect('client1/apply')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'No application selected.']);
			}

			if($request->isMethod("post")) {
				if($request->has('action') && $request->action == 'trigger'){
					if(DB::table('appform')->where('appid',$appid)->update(['isReadyForInspec' => 1])){
						return 'DONE';
					}
				} else {
				$curRecord = []; $msgRet = []; $isApproved = [1, null]; $isAllUpload = [];
				foreach(FunctionsClientController::getReqUploads($hfser, $appid, $office) AS $each) {
					if(! isset($each->filepath)) {
						array_push($curRecord, $each->upid);
					} else {
						if(! in_array($each->evaluation, $isApproved)) {
							if(in_array($curForm[0]->canapply, [1])) {
								array_push($curRecord, $each->upid);
							}
						} else {
							if($each->evaluation == 0) {
								if(in_array($curForm[0]->canapply, [1])) {
									array_push($curRecord, $each->upid);
								}
							}
						}
					}
				}

				if($request->has('upload')){
					if($curForm[0]->isReadyForInspec == 0){
						DB::table('appform')->where('appid',$appid)->update(['isReadyForInspec' => 1]);
					}
					foreach($request->upload AS $uKey => $uValue) {
						if(in_array($uKey, $curRecord)) {
							$arrFind = DB::table('app_upload')->where([['app_id', $appid], ['upid', $uKey]])->get(); $_file = $request->upload[$uKey];
							// dd($arrFind);
							if(isset($_file) || ! empty($_file)) {
				                $reData = FunctionsClientController::uploadFile($_file);
								$arrData = ['app_id', 'upid', 'filepath', 'fileExten', 'fileSize', 't_date', 't_time', 'ipaddress'];
								$sRequest = ['app_id'=>$appid, 'upid'=>$uKey, 'filepath'=>$reData['fileNameToStore'], 'fileExten'=>$reData['fileExtension'], 'fileSize'=>$reData['fileSize'], 't_date'=>Carbon::now()->toDateString(), 't_time'=>Carbon::now()->toTimeString(), 'ipaddress'=>request()->ip()];
								$arrCheck = []; $makeHash = []; $haveAdd = ['evaluation'=>NULL]; $fMail = [];
								$validate = [['app_id', 'upid', 'filepath'], ['app_id'=>'No application selected.', 'upid'=>'No upload.', 'filepath'=>'No path selected.']];
								$stat = ((count($arrFind) > 0) ? FunctionsClientController::fUpdData($sRequest, $arrData, $arrCheck, $makeHash, $haveAdd, $fMail, $validate, 'app_upload', [['app_id', $appid], ['upid', $uKey]]) : FunctionsClientController::fInsData($sRequest, $arrData, $arrCheck, $makeHash, $haveAdd, $fMail, $validate, 'app_upload'));
								if(! in_array($stat, $msgRet)) {
									array_push($msgRet, $stat);
								}
							}
						}
					}
				} else {
					return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No file selected']);
				}
				// foreach(FunctionsClientController::getReqUploads($hfser, $appid) AS $each) {
				// 	if(! isset($each->filepath)) {
				// 		array_push($isAllUpload, $each->upid);
				// 	} else {
				// 		if(! in_array($each->evaluation, $isApproved)) {
				// 			if(in_array($curForm[0]->canapply, [1])) {
				// 				array_push($isAllUpload, $each->upid);
				// 			}
				// 		}
				// 	}
				// }
				// if(count($isAllUpload) < 1) {
				// 	DB::table('appform')->where([['appid', $appid]])->update(['documentSent'=>Carbon::now()->toDateString()]);
				// }

				}
				$submitted = true;

			}
			$facilities = DB::table('x08_ft')->where('appid',$appid)->select('facid')->get();
			// dd($facilities);
			foreach ($facilities as $key => $value) {
				if(!in_array(trim($value->facid), $arrFaci)){
					array_push($arrFaci, trim($value->facid));
				}
			}
			$reqChecklist = DB::table('x08_ft')->join('facilitytypupload','x08_ft.facid','facilitytypupload.facid')->where([['facilitytypupload.hfser_id',$hfser],['x08_ft.appid',$appid]])->get();
			$req = FunctionsClientController::getReqUploads($hfser, $appid, $office);
			$arrRet = [
				'userInf'=>FunctionsClientController::getUserDetails(),
				'appDet'=>$req,
				'appform'=>$curForm[0],
				'cToken'=>FunctionsClientController::getToken(),
				'orderOfPayment'=>FunctionsClientController::getChgfilCharges($appid),
				'checklist' => $reqChecklist,
				// 'canSubmit' => (array_search(null,array_column($req, 'evaluation')) <= 0 ? true : false)
				'canSubmit' => true,
				'prompt' => ($submitted ? ($office == 'hfsrb' ? DB::table('appform')->join('chgfil','chgfil.appform_id','appform.appid')->where([['chgfil.userChoosen',1],['chgfil.appform_id',$appid]])->orWhere([['chgfil.userChoosen',1],['chgfil.appform_id',$appid],['appform.isPayEval',1]])->doesntExist() : false) : false),
				'appid' => $appid,
				'isReadyToInspect' => DB::table('appform')->where([['appid',$appid],['isReadyForInspec',1]])->exists(),
				'office' => $office
			];
			// dd($arrRet);
			return view('client1.applyattach', $arrRet);
		// } catch(Exception $e) {
		// 	return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Add new Application. Contact the admin']);
		// }
	}

	//find me
	public function __applyApp(Request $request, $hfser, $appid, $hideExtensions = NULL, $aptid = NULL) {
		try {
			$user_data = session()->get('uData');
			$hfLocs = 
				[
					'client1/apply/app/LTO/'.$appid, 
					'client1/apply/app/LTO/'.$appid.'/hfsrb', 
					'client1/apply/app/LTO/'.$appid.'/fda'
				];
			if(isset($hideExtensions)) {
				$hfLocs = [
					'client1/apply/employeeOverride/app/LTO/'.$appid, 
					'client1/apply/employeeOverride/app/LTO/'.$appid.'/hfsrb', 
					'client1/apply/employeeOverride/app/LTO/'.$appid.'/fda'
				];
			}
			if(! isset($hideExtensions)) {
				$cSes = FunctionsClientController::checkSession(true);
				if(count($cSes) > 0) {
					return redirect($cSes[0])->with($cSes[1], $cSes[2]);
				}
			}
			$appGet = FunctionsClientController::getUserDetailsByAppform($appid, $hideExtensions);
			// dd($appGet);
			if(count($appGet) < 1) {
				return redirect('client1/apply')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'No application selected.']);
			}
			// dd($hfer);
			if($hfser != $appGet[0]->hfser_id) {
				return redirect('client1/apply/app/'.$appGet[0]->hfser_id.'/'.$appid.'');
			}
			$percentage = FunctionsClientController::getAssessmentTotalPercentage($appid, ''.$appGet[0]->uid.'_'.$appGet[0]->hfser_id.'_'.$appGet[0]->aptid.'');
			if(intval($percentage[0]) < 100) {
				// return redirect('client1/apply/assessmentReady/'.$appid.'/'.$appGet[0]->hfser_id.'')->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Proceed to assessment.']);
			}
			$arrRet = []; 
			$locRet = ""; 
			$hfaci_sql = "SELECT * FROM hfaci_grp WHERE hgpid IN (SELECT hgpid FROM `facl_grp` WHERE hfser_id = '$hfser')"; 
			$arrCon = [6];
			$apptype = $appGet[0]->hfser_id;
			// unset($appGet[0]->areacode);  //temporary fix
			switch($hfser) {
				case 'CON':
					session()->forget('ambcharge');
					$arrRet = [
						'userInf'=>FunctionsClientController::getUserDetails(),
						'serv_cap'=>DB::table('facilitytyp')->where([['servtype_id',1],['forSpecialty',0]])->whereIn('hgpid', $arrCon)->get(),
						'ownership'=>DB::table('ownership')->get(),
						'class'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NULL OR isSub = '')")),
						'subclass'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NOT NULL OR isSub != '')")),
						'function'=>DB::table('funcapf')->get(),
						'facmode'=>DB::table('facmode')->get(),
						'apptype'=>DB::table('apptype')->get(),
						'fAddress'=>$appGet,
						'servfac'=>json_encode(FunctionsClientController::getServFaclDetails($appid)),
						'condet'=>FunctionsClientController::getCONDetails($appid),
						'cToken'=>FunctionsClientController::getToken(),
						'hfer' => $apptype,
						'hideExtensions'=>$hideExtensions,
						'aptid'=>$aptid,
						'arrCon'=>json_encode($arrCon)
					]; 
					// unset($arrRet['fAddress'][0]->areacode);
					// dd($arrRet);
					$locRet = "client1.apply.CON1.conapp";
					break;
				case 'PTC':
					session()->forget('ambcharge');
					$arrRet = [
						'userInf'=>FunctionsClientController::getUserDetails(),
						'hfaci_serv_type'=>DB::select($hfaci_sql),
						'serv_cap'=>json_encode(DB::table('facilitytyp')->where([['servtype_id',1]/*,['forSpecialty',0]*/])->get()),
						'ownership'=>DB::table('ownership')->get(),
						'class'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NULL OR isSub = '')")),
						'subclass'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NOT NULL OR isSub != '')")),
						'function'=>DB::table('funcapf')->get(),
						'facmode'=>DB::table('facmode')->get(),
						'fAddress'=>$appGet,
						'servfac'=>json_encode(FunctionsClientController::getServFaclDetails($appid)),
						'ptcdet'=>json_encode(FunctionsClientController::getPTCDetails($appid)),
						'cToken'=>FunctionsClientController::getToken(),
						'hfer' => $apptype,
						'hideExtensions'=>$hideExtensions,
						'aptid'=>$aptid,
					]; 
					// dd($arrRet);
					$locRet = "client1.apply.PTC1.ptcapp";
					break;
				case 'LTO':
					$proceesedAmb = [];
					foreach (AjaxController::getForAmbulanceList(false,'forAmbulance.hgpid') as $key => $value) {
						array_push($proceesedAmb, $value->hgpid);
					}

					// 5-12-2021
					$hfser_id = 'LTO';
					$faclArr = [];
							$facl_grp = FACLGroup::where('hfser_id', $hfser_id)->select('hgpid')->get();
							foreach ($facl_grp as $f) {
								array_push($faclArr, $f->hgpid);
							}

					$arrRet = [
						'hfser' =>  $hfser_id,
						'user'=> $user_data,
						'regions' => Regions::orderBy('sort')->get(),
						'hfaci_service_type'    => HFACIGroup::whereIn('hgpid', $faclArr)->get(),
						'appFacName'            => FunctionsClientController::getDistinctByFacilityName(),

						'userInf'=>FunctionsClientController::getUserDetails(),
						'hfaci_serv_type'=>DB::select($hfaci_sql),
						'serv_cap'=>json_encode(DB::table('facilitytyp')->where('servtype_id',1)->get()),
						'apptype'=>DB::table('apptype')->get(),
						'ownership'=>DB::table('ownership')->get(),
						'class'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NULL OR isSub = '')")),
						'subclass'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NOT NULL OR isSub != '')")),
						'function'=>DB::table('funcapf')->get(),
						'facmode'=>DB::table('facmode')->get(),
						'fAddress'=>$appGet,
						'servfac'=>json_encode(FunctionsClientController::getServFaclDetails($appid)),
						'ptcdet'=>json_encode(FunctionsClientController::getPTCDetails($appid)),
						'cToken'=>FunctionsClientController::getToken(),
						'addresses'=>$hfLocs,
						'hfer' => $apptype,
						'hideExtensions'=>$hideExtensions,
						'ambcharges'=>DB::table('chg_app')->whereIn('chgapp_id', ['284', '472'])->get(),
						'aptid'=>$aptid,
						'group' => json_encode(DB::table('facilitytyp')->where('servtype_id','>',1)->whereNotNull('grphrz_name')->get()),
						'forAmbulance' => json_encode($proceesedAmb),
					];
					 $locRet = "dashboard.client.license-to-operate";
					//  $locRet = "client1.apply.LTO1.ltoapp";
					break;
				case 'COR':
						session()->forget('ambcharge');
						$hfser_id = 'COR';
						$faclArr = [];
							$facl_grp = FACLGroup::where('hfser_id', $hfser_id)->select('hgpid')->get();
							foreach ($facl_grp as $f) {
								array_push($faclArr, $f->hgpid);
							}
						$arrRet = [
							'hfser' =>  "COR",
							'user'=> $user_data,
							'appFacName'            => FunctionsClientController::getDistinctByFacilityName(),
							'userInf'=>FunctionsClientController::getUserDetails(),
							'hfaci_serv_type'=>DB::select($hfaci_sql),
							'hfaci_service_type'    => HFACIGroup::whereIn('hgpid', $faclArr)->get(),
							'serv_cap'=>json_encode(DB::table('facilitytyp')->where([['servtype_id',1],['forSpecialty',0]])->get()),
							'ownership'=>DB::table('ownership')->get(),
							'class'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NULL OR isSub = '')")),
							'subclass'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NOT NULL OR isSub != '')")),
							'function'=>DB::table('funcapf')->get(),
							'facmode'=>DB::table('facmode')->get(),
							'apptype'=>DB::table('apptype')->get(),
							'fAddress'=>$appGet,
							'servfac'=>json_encode(FunctionsClientController::getServFaclDetails($appid)),
							'cToken'=>FunctionsClientController::getToken(),
							'hfer' => $apptype,
							'hideExtensions'=>$hideExtensions,
							'aptid'=>$aptid
						]; 
						$locRet = "dashboard.client.certificate-of-registration";
						// $locRet = "client1.apply.default1.defaultapp";
						break;

				case 'COA':
					session()->forget('ambcharge');
					$hfser_id = 'COA';
						$faclArr = [];
							$facl_grp = FACLGroup::where('hfser_id', $hfser_id)->select('hgpid')->get();
							foreach ($facl_grp as $f) {
								array_push($faclArr, $f->hgpid);
							}
					$arrRet = [
						'appFacName'            => FunctionsClientController::getDistinctByFacilityName(),
						'hfser' =>  "COA",
						'user'=> $user_data,
						'hfaci_service_type'    => HFACIGroup::whereIn('hgpid', $faclArr)->get(),
						'userInf'=>FunctionsClientController::getUserDetails(),
						'hfaci_serv_type'=>DB::select($hfaci_sql),
						'serv_cap'=>json_encode(DB::table('facilitytyp')->where([['servtype_id',1],['forSpecialty',0]])->get()),
						'ownership'=>DB::table('ownership')->get(),
						'class'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NULL OR isSub = '')")),
						'subclass'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NOT NULL OR isSub != '')")),
						'function'=>DB::table('funcapf')->get(),
						'facmode'=>DB::table('facmode')->get(),
						'apptype'=>DB::table('apptype')->get(),
						'fAddress'=>$appGet,
						'servfac'=>json_encode(FunctionsClientController::getServFaclDetails($appid)),
						'cToken'=>FunctionsClientController::getToken(),
						'hfer' => $apptype,
						'hideExtensions'=>$hideExtensions,
						'aptid'=>$aptid
					]; 
					
					$locRet = "dashboard.client.certificate-of-accreditation";
					// $locRet = "client1.apply.COA1.coaapp";
					break;
				
				default:
					session()->forget('ambcharge');
					$arrRet = [
						'userInf'=>FunctionsClientController::getUserDetails(),
						'hfaci_serv_type'=>DB::select($hfaci_sql),
						'serv_cap'=>json_encode(DB::table('facilitytyp')->where([['servtype_id',1],['forSpecialty',0]])->get()),
						'ownership'=>DB::table('ownership')->get(),
						'class'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NULL OR isSub = '')")),
						'subclass'=>json_encode(DB::select("SELECT * FROM class WHERE (isSub IS NOT NULL OR isSub != '')")),
						'function'=>DB::table('funcapf')->get(),
						'facmode'=>DB::table('facmode')->get(),
						'apptype'=>DB::table('apptype')->get(),
						'fAddress'=>$appGet,
						'servfac'=>json_encode(FunctionsClientController::getServFaclDetails($appid)),
						'cToken'=>FunctionsClientController::getToken(),
						'hfer' => $apptype,
						'hideExtensions'=>$hideExtensions,
						'aptid'=>$aptid
					]; 
					$locRet = "client1.apply.default1.defaultapp";
					break;
					// unset($arrRet['fAddress'][0]->areacode);
			}
			return view($locRet, $arrRet);
		} catch(Exception $e) {
			return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on page Add new Application. Contact the admin']);
		}
	}
	public function __applyApp_n(Request $request, $hfser, $appid, $aptid) {
		$col1 = "up_appid, hfser_id, facilityname, owner, rgnid, provid, cmid, brgyid, contact, email, uid, street_name, street_number, faxNumber, zipcode, landline, mailingAddress, ownerMobile, ownerLandline, ownerEmail, aptid"; $col2 = "'$appid' AS up_appid, hfser_id, facilityname, owner, rgnid, provid, cmid, brgyid, contact, email, uid, street_name, street_number, faxNumber, zipcode, landline, mailingAddress, ownerMobile, ownerLandline, ownerEmail, '$aptid' AS aptid"; $tbl = "appform"; $where = "appid = '$appid'"; $NOT_ACCEPTED = ["CON", "PTC"];
		$appid1 = FunctionsClientController::getUserDetailsByAppform($appid);
		$cSes = FunctionsClientController::checkSession(true);
		if(count($cSes) > 0) {
			return redirect($cSes[0])->with($cSes[1], $cSes[2]);
		}
		if(count($appid1) > 0) { if(! in_array($appid1[0]->hfser_id, $NOT_ACCEPTED)) { if($appid1[0]->aptid != $aptid) { // if(isset($appid1[0]->aptid)) {
			$gData = "SELECT appid FROM appform WHERE appid = '$appid' AND aptid = '$aptid' AND (up_appid IS NOT NULL AND up_appid != '')"; // (COALESCE(hfser_id, ''), facilityname, owner, rgnid, provid, cmid, brgyid, contact, email, uid, COALESCE(street_name, ''), street_number, COALESCE(faxNumber, ''), zipcode, COALESCE(landline, ''), COALESCE(mailingAddress, ''), ownerMobile, ownerLandline, ownerEmail) IN (SELECT COALESCE(hfser_id, ''), facilityname, owner, rgnid, provid, cmid, brgyid, contact, email, uid, COALESCE(street_name, ''), street_number, COALESCE(faxNumber, ''), zipcode, COALESCE(landline, ''), COALESCE(mailingAddress, ''), ownerMobile, ownerLandline, ownerEmail FROM appform WHERE appid = '$appid')
			$gDataS = DB::select(DB::raw($gData));
			if(count($gDataS) > 0) {
				$nAppid = $gDataS[0]->appid; $rUrl = "client1/apply/app/$hfser/$nAppid/$aptid";
				return redirect($rUrl);
			} else {
				if(FunctionsClientController::fInsSel($col1, $tbl, $col2, $tbl, $where)) {
					$gDataS = DB::select(DB::raw("SELECT appid FROM appform WHERE up_appid = '$appid' AND aptid = '$aptid' AND (up_appid IS NOT NULL AND up_appid != '')")); $nAppid = $gDataS[0]->appid; $rUrl = "client1/apply/app/$hfser/$nAppid/$aptid";
					return redirect($rUrl);
				}
			}
		} return self::__applyApp($request, $hfser, $appid, NULL, $aptid); }
			return redirect('client1/home')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'Renewal not applicable to this application.']);
		}
		return redirect('client1/home')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'No application selected.']);
	}
	public function __updApp(Request $request, $appid) {
		$cSes = FunctionsClientController::checkSession(true);
		if(count($cSes) > 0) {
			return redirect($cSes[0])->with($cSes[1], $cSes[2]);
		}
		$errMsg = "No continuation for selected application type.";
		$sql = DB::select(DB::raw("SELECT hfaci_serv_type.* FROM appform INNER JOIN hfaci_serv_type ON appform.hfser_id = hfaci_serv_type.hfser_id WHERE appid = '$appid'"));
		if(count($sql) > 0) {
			$isSub = FunctionsClientController::retColArr($sql[0], 'isSub'); 
			$col1 = "up_appid, hfser_id, facilityname, owner, rgnid, provid, cmid, brgyid, contact, email, uid, street_name, street_number, faxNumber, zipcode, landline, mailingAddress, ownerMobile, ownerLandline, ownerEmail, aptid"; $col2 = "'$appid' AS up_appid, '$isSub' AS hfser_id, facilityname, owner, rgnid, provid, cmid, brgyid, contact, email, uid, street_name, street_number, faxNumber, zipcode, landline, mailingAddress, ownerMobile, ownerLandline, ownerEmail, (CASE WHEN (aptid IS NULL OR aptid = '') THEN NULL ELSE 'IN' END) AS aptid"; $tbl = "appform"; $where = "appid = '$appid'";
			if(isset($isSub)) {
				$appform = DB::select(DB::raw("SELECT * FROM licensed WHERE appid = '$appid'"));
				if(count($appform) > 0) {
					$upApp = DB::select("SELECT * FROM appform WHERE up_appid = '$appid' AND hfser_id = '$isSub'");
					if(count($upApp) > 0) {
						return redirect('client1/apply/edit/'.$upApp[0]->appid.'');
					} else {
						$insThis = FunctionsClientController::fInsSel($col1, $tbl, $col2, $tbl, $where);
						if(in_array($insThis, [true, 1])) {
							return redirect('client1/apply/app/updApp/'.$appid.'');
						}
					}
				} else {
					$errMsg = "Application selected is not yet licensed or doesn't exist.";
				}
			}
		}
		return redirect('client1/home')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>$errMsg]);
	}
	public function __change_request(Request $request, $appid) {
		// try {
			$cSes = FunctionsClientController::checkSession(true);
			$appform = FunctionsClientController::getUserDetailsByAppform($appid);
			$up_appid = DB::table('appform')->where([['up_appid', $appid], ['aptid', 'IC']])->first();
			$allConnected = FunctionsClientController::getTablesConnectedAppform($appid);
			$col1 = "up_appid, uid, facilityname, serv_capabilities, owner, email, mailingAddress, contact, rgnid, provid, cmid, brgyid, landline, street_name, street_number, faxnumber, zipcode, ownerMobile, ownerLandline, ownerEmail, hfser_id, facid, ocid, ocdesc, aptid, classid, classdesc, subClassid, subClassdesc, funcid, facmode, noofbed, conCode, noofsatellite, clab, draft, appid_payment, t_date, t_time, ipaddress, assignedRgn, assignedRgnTime, assignedRgnDate, assignedRgnIP, assignedRgnBy, assignedLO, assignedLOTime, assignedLoDate, assignedLOIP, assignedLOBy, status, cap_inv, lot_area, typeamb, plate_number, noofamb"; $col2 = "'$appid' AS up_appid, uid, facilityname, serv_capabilities, owner, email, mailingAddress, contact, rgnid, provid, cmid, brgyid, landline, street_name, street_number, faxnumber, zipcode, ownerMobile, ownerLandline, ownerEmail, hfser_id, facid, ocid, ocdesc, 'IC' AS aptid, classid, classdesc, subClassid, subClassdesc, funcid, facmode, noofbed, conCode, noofsatellite, clab, draft, appid_payment, t_date, t_time, ipaddress, assignedRgn, assignedRgnTime, assignedRgnDate, assignedRgnIP, assignedRgnBy, assignedLO, assignedLOTime, assignedLoDate, assignedLOIP, assignedLOBy, status, cap_inv, lot_area, typeamb, plate_number, noofamb"; $tbl = "appform"; $where = "appid = '$appid'";
			// dd($allConnected);
			if(count($cSes) > 0) {
				return redirect($cSes[0])->with($cSes[1], $cSes[2]);
			}
			if(count($appform) < 1) {
				return redirect('client1/home')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'No application selected.']);
			}
			if(! isset($up_appid)) {
				$insAppform = FunctionsClientController::fInsSel($col1, $tbl, $col2, $tbl, $where);
				if(in_array($insAppform, [true, 1])) {
					$nUp_appid = DB::table('appform')->where([['up_appid', $appid]])->first();
					if(isset($nUp_appid)) {
						$nAppid = $nUp_appid->appid;
						// foreach($allConnected AS $key => $value) {
						// 	if(! in_array($key, [$tbl, 'licensed'])) {
						// 		// DB::table($key)->where([[$value, $nAppid]])->delete();
						// 		$nCol = FunctionsClientController::arrayString_agg("COLUMN_NAME", FunctionsClientController::returnColumns("COLUMNS", "TABLE_NAME = '$key' AND COLUMN_NAME NOT IN ('$value') AND COLUMN_KEY != 'PRI'"));
						// 		$nCol1 = ((! empty($nCol)) ? "$value, $nCol" : "$value"); $nCol2 = ((! empty($nCol)) ? "'$nAppid' AS $value, $nCol" : "$value");
						// 		$insInTbl = FunctionsClientController::fInsSel($nCol1, $key, $nCol2, $key, "$value = '$appid'");
						// 		if(! in_array($insAppform, [true, 1])) {
						// 			return redirect('client1/home')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>$insAppform]);
						// 		}
						// 	}
						// }
						return redirect('client1/apply/edit/'.$nAppid.'');
					} else {
						$insAppform = "Error on saving";
					}
				}
				return redirect('client1/home')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>$insAppform]);
			}
			return redirect('client1/apply/edit/'.$up_appid->appid.'');
		// } catch(Exception $e) {

		// }
	}
	public function __payment(Request $request, $token = "", $chgapp_id = "", $appid = "") {
		try {
			$cSes = FunctionsClientController::checkSession(true);
			if(count($cSes) > 0) {
				return redirect($cSes[0])->with($cSes[1], $cSes[2]);
			}
			if((! empty($token)) && (! empty($chgapp_id))) {
				if($token == FunctionsClientController::getToken()) {
					$appform = FunctionsClientController::getUserDetailsByAppform($appid);
					if(count($appform) > 0) {
						$selHfser = ['LTO'];
						$retArr = FunctionsClientController::insPayment($request->all(), request()->ip(), $chgapp_id, $appid, FunctionsClientController::getTotalAmount('amount', FunctionsClientController::getChgfilCharges($appid)));
						$nLoc = ((in_array($appform[0]->hfser_id, $selHfser)) ? 'client1/apply/app/'.$appform[0]->hfser_id.'/'.$appid.'/hfsrb' : 'client1/apply/attachment/'.$appform[0]->hfser_id.'/'.$appid.'');
						return redirect($nLoc)->with($retArr[0], $retArr[1]);
					} else {
						return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No application selected.']);
					}
				} else {
					return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Incorrect token.']);
				}
			}
			return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No token and/or payment type set on payment.']);
		} catch(Exception $e) {
			return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on Processing Payment. Contact the admin.']);
		}
	}
	public function __dPayment(Request $request, $token = "", $appid = "") {
		try {
			if($request->isMethod('get')){
				$appform = DB::table('appform')->join('chgfil','chgfil.appform_id','appform.appid')->where([['chgfil.userChoosen',1],['chgfil.appform_id',$appid]])->orWhere([['chgfil.userChoosen',1],['chgfil.appform_id',$appid],['appform.isPayEval',1]])->exists();
				// dd($appform);
				if($appform){
					return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'You cannot choose a payment at this moment or you have already selected a payment method.']);
				}
				$cSes = FunctionsClientController::checkSession(true);
				if(count($cSes) > 0) {
					return redirect($cSes[0])->with($cSes[1], $cSes[2]);
				}
				$retArr = [];
				if($token == FunctionsClientController::getToken()) {
					// $payment = FunctionsClientController::getChgfilCharges($appid);
					$payment = FunctionsClientController::getChgfilTransactions($appid,'C');
					if(isset($payment)) { if(isset($payment)) {
						$arrRet = [
							'userInf'=>FunctionsClientController::getUserDetails(),
							'npayment'=>$payment,
							'cToken'=>FunctionsClientController::getToken(),
							'appid'=>$appid
						];
						return view('client1.payment', $arrRet);
					} }
					return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No payment(s) and/or charges selected.']);
				}
				return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Incorrect token.']);

			} else {
				if($request->has('mPay')){
					$filename = null;
					if($request->hasFile('attFile')){
						$filename = FunctionsClientController::uploadFile($request->attFile)['fileNameToStore'];
					}

					$test = DB::table('chgfil')->insert(['appform_id' => $appid,'paymentMode'=> $request->mPay, 'attachedFile'=>$filename, 'draweeBank' => $request->drawee, 'number' => $request->number, 'userChoosen' => 1, 't_date' => Date('Y-m-d',strtotime('now')) , 't_time' => Date('H:i:s',strtotime('now'))]);
					if($test){
						return redirect('client1/apply')->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Successfully submitted application form and updated payment information.']);
					}
				}
				return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'We are sorry but we have encoutered a problem. Contact the admin.']);
			}

		} catch(Exception $e) {
			dd($e);
			return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on Opening payment module. Contact the admin.']);
		}
	}
	public function __byHfser(Request $request, $hfser, $appid) {
		try {
			if(DB::table('appform')->join('licensed','licensed.appid','appform.appid')->where([['appform.appid',$appid],['appform.isApprove',1]])->doesntExist()){
				return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Application Does not Exist']);
			}
			$x08_ft = 0;
			$arrayFaci = $arrayserv = array();
			$retTable = FunctionsClientController::getUserDetailsByAppform($appid);
			switch ($retTable[0]->hfser_id) {
				case 'PTC':
					$otherDetails = DB::table('hferc_evaluation')->where([['appid',$appid],['HFERC_eval',1]])->first();
					break;

				case 'LTO':
					$otherDetails = [DB::table('assessmentrecommendation')->where([['appid',$appid],['choice','issuance']])->first(), DB::table('x08_ft')->where('appid',$appid)->whereIn('facid',['H','H2','H3'])->exists()];
					break;

				case 'CON':
					$otherDetails = DB::table('con_evaluate')->where('appid',$appid)->first();
					break;
				
				default:
					$otherDetails = null;
					break;
			}
			$facilityTypeId = "No Facility Type"; 
			$serviceId = "No Service";
			if($hfser != $retTable[0]->hfser_id) {
				return redirect('client1/apply/app/'.$retTable[0]->hfser_id.'/'.$appid.'');
			}
			$x08_ft = DB::table('x08_ft')->where('appid',$appid)->join('facilitytyp','facilitytyp.facid','x08_ft.facid')->where('facilitytyp.servtype_id',2)->get();
			if(count($x08_ft) > 0){
				foreach($x08_ft as $table){
					if(!in_array($table->facname, $arrayFaci)){
						array_push($arrayFaci, $table->facname);
					}
				}
			}
			$sData = FunctionsClientController::getServFaclDetails($appid);
			if(count($retTable) > 0) {
				if($retTable[0]->canapply != 2) {
					return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Application not yet applied.']);
				}
			} else {
				return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No application selected']);
			}
			if(count($sData[3])) {
				$impArr = [];
				foreach($sData[3] AS $facilityTypeRow) {
					array_push($impArr, $facilityTypeRow->hgpdesc);
				}
				$facilityTypeId = implode(', ', $impArr);
			}
			if(count($sData[2])) {
				$impArr1 = [];
				foreach($sData[2] AS $serviceTypeRow) {
					array_push($impArr1, $serviceTypeRow->facname);
				}
				$serviceId = implode(', ', $impArr1);
			}
			// $primaryApp = DB::table('x08_ft')->where('appid',$appid)->join('facilitytyp','facilitytyp.facid','x08_ft.facid')->where('facilitytyp.servtype_id',1)->first();
			// $rgn = $retTable[0]->rgnid;
			// if(isset($primaryApp)){
			// 	if($primaryApp->assignrgn == 'hfsrb' && $retTable[0]->hfep_funded != 1){
			// 		$rgn = 'HFSRB';
			// 	}
			// }
			$rgn = FunctionsClientController::isFacilityFor($appid);
			$hfser .= '1';
			$arrData = [
				'userInf'=>FunctionsClientController::getUserDetails(),
				'director'=>DB::table('branch')->where('regionid',$rgn)->first(),
				'm99'=>DB::table('m99')->first(),
				'retTable'=>$retTable, 
				'facilityTypeId'=>$facilityTypeId, 
				'serviceId'=>$serviceId,
				'addons' => $arrayFaci,
				'services' => AjaxController::getHighestApplicationFromX08FT($appid),
				'otherDetails' => $otherDetails
			];
			return view('client1.certificates.'.$hfser, $arrData);
		} catch(Exception $e) {
			dd($e);
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on Issuance. Contact the admin.']);
		}
	}
	public function viewCertExt(Request $request, $appid) {
		try {
			if(DB::table('appform')->join('licensed','licensed.appid','appform.appid')->where([['appform.appid',$appid],['appform.isApprove',1]])->doesntExist()){
				return 'Application Does not Exist';
			}
			$uid = AjaxController::getUidFrom($appid);
			$arrayFaci = $arrayserv = array();
			$retTable = FunctionsClientController::getUserDetailsByAppform($appid,$uid);
			$x08_ft = DB::table('x08_ft')->where('appid',$appid)->join('facilitytyp','facilitytyp.facid','x08_ft.facid')->where('facilitytyp.servtype_id',1)->get();
			// dd($x08_ft);
			foreach($x08_ft as $table){
				if($table->servtype_id == 1){
					if(!in_array($table->facname, $arrayFaci)){
						array_push($arrayFaci, $table->facname);
					}
				}
			}
			switch ($retTable[0]->hfser_id) {
				case 'PTC':
					$otherDetails = DB::table('hferc_evaluation')->where([['appid',$appid],['HFERC_eval',1]])->first();
					break;

				case 'LTO':
					$otherDetails = DB::table('assessmentrecommendation')->where([['appid',$appid],['choice','issuance']])->first();
					break;

				case 'CON':
					$otherDetails = DB::table('con_evaluate')->where('appid',$appid)->first();
					break;
				
				default:
					$otherDetails = null;
					break;
			}
			$arrData = [
				'retTable' => $retTable,
				'servCap' => $arrayFaci,
				'otherDetails' => $otherDetails
			];
			// dd($arrData['retTable'][0]->office);
			return view('client1.certificates.certView', $arrData);
		} catch(Exception $e) {
			dd($e);
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on Issuance. Contact the admin.']);
		}
	}
	public function viewCert($appid){
		if(session()->has('uData')){
			$hfer = AjaxController::gethfer_id($appid);
			return redirect('client1/certificates/'.$hfer.'/'.$appid);
		}
	}
	public function __pgPayment(Request $request, $token, $appid) {
		if(FunctionsClientController::isUserApplication($appid)){
			try {
				$cSes = FunctionsClientController::checkSession(true);
				if(count($cSes) > 0) {
					return redirect($cSes[0])->with($cSes[1], $cSes[2]);
				}
				if($token == FunctionsClientController::getToken()) {
					$amount = 0; 
					foreach(FunctionsClientController::getChgfilTransactions($appid, 'C') AS $each) { 
						if($each->m04ID_FK == null && $each->recievedBy == null){
							$amount += $each->amount; 
						}
					}
					$arrRet = [
						'userInf'=>FunctionsClientController::getUserDetails(),
						'appDet'=>FunctionsClientController::getUserDetailsByAppformWithTransactions($appid),
						'totalWord'=>[$amount, FunctionsClientController::moneyToString($amount)],
						'neededData' => [FunctionsClientController::getUserDetailsByAppform($appid)[0],FunctionsClientController::getServFaclDetails($appid)],
						'Notfinal' => DB::table('appform')->where([['isPayEval',1],['appid',$appid]])->exists(),
						'isDisplayABC' => DB::table('x08_ft')->where('appid',$appid)->whereIn('facid',['H','H2','H3'])->exists()
					];
					return view('client1.payment.defaultpayment', $arrRet);
				}
				return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Incorrect token.']);
			} catch(Exception $e) {
				return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on Order of Payment Module. Contact the admin.']);
			}

		} else {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Something went wrong. Please try again later.']);
		}
	}
	public function __fdaPayment(Request $request, $token, $appid) {
		if(FunctionsClientController::isExistOnAppform($appid) !== true){
			return redirect('client1/home');
		}
		try {
			if($token == FunctionsClientController::getToken()){
				$data = [
					'fda' => FunctionsClientController::getDetailsFDA($appid)
				];
				if(is_array($data['fda'])){
					return view('client1.payment.fdapayment',$data);	
				} else {
					return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>FunctionsClientController::getDetailsFDA($appid)]);
				}
			}
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Incorrect token.']);
		}
		catch (Exception $e) {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on Order of Payment Module. Contact the admin.']);
		}
	}

	public function fdacert(Request $request, $appid, $requestOfClient = null) {
		if(FunctionsClientController::isExistOnAppform($appid) !== true || !FunctionsClientController::existOnDB('fdacert',[['appid',$appid],['department',(AjaxController::isRequestForFDA($requestOfClient) == 'machines' ? 'cdrrhr' : 'cdrr')]]) || !isset($requestOfClient)){
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No records Found.']);
		}
		try {
			$selection = (AjaxController::isRequestForFDA($requestOfClient) == 'machines' ? 'cdrrhr' : 'cdrr');
			
			$data = DB::table('fdacert')->where([['fdacert.appid',$appid],['fdacert.department',$selection]])->join('appform','appform.appid','fdacert.appid')->leftjoin('x08','x08.uid','appform.uid')->leftjoin('apptype','appform.aptid','apptype.aptid')->select('appform.*','fdacert.*','x08.authorizedsignature','apptype.aptdesc')->first();

			if($data){
				if(AjaxController::isRequestForFDA($requestOfClient) != 'machines'){
					$view = ($data->certtype == 'COC' ? 'client1.FDA.coc' : 'client1.FDA.rl');
				} else {
					$view = ($data->certtype == 'COC' ? 'client1.FDA.cdrrhrCOC' : 'client1.FDA.cdrrhrRL');
					$machineData = DB::table('cdrrhrxraylist')->where('appid',$appid)->get();
				}
				$arrRet = [
					'appform' => DB::table('appform')->where('appid',$appid)->first(),
					'data' => $data,
					'machineData' => isset($machineData) ? $machineData : null,
					'otherDetails' => [DB::table('hfsrbannexa')->where([['appid',$appid],['isMainRadio',1]])->first(),DB::table('hfsrbannexa')->where([['appid',$appid],['isMainRadioPharma',1]])->first(),DB::table('hfsrbannexa')->where([['appid',$appid],['ismainpo',1]])->first()]
				];
				return view($view,$arrRet);
			}
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No records Found.']);
		}
		catch (Exception $e) {
			dd($e);
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on Order of Payment Module. Contact the admin.']);
		}
	}

	public function __fdaPaymentCDRR(Request $request, $token, $appid) {
		if(FunctionsClientController::isExistOnAppform($appid) !== true){
			return redirect('client1/home');
		}
		try {
			if($token == FunctionsClientController::getToken()){
				$data = [
					'fda' => FunctionsClientController::getDetailsFDACDRR($appid)
				];
				if(is_array($data['fda'])){
					return view('client1.payment.fdapaymentCDRR',$data);	
				} else {
					return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>FunctionsClientController::getDetailsFDACDRR($appid)]);
				}
			}
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Incorrect token.']);
		}
		catch (Exception $e) {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on Order of Payment Module. Contact the admin.']);
		}
	}
	public function __applyhfsrb(Request $request, $hfser, $appid, $hideExtensions = NULL) {
		try {
			if($request->isMethod('get')){
				$hfLocs = ['client1/apply/app/LTO/'.$appid, 'client1/apply/app/LTO/'.$appid.'/hfsrb', 'client1/apply/app/LTO/'.$appid.'/fda', 'client1/printPayment/'.FunctionsClientController::getToken().'/'.$appid];
				if(isset($hideExtensions)) {
					$hfLocs = ['client1/apply/employeeOverride/app/LTO/'.$appid, 'client1/apply/employeeOverride/app/LTO/'.$appid.'/hfsrb', 'client1/apply/employeeOverride/app/LTO/'.$appid.'/fda', 'client1/printPayment/'.FunctionsClientController::getToken().'/'.$appid];
				}
				if(! isset($hideExtensions)) {
					$cSes = FunctionsClientController::checkSession(true);
					if(count($cSes) > 0) {
						return redirect($cSes[0])->with($cSes[1], $cSes[2]);
					}
				}
				$appDet = FunctionsClientController::getUserDetailsByAppform($appid, $hideExtensions);
				if(count($appDet) < 1) {
					return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No application selected.']);
				}
				$arrRet = [
					'userInf'=>FunctionsClientController::getUserDetails(),
					'addresses'=>$hfLocs,
					'fAddress'=>$appDet,
					'hideExtensions'=>$hideExtensions,
					'appid'=>$appDet[0]->appid,
					'appform'=>$appDet[0],
					'data' =>AjaxController::getAllRequirementsLTO($appid)
				];
				// dd($arrRet);
				return view('client1.apply.LTO1.ltohfsrb', $arrRet);
			} else {
				if(isset($request->readyNow)){
					$ret = DB::table('appform')->where('appid',$appid)->update(['isReadyForInspec' => 1]);
					if($ret){
						return 'succ';
					}
					return 'fail';
				}
			}
		} catch(Exception $e) {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on HFSRB Module. Contact the admin.']);
		}
	}
	public function __applyfda(Request $request, $hfser, $appid, $hideExtensions = NULL) {
		try {
			if($request->isMethod('get')){
				$hfLocs = ['client1/apply/app/LTO/'.$appid, 'client1/apply/app/LTO/'.$appid.'/hfsrb', 'client1/apply/app/LTO/'.$appid.'/fda', 'client1/printPaymentFDA/'.FunctionsClientController::getToken().'/'.$appid, 'client1/printPaymentFDACDRR/'.FunctionsClientController::getToken().'/'.$appid];
				if(isset($hideExtensions)) {
					$hfLocs = ['client1/apply/employeeOverride/app/LTO/'.$appid, 'client1/apply/employeeOverride/app/LTO/'.$appid.'/hfsrb', 'client1/apply/employeeOverride/app/LTO/'.$appid.'/fda', 'client1/printPaymentFDA/'.FunctionsClientController::getToken().'/'.$appid];
				}
				if(! isset($hideExtensions)) {
					$cSes = FunctionsClientController::checkSession(true);
					if(count($cSes) > 0) {
						return redirect($cSes[0])->with($cSes[1], $cSes[2]);
					}
				}
				$appDet = FunctionsClientController::getUserDetailsByAppform($appid, $hideExtensions);
				if(count($appDet) < 1) {
					return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No application selected.']);
				}
				$arrRet = [
					'userInf'=>FunctionsClientController::getUserDetails(),
					'addresses'=>$hfLocs,
					'fAddress'=>$appDet,
					'hideExtensions'=>$hideExtensions,
					'appid' => $appid,
					'data' =>AjaxController::getRequirementsFDA($appid)
				];
				// dd($arrRet);
				return view('client1.apply.LTO1.ltofda', $arrRet);
			} else {
				if(isset($request->readyNow)){
					$toAppform = [];
					$pharma = FunctionsClientController::hasEmptyDBFields('cdrrpersonnel',['appid' => $appid],['prc','coe']);
					$mach = FunctionsClientController::hasEmptyDBFields('cdrrhrpersonnel',['appid' => $appid],['prc','bc','coe']);
					
					if($pharma[2] == true && $mach[2] == true){
						if(!$pharma[0] && !$mach[0]){
							$ret = DB::table('appform')->where('appid',$appid)->update(['isReadyForInspecFDA' => 1]);
							if($ret){
								$lrf = $lrfForPharma = 0;
								$dataMach = DB::table('cdrrhrxraylist')->select('id','maxma')->where('appid',$appid)->get();
								$appform = FunctionsClientController::getUserDetailsByAppform($appid)[0];
								$cdrr = FunctionsClientController::getDetailsFDACDRR($appid);
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

								foreach($dataMach as $mach){
									$price = DB::table('fdarange')->select($findIn, 'id')->where('rangeFrom','<=',$mach->maxma)->where('rangeTo','>=',$mach->maxma)->first();
									$lrf += ($price->$findIn <= 1000 ? 10 : ($price->$findIn /100));
									$toChgfil = DB::table('fda_chgfil')->insert(['appid' => $appid, 'fchg_code' => $price->id, 'xray_listID' => $mach->id ,'MAvalue' => $mach->maxma, 'amount' => $price->$findIn, 't_date' => Carbon::now()->toDateString(), 't_time' => Carbon::now()->toTimeString(), 'uid' => session()->get('uData')->uid, 'ipaddress' => request()->ip()]);
								}

								if(isset($cdrr[3])){
									$prices = DB::table('fda_pharmacycharges')->get();
									// main
									if(isset($cdrr[3][0]) && isset($prices)){
										for ($i=0; $i < $cdrr[3][0]; $i++) { 
											$lrfForPharma += ($prices[0]->price <= 1000 ? 10 : ($prices[0]->price /100));
										}
									}
									// sattelite
									if(isset($cdrr[3][1]) && isset($prices)){
										for ($j=0; $j < $cdrr[3][1]; $j++) { 
											$lrfForPharma += ($prices[1]->price <= 1000 ? 10 : ($prices[1]->price /100));
										}
									}
								}
								DB::table('fda_chgfil')->insert(['appid' => $appid, 'fchg_code' => null, 'xray_listID' => null ,'MAvalue' => null, 'amount' => isset($cdrr[2]) ? $cdrr[2] : null, 't_date' => Carbon::now()->toDateString(), 't_time' => Carbon::now()->toTimeString(), 'uid' => session()->get('uData')->uid, 'ipaddress' => request()->ip()]);
								DB::table('fda_chgfil')->insert(['appid' => $appid, 'fchg_code' => null, 'xray_listID' => null ,'MAvalue' => null, 'amount' => $lrfForPharma, 't_date' => Carbon::now()->toDateString(), 't_time' => Carbon::now()->toTimeString(), 'uid' => 'SYSTEM', 'lrfFor' => 'cdrr', 'ipaddress' => request()->ip()]);

								$sum = DB::table('fda_chgfil')->where([['appid',$appid],['amount', '>', 0]])->sum('amount');
								DB::table('fda_chgfil')->insert(['appid' => $appid, 'amount' => $lrf, 't_date' => Carbon::now()->toDateString(), 't_time' => Carbon::now()->toTimeString(), 'lrfFor' => 'cdrrhr', 'uid' => 'SYSTEM', 'ipaddress' => request()->ip()]);

								// pharmacy
								if(FunctionsClientController::hasRequirementsFor('cdrr',$appid)){
									$toAppform['isPayEvalFDAPharma'] = 1;
									$toAppform['payEvalbyFDAPharma'] = 'SYSTEM';
									$toAppform['payEvaldateFDAPharma'] = Carbon::now()->toDateString();
									$toAppform['payEvaltimeFDAPharma'] = Carbon::now()->toTimeString();
									$toAppform['payEvalipFDAPharma'] = request()->ip();
								}
								
								// machines
								if(FunctionsClientController::hasRequirementsFor('cdrrhr',$appid)){
									$toAppform['isPayEvalFDA'] = 1;
									$toAppform['payEvalbyFDA'] = 'SYSTEM';
									$toAppform['payEvaldateFDA'] = Carbon::now()->toDateString();
									$toAppform['payEvaltimeFDA'] = Carbon::now()->toTimeString();
									$toAppform['payEvalipFDA'] = request()->ip();
								}

								isset($toAppform) ? DB::table('appform')->where('appid',$appid)->update($toAppform) : '';
								
								
							}
							return 'succ';
						} else {

							$initial = 'Please provide Personnel on Pharmacy and Radiology and make sure to submit all requirements. Following are lacking requirements. ';
							if($pharma[2] == true){
								$pharMsg = $pharma[0] ? "For Pharmacy: " . implode(",",$pharma[1]). ". ": "";
							}else{
								$pharMsg = "No personnel yet on Pharmacy. ";
							}

							if($mach[2] == true){
								$radMsg = $mach[0] ? "For Radiology: " . implode(",",$mach[1]). ". ": "";
							}else{
								$radMsg = "No personnel yet on Radiology. ";
							}

							$message = $initial.$pharMsg.$radMsg;

							return $message ;
							return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Please provide Personnel on Pharmacy and Radiology.']);
						}
					}else{
						return "Please provide Personnel on Pharmacy and Radiology and make sure to submit all requirements" ;

					}


					return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on FDA Module. Contact the admin.']);

				}
			}
		} catch(Exception $e) {
			dd($e);
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on FDA Module. Contact the admin.']);
		}
	}
	public function __novm(Request $request, $appid = "") {
		try {
			if(! empty($appid)) {
				$novm = FunctionsClientController::getNOVRecords($appid);
				if(count($novm) > 0) {
					$arrRet = [
						'userInf'=>FunctionsClientController::getUserDetails(),
						'Nov'=>$novm[0],
						'novTeams'=>DB::table('x08')->where([['team', $novm[0]->novteam]])->get()
					];
					return view('client1.nov', $arrRet);
				}
				return redirect('client1/home')->with('errRet', ['errAlt'=>'success', 'errMsg'=>'No Notice of Violation associated with this application']);
			}
		} catch(Exception $e) {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on Notice on violation Module. Contact the admin.']);
		}
	}
	public function __editApp(Request $request, $hfser, $appid) {
		$emp = FunctionsClientController::getSessionParamObj("employee_login");
		$appid1 = FunctionsClientController::getApplicationDetailsFEmployee($appid);
		if(isset($emp)) { if(count($appid1) > 0) {
			return self::__applyApp($request, $hfser, $appid, $appid1[0]->uid);
		} }
		return back()->with('errRet', ['system_error'=>'No employee on sight.']);
	}
	public function __editAppHfsrb(Request $request, $hfser, $appid) {
		$emp = FunctionsClientController::getSessionParamObj("employee_login");
		$appid1 = FunctionsClientController::getApplicationDetailsFEmployee($appid);
		if(isset($emp)) { if(count($appid1) > 0) {
			return self::__applyhfsrb($request, $hfser, $appid, $appid1[0]->uid);
		} }
		return back()->with('errRet', ['system_error'=>'No employee on sight.']);
	}
	public function __editAppFda(Request $request, $hfser, $appid) {
		$emp = FunctionsClientController::getSessionParamObj("employee_login");
		$appid1 = FunctionsClientController::getApplicationDetailsFEmployee($appid);
		if(isset($emp)) { if(count($appid1) > 0) {
			return self::__applyfda($request, $hfser, $appid, $appid1[0]->uid);
		} }
		return back()->with('errRet', ['system_error'=>'No employee on sight.']);
	}
	// syrel ni
	//assessment
	public function cdrrhrxraymachine(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'cdrrhrxraylist' => DB::table('cdrrhrxraylist')->join('fda_xraymach','cdrrhrxraylist.machinetype','fda_xraymach.xrayid')/*->leftJoin('fda_xraymach','cdrrhrxraylist.location','fda_xraymach.xrayid')*/->where('appid',$appid)->get(),
				'dropdowns' => [AjaxController::getAllFrom('fda_xraylocation'),AjaxController::getAllFrom('fda_xraymach')],
				'canAdd' => DB::table('appform')->where('appid',$appid)->select('isReadyForInspecFDA')->first()->isReadyForInspecFDA,
				'appid' => $appid
			];
			// dd($arrRet);
			return view('client1.apply.LTO1.cdrrhr.xraymachine',$arrRet);
		} else if($request->isMethod('post')) {
			if($request->action == 'add'){
				$returnToSender = DB::table('cdrrhrxraylist')->insert(['machinetype' => $request->xray, 'brandtubehead' => $request->brandTH, 'brandtubeconsole' => $request->brandCC, 'modeltubehead' => $request->modelTH, 'modeltubeconsole' => $request->modelCC, 'serialtubehead' => $request->serialTH, 'serialconsole' => $request->serialCC, 'maxma' => $request->ma , 'maxkvp' => $request->kvp , 'photonmv' => $request->lmv , 'electronsmev' => $request->lmev , 'location' => $request->location, 'appid' => $appid]);
			} else if($request->action == 'edit'){
				$returnToSender = DB::table('cdrrhrxraylist')
				->where('id',$request->id)->update([
					'machinetype' => $request->edit_lname, 
					'brandtubehead' => $request->edit_position, 
					'brandtubeconsole' => $request->edit_tin,
					'email' => $request->edit_email, 
					'modeltubeconsole' => $request->edit_govid
				]);
			} else if($request->action == 'delete') {
				$returnToSender = DB::table('cdrrhrxraylist')->where('id',$request->id)->delete();
			}
			return ($returnToSender > 0 ? "DONE" : "ERROR");
		}
	}
	public function viewcdrrhrxraymachine(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'cdrrhrxraylist' => DB::table('cdrrhrxraylist')->join('fda_xraymach','cdrrhrxraylist.machinetype','fda_xraymach.xrayid')/*->leftJoin('fda_xraymach','cdrrhrxraylist.location','fda_xraymach.xrayid')*/->where('appid',$appid)->get(),
				'dropdowns' => [AjaxController::getAllFrom('fda_xraylocation'),AjaxController::getAllFrom('fda_xraymach')]
			];
			return view('client1.apply.LTO1.cdrrhrview.xraymachine',$arrRet);
		}
	}
	public function cdrrhrxrayservcat(Request $request, $appid){
		if($request->isMethod('get')){
			$data = DB::table('fda_xraycat')->join('fda_xrayserv','fda_xrayserv.catid','fda_xraycat.catid')->get();
			$selectedServices = (!empty(DB::table('cdrrhrxrayservcat')->where('appid',$appid)->first()) ? DB::table('cdrrhrxrayservcat')->where('appid',$appid)->first()->selected : []);
			$arrRet = [
				'serv' => $data,
				'selected' => (empty($selectedServices) ? "" :explode(',',$selectedServices)),
				'canAdd' => DB::table('appform')->where('appid',$appid)->select('isReadyForInspecFDA')->first()->isReadyForInspecFDA,
				'appid' => $appid
			];
			// dd($arrRet);
			return view('client1.apply.LTO1.cdrrhr.xrayservcat',$arrRet);
		} else if($request->isMethod('post')) {

			try {
				if(!empty($request->services)){
					if(DB::table('cdrrhrxrayservcat')->where('appid',$appid)->exists() === false){
						$returnToSender = DB::table('cdrrhrxrayservcat')->insert(['selected' => implode(',',$request->services), 'appid' => $appid]);
					} else {
						$returnToSender = DB::table('cdrrhrxrayservcat')->where(['appid' => $appid])->update(['selected' => implode(',',$request->services)]);
					}
					return ($returnToSender > 0 ? "DONE" : "ERROR");
				} else {
					return 'noServSelected';
				}
			} catch (Exception $e) {
				return $e;
			}
			

		}
	}
	public function viewcdrrhrxrayservcat(Request $request, $appid){
		if($request->isMethod('get')){
			$data = DB::table('fda_xraycat')->join('fda_xrayserv','fda_xrayserv.catid','fda_xraycat.catid')->get();
			$selectedServices = (!empty(DB::table('cdrrhrxrayservcat')->where('appid',$appid)->first()) ? DB::table('cdrrhrxrayservcat')->where('appid',$appid)->first()->selected : '');
			$arrRet = [
				'serv' => $data,
				'selected' => explode(',',$selectedServices)
			];
			return view('client1.apply.LTO1.cdrrhrview.xrayservcat',$arrRet);
		}
	}
	public function cdrrpersonnel(Request $request, $appid){
		if(FunctionsClientController::isUserApplication($appid)){
			if($request->isMethod('get')){
				$inHF = array();
				$cdrr = DB::table('cdrrpersonnel')->where('appid',$appid)->get();
				if(count($cdrr) > 0){
					foreach ($cdrr as $key) {
						if(!in_array($key->id, $inHF)){
							array_push($inHF, $key->id);
						}
					}
					$inHF = implode(',', $inHF);
				}
				// dd($inHF);
				$arrRet = [
					'cdrrpersonnel' => $cdrr,
					'annexa' => DB::table('hfsrbannexa')->where('appid',$appid)->whereNotIn('id',(is_array($inHF) ? [] : [$inHF]) )->get(),
					'appid' => $appid
				];
				// dd($arrRet);
				return view('client1.apply.LTO1.cdrr.personnel',$arrRet);
			} else if($request->isMethod('post')) {
				if($request->action == 'add'){
					if(!empty($request->states)){
						foreach ($request->states as $key) {
							$data = DB::table('hfsrbannexa')->where('id',$key)->first();
							DB::table('cdrrpersonnel')->insert(['hfsrbannexaID' => $data->id, 'appid' => $data->appid, 'name' => strtolower($data->prefix . ' ' . $data->firstname . ' ' . $data->middlename . ' ' . $data->surname . ' ' . $data->suffix ), 'designation' => $data->pos, 'tin' => $data->tin, 'email' => $data->email, 'area' => $data->area]);
						}
					}
					$returnToSender = 1;
				} else if($request->action == 'edit'){
					$editArr = ['tin' => $request->edit_tin, 'area' => $request->edit_area];
					if($request->hasFile('edit_prc') || $request->hasFile('edit_coe')){
						if($request->hasFile('edit_prc')){
							$prcFilename = FunctionsClientController::uploadFile($request->edit_prc);
							$editArr['prc'] = $prcFilename['fileNameToStore'];
						}
						if($request->hasFile('edit_coe')){
							$coeFilename = FunctionsClientController::uploadFile($request->edit_coe);
							$editArr['coe'] = $coeFilename['fileNameToStore'];
						}
					} 
					$returnToSender = DB::table('cdrrpersonnel')
						->where('id',$request->id)->update(/*[*/$editArr
							// 'name' => $request->edit_lname, 
							// 'designation' => $request->edit_position, 
							// 'tin' => $request->edit_tin,
							// 'email' => $request->edit_email
							/*'governmentid' => $request->edit_govid*/
						/*]*/);
					$returnToSender = 1;
				} else if($request->action == 'delete') {
					$returnToSender = DB::table('cdrrpersonnel')->where('id',$request->id)->delete();
				}
				return ($returnToSender > 0 ? "DONE" : "ERROR");
			}
		} else {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Something went wrong. Please try again later.']);
		}
	}
	public function viewcdrrpersonnel(Request $request, $appid, $tag = false){
		if($tag && !session()->has('employee_login')){
			return redirect('employee')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Please login first']);
		}
		try {
			if($request->isMethod('get')){
				$arrRet = [
					'tag' => $tag,
					'cdrrpersonnel' => DB::table('cdrrpersonnel')->where('appid',$appid)->get()
				];
				($tag ? $arrRet['currentUser'] = AjaxController::getCurrentUserAllData() : '');
				return view('client1.apply.LTO1.cdrrview.personnel',$arrRet);
			} else {
				if($tag && $request->has('action')){
					if(DB::table('cdrrpersonnel')->where('id',$request->id)->update(['isTag' => $request->action, 'tagBy' => AjaxController::getCurrentUserAllData()['cur_user']])){
						return 'DONE';
					}
				}
			}
		} catch (Exception $e) {
			return $e;
		}
		
	}
	public function viewcdrrattachments(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'cdrrattachment' => DB::table('cdrrattachment')->where('appid',$appid)->get(),
			];
			return view('client1.apply.LTO1.cdrrview.attachment',$arrRet);
		}
	}
	public function cdrrhrpersonnel(Request $request, $appid){
		if(FunctionsClientController::isUserApplication($appid)){
			if($request->isMethod('get')){
				$inHF = array();
				$cdrrhr = DB::table('cdrrhrpersonnel')->where('appid',$appid)->get();
				if(count($cdrrhr) > 0){
					foreach ($cdrrhr as $key) {
						if(!in_array($key->id, $inHF)){
							array_push($inHF, $key->id);
						}
					}
					$inHF = implode(',', $inHF);
				}
				$arrRet = [
					'cdrrhrpersonnel' => $cdrrhr,
					'annexa' => DB::table('hfsrbannexa')->where('appid',$appid)->whereNotIn('id',(is_array($inHF) ? [] : [$inHF]) )->get(),
					'appid' => $appid
				];
				return view('client1.apply.LTO1.cdrrhr.personnel',$arrRet);
			} else if($request->isMethod('post')) {
				if($request->action == 'add'){
					if(!empty($request->states)){
						foreach ($request->states as $key) {
							$data = DB::table('hfsrbannexa')->where('id',$key)->first();
							DB::table('cdrrhrpersonnel')->insert(['hfsrbannexaID' => $data->id, 'appid' => $data->appid, 'name' => strtolower($data->prefix . ' ' . $data->firstname . ' ' . $data->middlename . ' ' . $data->surname . ' ' . $data->suffix ), 'designation' => $data->pos, 'qualification' => $data->qual, 'prcno' => $data->prcno, 'faciassign' => $data->area, 'validity' => $data->validityPeriodTo]);
						}
					}
					$returnToSender = 1;
					// $filename = FunctionsClientController::uploadFile($request->add_attachment);
					// if($filename['mime'] == 'application/pdf'){
					// $returnToSender = DB::table('cdrrhrpersonnel')->insert(['name' => $request->add_name, 'designation' => $request->add_position, 'faciassign' => $request->add_faciassign, 'qualification' => $request->add_qualification, 'prcno' => $request->add_prcno, 'validity' => $request->add_validity, 'certificate' => $filename['fileNameToStore'], 'appid' => $appid]);
					// }
					// else {
					// 	return 'invalidFile';
					// }
				} else if($request->action == 'edit'){
					// if($request->hasFile('edit_attachment') && isset($request->oldFilename)){
					// 	if(Storage::exists('public/uploaded/'.$request->oldFilename)){
					// 		unlink(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploaded' . DIRECTORY_SEPARATOR . $request->oldFilename ));
					// 	}
					// 	$filename = FunctionsClientController::uploadFile($request->edit_attachment);
					// 	if($filename['mime'] == 'application/pdf'){
					// 		$returnToSender = DB::table('cdrrhrpersonnel')->where('id',$request->id)->update([
					// 			'name' => $request->edit_name, 
					// 			'designation' => $request->edit_position, 
					// 			'faciassign' => $request->edit_faciassign,
					// 			'qualification' => $request->edit_qualification,
					// 			'prcno' => $request->edit_prcno,
					// 			'validity' => $request->edit_validity,
					// 			'certificate' => $filename['fileNameToStore']
					// 		]);
					// 	} else {
					// 		return 'invalidFile';
					// 	}
					// } else {
					// 	$returnToSender = DB::table('cdrrhrpersonnel')
					// 	->where('id',$request->id)->update([
					// 		'name' => $request->edit_name, 
					// 		'designation' => $request->edit_position, 
					// 		'faciassign' => $request->edit_faciassign,
					// 		'qualification' => $request->edit_qualification,
					// 		'prcno' => $request->edit_prcno,
					// 		'validity' => $request->edit_validity
					// 	]);
					// }
					$editArr = ['faciassign' => $request->edit_faciassign];
					if($request->hasFile('edit_prc') || $request->hasFile('edit_coe') || $request->hasFile('edit_bc')){
						
						if($request->hasFile('edit_prc')){
							$prcFilename = FunctionsClientController::uploadFile($request->edit_prc);
							$editArr['prc'] = $prcFilename['fileNameToStore'];
						}
						if($request->hasFile('edit_coe')){
							$coeFilename = FunctionsClientController::uploadFile($request->edit_coe);
							$editArr['coe'] = $coeFilename['fileNameToStore'];
						}
						if($request->hasFile('edit_bc')){
							$bcFilename = FunctionsClientController::uploadFile($request->edit_bc);
							$editArr['bc'] = $bcFilename['fileNameToStore'];
						}
					} 
					$returnToSender = DB::table('cdrrhrpersonnel')
					->where('id',$request->id)->update(/*[*/$editArr
						// 'name' => $request->edit_lname, 
						// 'designation' => $request->edit_position, 
						// 'tin' => $request->edit_tin,
						// 'email' => $request->edit_email
						/*'governmentid' => $request->edit_govid*/
					/*]*/);
					$returnToSender = 1;
				} else if($request->action == 'delete') {

					// if(Storage::exists('public/uploaded/'.$request->filename)){
					// 	unlink(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploaded' . DIRECTORY_SEPARATOR . $request->filename));
					// }
					$returnToSender = DB::table('cdrrhrpersonnel')->where('id',$request->id)->delete();
				}
				return ($returnToSender > 0 ? "DONE" : "ERROR");
			}
		} else {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Something went wrong. Please try again later.']);
		}
	}
	public function viewcdrrhrpersonnel(Request $request, $appid){
		try {
			if($request->isMethod('get')){
				$arrRet = [
					'cdrrhrpersonnel' => DB::table('cdrrhrpersonnel')->where('appid',$appid)->get()
				];
				return view('client1.apply.LTO1.cdrrhrview.personnel',$arrRet);
			}
		} catch (Exception $e) {
			return $e;
		}
		
	}
	public function cdrrreceipt(Request $request, $appid){
		if(FunctionsClientController::isUserApplication($appid)){
			if($request->isMethod('get')){
				$arrRet = [
					'cdrrreceipt' => DB::table('cdrrreceipt')->where('appid',$appid)->get()
				];
				return view('client1.apply.LTO1.cdrr.receipt',$arrRet);
			} else if($request->isMethod('post')) {
				if($request->action == 'add'){
					$filename = FunctionsClientController::uploadFile($request->add_attachment);
					if($filename['mime'] == 'application/pdf'){
						$returnToSender = DB::table('cdrrreceipt')->insert(['receiptno' => $request->add_receipt, 'dop' => $request->add_dop, 'office' => $request->add_office, 'amountpaid' => $request->add_amount, 'attachment' => $filename['fileNameToStore'], 'appid' => $appid]);
							return ($returnToSender > 0 ? "DONE" : "ERROR");
					} else {
						return 'invalidFile';
					}
				}
			}
		}
		else {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Something went wrong. Please try again later.']);
		}
	}
	public function cdrrhrreceipt(Request $request, $appid){
		if(FunctionsClientController::isUserApplication($appid)){
			if($request->isMethod('get')){
				$arrRet = [
					'cdrrhrreceipt' => DB::table('cdrrhrreceipt')->where('appid',$appid)->get()
				];
				return view('client1.apply.LTO1.cdrrhr.receipt',$arrRet);
			} else if($request->isMethod('post')) {
				if($request->action == 'add'){
					$filename = FunctionsClientController::uploadFile($request->add_attachment);
					if($filename['mime'] == 'application/pdf'){
						$returnToSender = DB::table('cdrrhrreceipt')->insert(['receiptno' => $request->add_receipt, 'dop' => $request->add_dop, 'office' => $request->add_office, 'amountpaid' => $request->add_amount, 'attachment' => $filename['fileNameToStore'], 'appid' => $appid]);
							return ($returnToSender > 0 ? "DONE" : "ERROR");
					} else {
						return 'invalidFile';
					}
				}
			}
		}
		else {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Something went wrong. Please try again later.']);
		}
	}
	public function cdrrattachments(Request $request, $appid){
		if(FunctionsClientController::isUserApplication($appid)){
			if($request->isMethod('get')){
				$arrRet = [
					'cdrrattachment' => DB::table('cdrrattachment')->where('appid',$appid)->get(),
					'canAdd' => DB::table('appform')->where('appid',$appid)->select('isReadyForInspecFDA')->first()->isReadyForInspecFDA,
					'appid' => $appid
				];
				return view('client1.apply.LTO1.cdrr.attachment',$arrRet);
			} else if($request->isMethod('post')) {
				if($request->action == 'add'){
					$filename = FunctionsClientController::uploadFile($request->add_attachment);
					if($filename['mime'] == 'application/pdf'){
						$returnToSender = DB::table('cdrrattachment')->insert(['attachmentdetails' => $request->add_details, 'attachment' => $filename['fileNameToStore'], 'appid' => $appid]);
					} else {
						return 'invalidFile';
					}
				} else if($request->action == 'delete'){
					if(Storage::exists('public/uploaded/'.$request->deleteFile)){
						unlink(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploaded' . DIRECTORY_SEPARATOR . $request->deleteFile ));
					}
					$returnToSender = DB::table('cdrrattachment')->where('id',$request->id)->delete();
				} else if($request->action == 'edit'){
					if($request->hasFile('edit_attachment') && isset($request->oldFilename)){
						if(Storage::exists('public/uploaded/'.$request->oldFilename)){
							unlink(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploaded' . DIRECTORY_SEPARATOR . $request->oldFilename ));
						}
						$filename = FunctionsClientController::uploadFile($request->edit_attachment);
						if($filename['mime'] == 'application/pdf'){
							$returnToSender = DB::table('cdrrattachment')->where('id',$request->id)->update(['attachmentdetails' => $request->edit_details, 'attachment' => $filename['fileNameToStore']]);
						} else {
							return 'invalidFile';
						}
					} else {
						$returnToSender = DB::table('cdrrattachment')->where('id',$request->id)->update([
							'attachmentdetails' => $request->edit_details
						]);
					}
				}
				return ($returnToSender > 0 ? "DONE" : "ERROR");
			}
		} else {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Something went wrong. Please try again later.']);
		}
	}




	public function cdrrhrattachments(Request $request, $appid){
		if(FunctionsClientController::isUserApplication($appid)){
			if($request->isMethod('get')){
				$arrRet = [
					'cdrrhrotherattachment' => DB::table('cdrrhrotherattachment')->leftJoin('cdrrhrrequirements','cdrrhrrequirements.reqID','cdrrhrotherattachment.reqID')->where('appid',$appid)->get(),
					'attType' => DB::table('cdrrhrrequirements')->get(),
					'appid' => $appid
				];
				// dd($arrRet);
				return view('client1.apply.LTO1.cdrrhr.attachment',$arrRet);
			} else if($request->isMethod('post')) {
				// return $request->all();
				if($request->action == 'add'){
					$filename = FunctionsClientController::uploadFile($request->add_attachment);
						$returnToSender = DB::table('cdrrhrotherattachment')->insert(['attachmentdetails' => $request->add_details, 'attachment' => $filename['fileNameToStore'], 'reqID' => $request->req, 'appid' => $appid]);
				} else if($request->action == 'delete'){
					if(Storage::exists('public/uploaded/'.$request->deleteFile)){
						unlink(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploaded' . DIRECTORY_SEPARATOR . $request->deleteFile ));
					}
					$returnToSender = DB::table('cdrrhrotherattachment')->where('id',$request->id)->delete();
				} else if($request->action == 'edit'){
					if($request->hasFile('edit_attachment') && isset($request->oldFilename)){
						if(Storage::exists('public/uploaded/'.$request->oldFilename)){
							unlink(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploaded' . DIRECTORY_SEPARATOR . $request->oldFilename ));
						}
						$filename = FunctionsClientController::uploadFile($request->edit_attachment);
						if($filename['mime'] == 'application/pdf'){
							$returnToSender = DB::table('cdrrhrotherattachment')->where('id',$request->id)->update(['attachmentdetails' => $request->edit_details, 'reqID' => $request->edit_req, 'attachment' => $filename['fileNameToStore']]);
						} else {
							return 'invalidFile';
						}
					} else {
						$returnToSender = DB::table('cdrrhrotherattachment')->where('id',$request->id)->update([
							'attachmentdetails' => $request->edit_details
						]);
					}
				}
				return ($returnToSender > 0 ? "DONE" : "ERROR");
			}
		} else {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Something went wrong. Please try again later.']);
		}
	}

	public function viewcdrrhrattachments(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'cdrrhrotherattachment' => DB::table('cdrrhrotherattachment')->leftJoin('cdrrhrrequirements','cdrrhrrequirements.reqID','cdrrhrotherattachment.reqID')->where('appid',$appid)->get()
			];
			return view('client1.apply.LTO1.cdrrhrview.attachment',$arrRet);
		}
	}


	//hfsrb requirements
	public function annexa(Request $request, $appid){
		if(FunctionsClientController::isUserApplication($appid)){
			$pos = DB::table('position')->get();
			if($request->isMethod('get')){
				$arrRet = [
					'workstat' => AjaxController::getAllWorkStatus(),
					'pos' => $pos,
					'hfsrbannexa' => [DB::table('hfsrbannexa')->leftJoin('pwork_status','pwork_status.pworksid','hfsrbannexa.employement')->leftJoin('position','position.posid','hfsrbannexa.prof')->where('appid',$appid)->get(),DB::table('hfsrbannexa')->leftJoin('pwork_status','pwork_status.pworksid','hfsrbannexa.employement')->leftJoin('position','position.posid','hfsrbannexa.prof')->where([['appid',$appid],['isMainRadio',1]])->doesntExist(),DB::table('hfsrbannexa')->leftJoin('pwork_status','pwork_status.pworksid','hfsrbannexa.employement')->leftJoin('position','position.posid','hfsrbannexa.prof')->where([['appid',$appid],['isMainRadioPharma',1]])->doesntExist(),DB::table('hfsrbannexa')->leftJoin('pwork_status','pwork_status.pworksid','hfsrbannexa.employement')->leftJoin('position','position.posid','hfsrbannexa.prof')->where([['appid',$appid],['ismainpo',1]])->doesntExist()],
					// 'canAdd' => DB::table('appform')->where([['appid',$appid],['isReadyForInspec',0]])->exists()
					'canAdd' => true,
					'appid' =>$appid
				];
				// dd($arrRet);
				return view('client1.apply.LTO1.hfsrb.annexa',$arrRet);
			} else if($request->isMethod('post')) {
				$customInsertMach = $customInsertPhar = false;
				$filename = $returnToSender = null;
				$arrName = $arrFiles = $arrPharma = $arrMach = array();
				$toInsert = ['surname' => strtolower($request->sur_name), 'firstname' => strtolower($request->fname), 'middlename' => $request->mname, 'prof' => $request->prof, 'prcno' => $request->prcno, /*'validityPeriodFrom' => $request->vfrom,*/ 'validityPeriodTo' => $request->vto ,'speciality' => $request->speciality, 'dob' => $request->dob, 'sex' => $request->sex, 'employement' => $request->employement, 'prefix' => $request->prefix, 'suffix' => $request->suffix, 'pos' => $request->position, 'designation' => $request->designation, 'area' => $request->assignment, 'qual' => $request->qual, 'email' => $request->email,'tin' => $request->tin, 'isMainRadio' => ($request->head == 1 ? $request->head : null), 'isMainRadioPharma' => ($request->pharmahead == 1 ? $request->pharmahead : null), 'ismainpo' => ($request->po == 1 ? $request->po : null), 'appid' => $appid];


				$pharma = ['appid' => $appid, 'name' => strtolower($request->prefix . ' ' . $request->fname . ' ' . $request->mname . ' ' . $request->sur_name . ' ' . $request->suffix ), 'designation' => $request->position, 'tin' => $request->tin, 'email' => $request->email, 'area' => $request->assignment];
				$mach = ['appid' => $appid, 'name' => strtolower($request->prefix . ' ' . $request->fname . ' ' . $request->mname . ' ' . $request->sur_name . ' ' . $request->suffix ), 'designation' => $request->position, 'qualification' => $request->qual, 'prcno' => $request->prcno, 'faciassign' => $request->assignment, 'validity' => $request->vto];

				// for custom addition to FDA
				if($request->po == 1 || $request->head == 1){
					$customInsertMach = true;
				}
				if(count($pos) > 0){
					foreach ($pos as $position) {
						if($position->fda_type == 'cdrr'){
							if(!in_array($position->posid, $arrPharma)){
								array_push($arrPharma, $position->posid);
							}
						}
						if($position->fda_type == 'cdrrhr'){
							if(!in_array($position->posid, $arrMach)){
								array_push($arrMach, $position->posid);
							}
						}
					}
				}
				
				if($request->has('req')){
					foreach ($request->file('req') as $key => $value) {
						$filename = FunctionsClientController::uploadFile($value);
						if($key == 'prc1'){
							array_push($arrName, 'prc');
						} else {
							array_push($arrName, $key);
						}
						array_push($arrFiles, $filename['fileNameToStore']);
					}
				}
				$filename = array_combine($arrName, $arrFiles);
				if(count($filename) > 0){
					foreach($filename as $key => $value){
						$toInsert[$key]  = $value;
					}
				}
				if($request->action == 'add'){
					$returnToSender = DB::table('hfsrbannexa')->insertGetId($toInsert);
					if($returnToSender){
						if(in_array($request->prof, $arrPharma) || in_array($request->prof, $arrMach) || $customInsertMach || $customInsertPhar){
							$pharma['hfsrbannexaID'] = $returnToSender;
							$mach['hfsrbannexaID'] = $returnToSender;
							if(in_array($request->prof, $arrPharma)  || $customInsertPhar){
								$returnToSender = DB::table('cdrrpersonnel')->insert($pharma);
							}
							if(in_array($request->prof, $arrMach) || $customInsertMach){
								$returnToSender = DB::table('cdrrhrpersonnel')->insert($mach);
							}
						}
					}
				} else if($request->action == 'edit'){
					$curStat = DB::table('hfsrbannexa')->where('id',$request->id)->first();
					if(!empty($filename) && !empty($curStat)){
						foreach ($filename as $key => $value) {
							if(Storage::exists('public/uploaded/'.$curStat->$key)){
								unlink(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploaded' . DIRECTORY_SEPARATOR . $curStat->$key ));
							}
						}
					}
					$returnToSender = DB::table('hfsrbannexa')
					->where('id',$request->id)->update($toInsert);
					if(in_array($request->prof, $arrPharma) || in_array($request->prof, $arrMach)){
						if(in_array($request->prof, $arrPharma)){
							DB::table('cdrrpersonnel')->where('hfsrbannexaID',$request->id)->update($pharma);
						}
						if(in_array($request->prof, $arrMach)){
							DB::table('cdrrhrpersonnel')->where('hfsrbannexaID',$request->id)->update($mach);
						}
					}
				} else if($request->action == 'delete') {
					$curStat = DB::table('hfsrbannexa')->where('id',$request->id)->select('status')->first()->status;
					$returnToSender = DB::table('hfsrbannexa')->where('id',$request->id)->update(['status' => ($curStat == 1 ? 2 : 1)]);
				}
				return ($returnToSender > 0 ? "DONE" : "ERROR");
		}
		} else {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Something went wrong. Please try again later.']);
		}
	}
	public function annexb(Request $request, $appid){
		if(FunctionsClientController::isUserApplication($appid)){
			if($request->isMethod('get')){
				$arrRet = [
					'hfsrbannexb' => DB::table('hfsrbannexb')->where('appid',$appid)->get(),
					// 'canAdd' => DB::table('appform')->where([['appid',$appid],['isReadyForInspec',0]])->exists()
					'canAdd' => true,
					'appid' =>$appid
				];
				return view('client1.apply.LTO1.hfsrb.annexb',$arrRet);
			} else if($request->isMethod('post')) {
				if($request->action == 'add'){
					$returnToSender = DB::table('hfsrbannexb')->insert(['equipment' => $request->equipment, 'brandname' => $request->brandname, 'model' => $request->model, 'serial' => $request->serial, 'quantity' => $request->quantity, 'dop' => $request->dop, 'manDate' => $request->manDate, 'appid' => $appid]);
				} else if($request->action == 'edit'){
					$returnToSender = DB::table('hfsrbannexb')
					->where('id',$request->id)->update([
						'equipment' => $request->equipment, 
						'brandname' => $request->brandname, 
						'model' => $request->model, 
						'serial' => $request->serial, 
						'quantity' => $request->quantity, 
						'manDate' => $request->manDate,
						'dop' => $request->dop
					]);
				} else if($request->action == 'delete') {
					$returnToSender = DB::table('hfsrbannexb')->where('id',$request->id)->delete();
				}
				return ($returnToSender > 0 ? "DONE" : "ERROR");
			}
		} else {
			return redirect('client1/home')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Something went wrong. Please try again later.']);
		}
	}
	public function annexc(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexcd' => DB::table('hfsrbannexc')->where('appid',$appid)->get(),
				// 'canAdd' => DB::table('appform')->where([['appid',$appid],['isReadyForInspec',0]])->exists()
				'canAdd' => true
			];
			return view('client1.apply.LTO1.hfsrb.annexcd',$arrRet);
		} else if($request->isMethod('post')) {
			if($request->action == 'add'){
				$returnToSender = DB::table('hfsrbannexc')->insert(['testmethod' => $request->testmethod, 'equipment' => $request->equipment, 'reagent' => $request->reagent, 'materials' => $request->materials,'appid' => $appid]);
			} else if($request->action == 'edit'){
				$returnToSender = DB::table('hfsrbannexc')
				->where('id',$request->id)->update([
					'testmethod' => $request->testmethod, 
					'equipment' => $request->equipment,
					'reagent' => $request->reagent, 
					'materials' => $request->materials, 
				]);
			} else if($request->action == 'delete') {
				$returnToSender = DB::table('hfsrbannexc')->where('id',$request->id)->delete();
			}
			return ($returnToSender > 0 ? "DONE" : "ERROR");
		}
	}
	public function annexd(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexcd' => DB::table('hfsrbannexd')->where('appid',$appid)->get(),
				// 'canAdd' => DB::table('appform')->where([['appid',$appid],['isReadyForInspec',0]])->exists()
				'canAdd' => true
			];
			return view('client1.apply.LTO1.hfsrb.annexcd',$arrRet);
		} else if($request->isMethod('post')) {
			if($request->action == 'add'){
				$returnToSender = DB::table('hfsrbannexd')->insert(['testmethod' => $request->testmethod, 'equipment' => $request->equipment, 'reagent' => $request->reagent, 'materials' => $request->materials,'appid' => $appid]);
			} else if($request->action == 'edit'){
				$returnToSender = DB::table('hfsrbannexd')
				->where('id',$request->id)->update([
					'testmethod' => $request->testmethod, 
					'equipment' => $request->equipment,
					'reagent' => $request->reagent, 
					'materials' => $request->materials, 
				]);
			} else if($request->action == 'delete') {
				$returnToSender = DB::table('hfsrbannexd')->where('id',$request->id)->delete();
			}
			return ($returnToSender > 0 ? "DONE" : "ERROR");
		}
	}
	public function annexf(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexf' => DB::table('hfsrbannexf')->where('appid',$appid)->get(),
				// 'canAdd' => DB::table('appform')->where([['appid',$appid],['isReadyForInspec',0]])->exists()
				'canAdd' => true
			];
			return view('client1.apply.LTO1.hfsrb.annexf',$arrRet);
		} else if($request->isMethod('post')) {
			if($request->action == 'add'){
				$returnToSender = DB::table('hfsrbannexf')->insert([
					$request->department => 1, 
					'fpcr' => $request->fpcr, 
					'dpbr' => $request->dpbr, 
					'dohcert' => $request->dohcert, 
					'fpccp' => $request->fpccp,
					'trained' => $request->trained,
					'fpros' => $request->fpros,
					'rxt' => $request->rxt,
					'rrt' => $request->rrt,
					'rso' => $request->rso,
					'others' => $request->others,
					'prcno' => $request->prcno,
					'validity' => $request->validity,
					'appid' => $appid
				]);
			} else if($request->action == 'edit'){
				$returnToSender = DB::table('hfsrbannexf')
				->where('id',$request->id)->update([
					'testmethod' => $request->testmethod, 
					'equipment' => $request->equipment,
					'reagent' => $request->reagent, 
					'materials' => $request->materials, 
				]);
			} else if($request->action == 'delete') {
				$returnToSender = DB::table('hfsrbannexf')->where('id',$request->id)->delete();
			}
			return ($returnToSender > 0 ? "DONE" : "ERROR");
		}
	}
	public function annexh(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexh' => DB::table('hfsrbannexh')->where('appid',$appid)->get(),
				// 'canAdd' => DB::table('appform')->where([['appid',$appid],['isReadyForInspec',0]])->exists()
				'canAdd' => true
			];
			return view('client1.apply.LTO1.hfsrb.annexh',$arrRet);
		} else if($request->isMethod('post')) {
			if($request->action == 'add'){
				$returnToSender = DB::table('hfsrbannexh')->insert([
					'brandname' => $request->brandname, 
					'model' => $request->model, 
					'serialno' => $request->serialno, 
					'quantity' => $request->quantity, 
					'dop' => $request->dop, 
					'labmaterials' => $request->labmaterials, 
					'appid' => $appid]);
			} else if($request->action == 'edit'){
				$returnToSender = DB::table('hfsrbannexh')
				->where('id',$request->id)->update([
					'brandname' => $request->brandname, 
					'model' => $request->model,
					'serialno' => $request->serialno, 
					'quantity' => $request->quantity, 
					'dop' => $request->dop, 
					'labmaterials' => $request->labmaterials
				]);
			} else if($request->action == 'delete') {
				$returnToSender = DB::table('hfsrbannexh')->where('id',$request->id)->delete();
			}
			return ($returnToSender > 0 ? "DONE" : "ERROR");
		}
	}
	public function annexi(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexi' => DB::table('hfsrbannexi')->where('appid',$appid)->get(),
				// 'canAdd' => DB::table('appform')->where([['appid',$appid],['isReadyForInspec',0]])->exists()
				'canAdd' => true
			];
			return view('client1.apply.LTO1.hfsrb.annexi',$arrRet);
		} else if($request->isMethod('post')) {
			if($request->action == 'add'){
				$returnToSender = DB::table('hfsrbannexi')->insert([
					'test' => $request->test, 
					'kittype' => $request->kittype, 
					'kit' => $request->kit, 
					'lotno' => $request->lotno, 
					'appid' => $appid]);
			} else if($request->action == 'edit'){
				$returnToSender = DB::table('hfsrbannexi')
				->where('id',$request->id)->update([
					'test' => $request->test, 
					'kittype' => $request->kittype,
					'kit' => $request->kit, 
					'lotno' => $request->lotno,
				]);
			} else if($request->action == 'delete') {
				$returnToSender = DB::table('hfsrbannexi')->where('id',$request->id)->delete();
			}
			return ($returnToSender > 0 ? "DONE" : "ERROR");
		}
	}

	public function sendActionTaken(Request $request, $from, $actid){
		try {

			$violationMonRemarks = $violationDetails = array();
			$vio = $reco = $subreco = $otherDet = null;
			$allowedFrom = ['mon','surv','fdamonitoring'];
			$from = strtolower($from);
			if(in_array($from, $allowedFrom)){
				switch ($from) {
					case 'mon':
						$table = 'mon_form';
						$field = 'monid';
						$teamField = 'team';
						$teamTable = 'mon_team_members';
						$teamPK = 'montid';
						$attachmentFromLo = 'attached_files';
						$attachmentToLo = 'attached_filesUser';
						break;
					case 'surv':
						$table = 'surv_form';
						$field = 'survid';
						$teamField = 'team';
						$teamTable = 'surv_team_members';
						$teamPK = 'montid';
						$attachmentFromLo = 'LOAttachments';
						$attachmentToLo = 'attachments';
						break;
					case 'fdamonitoring':
						$table = 'fdamonitoring';
						$field = 'fdamon';
						$teamField = 'team';
						$attachmentFromLo = null;
						$attachmentToLo = null;
						break;
				}
				$exp = DB::table($table)->where($field,$actid)->first();
				if(empty($exp)){
					return redirect('client1')->with('errRet', ['errAlt'=>'success', 'errMsg'=>'No Records found']);
				}
				if($from == 'mon'){
					$violationMon = DB::table('assessmentcombinedduplicate')->where([['monid',$actid],['selfassess',null]])->whereNotIn('evaluation',[1,'NA'])->get();
					foreach ($violationMon as $key) {
						
						array_push($violationDetails,(isset($key->assessmentName) ? $key->assessmentName : 'No Details Provided'));
						array_push($violationMonRemarks,(isset($key->remarks) ? $key->remarks : 'No Details Provided'));
					}
					$vio = array_combine($violationDetails,$violationMonRemarks);
					$otherDet = DB::table('technicalfindingshist')->where([['id',$actid],['fromWhere', 'mon']])->get();
					$reco = $exp->verdict;
				}
				if($from == 'fdamonitoring'){
					$toFilter = DB::table('fdamonitoringfiles')->where([['fdaMonId',$actid]])->orderBy('addedTimeDate','DESC')->get();
					foreach ($toFilter as $key => $value) {
						if($value->isReply != null){
							$vio['fromUser'][] = $value;
						} else {
							$vio['fromPO'] = $value;
						}
					}
				}
				if($from == 'surv'){
					$otherDet = DB::table('technicalfindingshist')->where([['id',$actid],['fromWhere', 'surv']])->get();
					$reco = DB::table('surv_rec')->where('rec_id',$exp->recommendation)->first();
					if(isset($reco)){
						switch ($reco->rec_id) {
							case 2:
								$subreco = 'payment';
								break;
							case 3:
								$subreco = 'suspension';
								break;
							case 5:
								$subreco = 's_rec_others';
								break;
						}
						$reco = array_combine(array($reco->rec_desc), array(isset($subreco) ? $exp->$subreco : null));
					}
				}
			} else {
				return redirect('client1')->with('errRet', ['errAlt'=>'success', 'errMsg'=>'No Records found']);
			}
			if($request->isMethod('get')){
				return view('client1.reported',['data' => $exp, 'from' => $from, 'LO' => $attachmentFromLo, 'user' => $attachmentToLo, 'vio' => $vio, 'reco' => $reco, 'extraDetails' => $otherDet]);
			} else if($request->isMethod('post')){
				$images = null;
				if($request->has('images')){
					$images = array();
					foreach ($request->file('images') as $key) {
						$imageRec = FunctionsClientController::uploadFile($key);
						array_push($images,$imageRec['fileNameToStore']);
					}
				}
				switch ($from) {
					case 'mon':
						
					case 'surv':
						// $test = DB::table($table)->where($field,$actid)->update(['hasLOE' => 1, 'LOE' => $request->exp, $attachmentToLo => (is_array($images) ? implode(',',$images): null )]);
						if($request->has('images')){
							$test = DB::table($table)->where($field,$actid)->update(['hasLOE' => 1, ($from == 'mon' ? 'explanation' : 'LOE') => $request->exp, $attachmentToLo => (is_array($images) ? implode(',',$images): null ), 'forResubmit' => null]);
							$forNotify = DB::table($table)->where($field,$actid)->first();
							if(isset($forNotify)){
								$notifyPersons = DB::table($teamTable)->where($teamPK,$forNotify->$teamField)->get();
								if(isset($notifyPersons)){
									foreach ($notifyPersons as $key => $value) {
										AjaxController::notifyClient($actid,$value->uid,($from == 'mon' ? 66: 67));
									}
									
								}
							}
						} else {
							$test = DB::table($table)->where($field,$actid)->update(['hasLOE' => 1, 'LOE' => $request->exp]);
						}
						break;
					case 'fdamonitoring':
						$test = DB::table($table)->where($field,$actid)->update(['hasReplyFlag' => 1]);
						if($test){
							$test = DB::table('fdamonitoringfiles')->insert(['addedBy' => (session()->get('uData')->uid ?? 'SYSTEM'), 'remark' => $request->exp, 'fdaMonId' => $actid, 'isReply' => 1, 'fileName' => (is_array($images) ? implode(',',$images): null )]);
							$POUdet = DB::table('fdamonitoring')->where('fdamon',$actid)->select('addedBy','type')->first();
							AjaxController::notifyClient($actid,$POUdet->addedBy,($POUdet->type == 'machines' ? 61: 62),$POUdet->type);
						}
					break;
				}
				return $test ? 'done' : $test;
			}
		} catch (Exception $e) {
			return $e;
			return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on action Taken. Contact the admin']);
		}
	}

	// view sched
	public function viewInsSched(Request $request, $appid){
		try {
			if(session()->has('uData')){
				$supData = FunctionsClientController::getUserDetailsByAppform($appid)[0];
				if($supData->uid == session()->get('uData')->uid && isset($supData->proposedWeek)){
					$inspectors = DB::table('app_team')->join('x08','x08.uid','app_team.uid')->where('app_team.appid',$appid)->get();
					return view('client1.insSched',['data' => $supData, 'inspectors' => $inspectors]);
				}
				return redirect('client1')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'Record does not exist.']);
			}
			return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on View Sched module. Contact the admin']);
		} catch (Exception $e) {
			return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on View Sched module. Contact the admin']);
		}
	}

	// end hfsrb requirements
	//hfsrb view requirements
	public function viewannexa(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexa' => DB::table('hfsrbannexa')->leftJoin('pwork_status','pwork_status.pworksid','hfsrbannexa.employement')->leftJoin('position','position.posid','hfsrbannexa.prof')->where('appid',$appid)->get(),
			];
			return view('client1.apply.LTO1.hfsrbView.annexa',$arrRet);
		} else {
			if(isset($request->appid)){
				$imploded = implode("','", $request->appid);
				return DB::select("SELECT facilityname,appid from appform where appid in ($imploded)");
			}
		}
	}
	public function viewannexb(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexb' => DB::table('hfsrbannexb')->where('appid',$appid)->get()
			];
			return view('client1.apply.LTO1.hfsrbView.annexb',$arrRet);
		} else {
			if(isset($request->appid)){
				$imploded = implode("','", $request->appid);
				return DB::select("SELECT facilityname,appid from appform where appid in ($imploded)");
			}
		}
	}
	public function viewannexd(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexcd' => DB::table('hfsrbannexd')->where('appid',$appid)->get()
			];
			return view('client1.apply.LTO1.hfsrbView.annexcd',$arrRet);
		}
	}
	public function viewannexc(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexcd' => DB::table('hfsrbannexc')->where('appid',$appid)->get()
			];
			return view('client1.apply.LTO1.hfsrbView.annexcd',$arrRet);
		}
	}
	public function viewannexf(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexf' => DB::table('hfsrbannexf')->where('appid',$appid)->get()
			];
			return view('client1.apply.LTO1.hfsrbView.annexf',$arrRet);
		}
	}
	public function viewannexh(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexh' => DB::table('hfsrbannexh')->where('appid',$appid)->get()
			];
			return view('client1.apply.LTO1.hfsrbView.annexh',$arrRet);
		}
	}
	public function viewannexi(Request $request, $appid){
		if($request->isMethod('get')){
			$arrRet = [
				'hfsrbannexi' => DB::table('hfsrbannexi')->where('appid',$appid)->get()
			];
			return view('client1.apply.LTO1.hfsrbView.annexi',$arrRet);
		}
	}
	// end view hfsrb requirements
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

	public function assessmentHeaderOne(Request $request, $appid, $part)
	{
		try {
			$dohC = new DOHController();
			$toViewArr = $dohC->AssessmentShowH1($request,$appid,$part,false,true);
			if($toViewArr){
				return view('client1.assessment.assessmentView',$toViewArr);
			}
			return redirect('client1/apply/assessmentReady/'.$appid)->with('errRet', ['errAlt'=>'Part does not exist.']);
		} catch (Exception $e) {
			return $e;
		}
	}

	public function AssessmentShowH2(Request $request, $appid, $h1)
	{
		try {
			$dohC = new DOHController();
			$toViewArr = $dohC->AssessmentShowH2($request,$appid,$h1,false,true);
			if($toViewArr){
				return view('client1.assessment.assessmentView',$toViewArr);
			}
			return redirect('client1/apply/assessmentReady/'.$appid)->with('errRet', ['errAlt'=>'Header does not exist.']);
		} catch (Exception $e) {
			return $e;
		}
	}

	public function AssessmentShowH3(Request $request, $appid, $h2)
	{
		try {
			$dohC = new DOHController();
			$toViewArr = $dohC->AssessmentShowH3($request,$appid,$h2,false,true);
			if($toViewArr){
				return view('client1.assessment.assessmentView',$toViewArr);
			}
			return redirect('client1/apply/assessmentReady/'.$appid)->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Area does not exist.']);
		} catch (Exception $e) {
			return $e;
		}
	}

	public function ShowAssessments(Request $request, $appid, $h3)
	{
		try {
			$dohC = new DOHController();
			$toViewArr = $dohC->ShowAssessments($request,$appid,$h3,false,true);
			if($toViewArr){
				return view('client1.assessment.assessmentAnswer',$toViewArr);
			}
			return redirect('client1/apply/assessmentReady/'.$appid)->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Sub Category does not exist or has been assessed.']);
		} catch (Exception $e) {
			return $e;
		}
	}

	public function SaveAssessments(Request $request)
	{
		try {
			$dohC = new DOHController();
			$toViewArr = $dohC->SaveAssessments($request,true);
			if($toViewArr){
				return view('client1.assessment.assessmentSuccess',$toViewArr);
			}
			return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Item not found on DB.']);
		} catch (Exception $e) {
			return $e;
		}
	}

	public function assessmentRegister(Request $request){
		return json_encode(AjaxController::logAssessed($request->level,$request->appid,$request->id,null,1)); 
	}

	public function GenerateReportAssessment(Request $request, $appid){
		try {
			$dohC = new DOHController();
			$toViewArr = $dohC->GenerateReportAssessment($request,$appid,null,1);
			if($toViewArr){
				$toViewArr['appid'] = $appid;
				return view('client1.assessment.assessmentGeneratedClient',$toViewArr);
			}
			return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Assessment records not found.']);
		} catch (Exception $e) {
			return $e;
		}
	}



	public function assessmentView(Request $request, $appid, $apptype, $choosen, $otherApplication = false)
	{		
		$charCombined = $checkExistMon = $currentUser = null;
		$appidReal = $appid;
		$applyType = 'license';
		$origChoosen = $choosen;
		$choosen = (strtoupper($choosen) !== 'OTHERS'? ['asmt2_loc.header_lvl1',$choosen] : ['asmt2_loc.asmt2l_id','<>',null]);
		$firstLevel = array();
		if ($request->isMethod('get')) 
		{
			if(strtolower($apptype) !== 'mon' && strtolower($apptype) !== 'surv'){
				$whereClause = [['x08_ft.appid','=',$appid],['serv_asmt.hfser_id', '=',$apptype], $choosen];
				$appformFetch = DB::table('appform')->where('appid',$appid)->select('uid','hfser_id','aptid')->get()->first();
				if(!empty($appformFetch)){
					$charCombined = $appformFetch->uid.'_'.$appformFetch->hfser_id.'_'.$appformFetch->aptid.'_'.$appidReal;
					if(DB::table('app_assessment')->where('appid',$charCombined)->count() > 0 || $appformFetch->hfser_id != $apptype){
						// return redirect('employee/dashboard/processflow/assessment/view/'.$appid.'/'.$apptype);
						// dd('Redirecting you to page');
					}
				} else {
					return response()->back();
					dd('Wrong appid');
				}
			} 
			try 
			{

				$asmt2_col = $asmt2_loc = $levelFirst = array();
				$joinedData = null;
				$allAccess = $filenames = array();
				$countColoumn = DB::SELECT("SELECT count(*) as 'all' FROM information_schema.columns WHERE table_name = 'asmt2'")[0]->all -1;
				// $currentUser = AjaxController::getCurrentUserAllData()['position'];
				$joinedData = FunctionsClientController::getAllAssessment($whereClause);	            // if(!empty($headersFromDB)){
				// dd($joinedData);
	            if(strtolower($origChoosen) === 'others'){
	            	$joinedData->whereNull('asmt2_loc.header_lvl1');
	            }
	            $joinedData = json_decode($joinedData->get(),true);
	            // dd($joinedData);
	            foreach ($joinedData as $data) {
		            if($countColoumn){
		            	for ($i=1; $i <= $countColoumn ; $i++) {
							$actualHeader = 'header_lvl'.$i;
							if($data['srvasmt_col'] !== null){
								foreach (json_decode($data['srvasmt_col']) as $json) {
								 	if(!in_array($json, $asmt2_col)){
								 		array_push($asmt2_col, $json);
								 	}
								 }
							}
							if(Schema::hasColumn('asmt2_loc', $actualHeader))
							{
								if($data[$actualHeader] !== NULL){
									if(!in_array($data[$actualHeader], $asmt2_loc)){
										array_push($asmt2_loc, $data[$actualHeader]);
									}
								}
							}
		            	}
		            }
					if(!empty($data['filename'])){
						if(!in_array($data['filename'], $filenames)){
							array_push($filenames, $data['filename']);
						}
					}
				}
	            ////////////fetch data from DB
	            foreach ($asmt2_col as $colValue) {
	            	$dataAll = DB::table('asmt2_col')->where('asmt2c_id' , '=', $colValue)->select('asmt2c_desc','asmt2c_type')->get()->first();
	            	$joinedData[$colValue.'Desc'] = $dataAll->asmt2c_desc;
	            	$joinedData[$colValue.'Type'] = $dataAll->asmt2c_type;
	            	$dataAll = null;
	            }
	            foreach ($asmt2_loc as $locData) {
	            	$dataAll = DB::table('asmt2_loc')->where('asmt2l_id' , '=', $locData)->select('asmt2l_desc')->get()->first();
	            	$joinedData[$locData.'Desc'] = $dataAll->asmt2l_desc;
	            	$dataAll = null;
				}
				$data = AjaxController::getAllDataEvaluateOne($appid);
				$SELECTED = $data->uid.'_'.$data->hfser_id.'_'.$data->aptid.'_'.$appidReal;
				// dd($joinedData);
				return view('client1.assessment.assessmentAnswer', ['AppData'=>$data, 'appId'=> $appidReal, 'joinedData'=>$joinedData, 'apptype' => $apptype, 'filenames'=>$filenames, 'monType'=>$applyType, 'header'=>$origChoosen,'org'=>$SELECTED/*, 'assessor' => $currentUser*/]);	
			} 
			catch (Exception $e) 
			{
				dd($e);
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pfassessmentone');	
			}
		}
		if ($request->isMethod('post')) 
		{
			try 
			{
				$Cur_useData = AjaxController::getCurrentUserAllData();
				// 	chckOrNot [] , rmks [], num, AsId [], id, SeldID [],
				// $Gas = DB::table('app_assessment')->where('appid', '=', $request->id)->first();
				$X = 0;
				for ($i=0; $i < $request->num; $i++) { 
					$test = DB::table('app_assessment')
							->where('app_assess_id', '=', $request->SeldID[$i])
							->update([
								'isapproved' => $request->chckOrNot[$i],
								'remarks' => $request->rmks[$i],
								't_date' => $Cur_useData['date'],
								't_time' => $Cur_useData['time'],
								'assessedby' => $Cur_useData['cur_user'],
								// 'uid' => $Cur_useData['cur_user']
							]);
					}
					if ($request->hasNotApproved == 0) {$Stat = 'FR';$x = 1;} 
					else { $Stat = 'RI';$x = 0;}
					$update = array(
									'status'=>$Stat,
									'isInspected'=> $x,
									'inspecteddate'=> $Cur_useData['date'],
									'inspectedtime'=> $Cur_useData['time'],
									'inspectedipaddr'=> $Cur_useData['ip'],
									'inspectedby'=> $Cur_useData['cur_user'],
								);
					$test = DB::table('appform')->where('appid', '=', $request->id)->update($update);
					$selected = AjaxController::getUidFrom($request->id);
					AjaxController::notifyClient($selected, 4);
					if ($test) {
						return 'DONE';
					} else {
						$TestError = $this->SystemLogs('No data has been modfied in appform table. (AssessmentOneProcessFlow)');
						return 'ERROR';
					}
					return $request->hasApproved;	
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				return 'ERROR';
			}
		}
	}
	public function AssessmentSend(Request $request, $appid, $apptype, $otherApplication = false)
	{
		$dataToView = $charCompiled = $toDir = $jsonToArray = $noCharCompiled = $selfAssessmentCheck = $jsonToDB = $appform = $table = $selectFromDB = $whereClause = $recordsToCheck = $tableToUpdate = $slug = $fieldsOnUpdate = $allUserDetails = $checkExistMon = $currentAssessment = $storedAssessment = $merged = $checkForStatus = $urlToRedirect = $tableNames = null;
		try 
		{
			($request->isMobile === "true" && $this->agent ? self::sessionForMobile($request->uid) : null);
			$allUserDetails = AjaxController::getCurrentUserAllData();
			$exceptData = array('_token','appID','facilityname','monType','org','header');
			if(strtolower($apptype) !== 'mon' && strtolower($apptype) !== 'surv'){//licensing
				$tableNames = 'appform';
				$urlToRedirect = asset('client1/apply/assessmentReady/'.$appid.'/'.$apptype);
				$selectFromDB = array('selfAssessment');
				if(DB::table('appform')->where('appid',$appid)->count() < 1){
					// return redirect('employee/dashboard/processflow/assessment/');
					dd('redirecting you to page');
				}
				if(!empty($request->all())){
					$charCompiled = $request->org;
				} else {
					$noCharCompiled = DB::table('appform')->where('appid',$appid)->select('uid','hfser_id','aptid')->get()->first();
					$charCompiled = $noCharCompiled->uid.'_'.$noCharCompiled->hfser_id.'_'.$noCharCompiled->aptid.'_'.$appid;
				}
				$table = 'app_assessment';
				$whereClause = 'appid';
				$fieldsOnUpdate = array(
					'isInspected'=>1,
					'inspecteddate'=>$allUserDetails['date'],
					'inspectedtime'=>$allUserDetails['time'],
					'inspectedipaddr'=>$allUserDetails['ip'],
					'inspectedby'=>$allUserDetails['cur_user']
				);
			}
			$dataToView = DB::table($table)->where($whereClause,$charCompiled)->select($selectFromDB)->get()->first();
			$selectFromDB = implode('', $selectFromDB);
			if(empty($dataToView->$selectFromDB)){
				if(!empty($request->all())){
					$recordsToCheck = $request->all();
					$slug = in_array('false',$recordsToCheck,true);
					if($slug && strtolower($apptype) !== 'mon'){	
						DB::table($tableNames)
						->where($whereClause,$appid)							
						->update($fieldsOnUpdate);
					}
					$jsonToDB = json_encode(array($request->header => $request->except($exceptData)));
					if(DB::table($table)->where($whereClause,$charCompiled)->count() <= 0 ){
						DB::table($table)->insert([
						    ['appid' => $charCompiled,'t_date' => Carbon::now(),'t_time' => Carbon::now()->toTimeString(),'selfAssessment' => $jsonToDB]
							]);
					} else {
						DB::table($table)->update([
					    	'selfAssessment' => $jsonToDB
						]);
					}	
					$dataToView = json_encode($request->except($exceptData));
				} else {
					// return view('client1.assessment.assessmentSuccess', ['redirectTo' => $urlToRedirect]);
					// return redirect('employee/dashboard/processflow/assessment');
					return response()->back();
				}
			} else {
				// $checkForStatus = (is_null(DB::table($table)->where($whereClause,$charCompiled)->select('assessmentStatus')->get()->first()) ? null : DB::table($table)->where($whereClause,$charCompiled)->select('assessmentStatus')->get()->first()->assessmentStatus); //do not make hilabot
				$dataToView = $dataToView->$selectFromDB;
				// if($checkForStatus  === 0){
					if(!empty($request->all())){
						$storedAssessment = json_decode($dataToView,true);
						$currentAssessment = array($request->header => $request->except($exceptData));
						if(!array_key_exists($request->header,$storedAssessment)){
							$merged = json_encode(array_merge($currentAssessment,$storedAssessment));
							DB::table($table)
								->where($whereClause,$charCompiled)							
								->update([$selectFromDB => $merged]);
						} /*else {
							return view('employee/assessment/operationSucess');
						}*/
					} else {
						return response()->back();
					} /*else {
						return redirect('employee/dashboard/processflow/assessment/view/'.$appid.'/'.$apptype.'/'.$otherApplication);
						dd('Redirecting you to page');
					}*/
				// } elseif($checkForStatus === 1) { // FOR POSSIBLE CHANGES ONLY. PLEASE DON'T MAKE HILABOT
					// dd('Unknown Error occured. Please try again later.');
				// }
			}
			return view('client1.assessment.assessmentSuccess', ['redirectTo' => $urlToRedirect]);
			// $dataToView = json_decode($dataToView,true);
			// $toDir = explode(',',$dataToView['filename']);
			// unset($dataToView['filename']);
			// unset($dataToView['header']);
			// $appform = DB::table('appform')->where('appid',$appid)->get()->first();
			// return view('employee.processflow.pfassessmentoneview',['data' => json_encode($dataToView),'file'=>$toDir,'selfCheck'=>$selfAssessmentCheck, 'appform' => $appform]);
		} 
		catch (Exception $e) 
		{
			dd($e);
			AjaxController::SystemLogs($e);
			session()->flash('system_error','ERROR');
			return view('employee.processflow.pfassessmentoneview');
		}
	}
	public function AssessmentDisplay(Request $request, $appid, $apptype)
	{
		$charCompiled = $noCharCompiled = $appform = $table = $selectFromDB = $whereClause = $fieldsOnUpdate = $checkExistMon = $checkStatus = $checkForStatus = $compliedToString = $dataFromDB = $mergedData = $unsortedData = $isEmptyAssess = $checkInspected = $currentTask = null;
		$assessor = $filenames = $listofSelfAssessment = array();
		$exceptData = array('_token','appID','facilityname','monType','org','header','assessor');
		$allUserDetails = AjaxController::getCurrentUserAllData();
		$fieldsOnUpdate = array('assessmentStatus' => 1);
		if(strtolower($apptype) !== 'mon' && strtolower($apptype) !== 'surv'){//licensing
			$selectFromDB = array('selfAssessment');
			if(DB::table('appform')->where('appid',$appid)->count() < 1){
				return redirect('employee/dashboard/processflow/assessment/');
				dd('redirecting you to page');
			}	
			// if(!empty($request->all())){
			// 	$charCompiled = $request->org;
			// } else {
				$noCharCompiled = DB::table('appform')->where('appid',$appid)->select('uid','hfser_id','aptid')->get()->first();
				$charCompiled = $noCharCompiled->uid.'_'.$noCharCompiled->hfser_id.'_'.$noCharCompiled->aptid.'_'.$appid;
			// }
			$table = 'app_assessment';
			$whereClause = 'appid';
		}
		$selectFromDBRaw = $selectFromDB[0];
		$isEmptyAssess = empty(DB::table($table)->where($whereClause,$charCompiled)->get()->first()->$selectFromDBRaw);
		if(!$isEmptyAssess){
			$compliedToString = $selectFromDB[0];
			$checkForStatus = (is_null(DB::table($table)->where($whereClause,$charCompiled)->select($selectFromDB)->get()->first()) ? null : DB::table($table)->where($whereClause,$charCompiled)->select($selectFromDB)->get()->first()->$compliedToString);
			$dataFromDB = json_decode($checkForStatus,true);
			foreach ($dataFromDB as $key => $value) {
				if(array_key_exists('assessor',$value)){
					if(!in_array($value['assessor'], $assessor)){
						array_push($assessor, $value['assessor']);
						unset($dataFromDB[$key]['assessor']);
					}
				}
				if(array_key_exists('filename', $value)){
					if(!in_array($value['filename'], $filenames)){
						array_push($filenames, $value['filename']);
						unset($dataFromDB[$key]['filename']);
					}
				}
			}
			$unsortedData = call_user_func_array("array_merge", $dataFromDB);
			$testArray = array();
			foreach ($unsortedData as $key => $value) {
				$stringToFind = '/headCode';
				if($key !== 'filename'){
					$string = $key;
					$findSeq = strpos($string,$stringToFind);
					$part = null;
					if($findSeq !== false) {
						$findSeq +=strlen($stringToFind);
						while(substr($string,$findSeq,1) !== '/'){
							$part = $part.substr($string,$findSeq,1);
							$findSeq +=1;
						}
						$testArray[$part][$key] = $value;
						$findSeq = $part = null;
					}
				}
			}
			$testFinalArray = array();
			// $testArray['filename'] = array($unsortedData['filename']);
			$sortArray = array(); 
			foreach ($testArray as $key => $value) {
				$testHere = $testArray[$key];
				ksort($testHere,SORT_NATURAL);
				array_push($testFinalArray, $testHere);
			}
			$tryLng = call_user_func_array("array_merge", $testFinalArray);
			// $tryLng['filename'] = $tryLng[0];
			// unset($tryLng[0]);
			$dataToView = $tryLng;
			if(strtolower($apptype) !== 'mon' && strtolower($apptype) !== 'surv'){
				$checkInspected = DB::table('appform')->where($whereClause,$appid)->select('isInspected')->first()->isInspected;
				if($checkInspected <=0 ){
					$valueToUpdate = 1;
					if(in_array('false',$dataToView,true)){
						$valueToUpdate = 2;
					}
					DB::table('appform')->where($whereClause,$appid)->update([
						'isInspected'=>$valueToUpdate,
						'inspecteddate'=>Date('Y-m-d'),
						'inspectedtime'=>Date('H:i:s',strtotime('now')),
						'inspectedipaddr'=>$request->ip(),
						'inspectedby'=>session()->get('uData')->uid
					]);
				}
			}
			$currentTask = 'Self Assessment';
			$listofSelfAssessment = ['LTO'];
			if(!in_array($apptype,$listofSelfAssessment)){
				$currentTask = 'Checklist';
			}
			$toDir = $filenames;
			return view('client1/assessment/assessmentDisplay',['data' => json_encode($dataToView),'file'=>$toDir, 'assessor' => $assessor, 'currentTask' => $currentTask]);
		} else {
			return redirect('client1/apply');
		}
	}
	//extra
	//extra
	//extra
	//extra extra
	public static function __rToken(Request $request, $token) {
		try {
			if($request->isMethod('get')) {
				$chkQry = DB::table('x08')->where('token', $token)->select('*')->first();
				dd($chkQry);
				if($chkQry != null) {
					DB::table('x08')->where('token', $token)->update(['token'=>NULL]);
					return redirect('client1')->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Successfully verified account.']);
				} else {
					return redirect('client1')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'Error on verifying account. Token must be expired.']);
				}
			} else {
				return redirect('client1');
			}
		} catch (Exception $e) {
			return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'An error has occured in verifying token.']);
		}
	}
	
	public static function __rMail(Request $request, $uid) {
		try {
			if($request->isMethod('get')) {
				$nToken = Str::random(40);
				$chkQry = DB::table('x08')->where('uid', $uid)->select('*')->first();
				dd($chkQry);
				if($chkQry != null) {
					$dRequest = new stdClass();
					$dRequest->text2 = $chkQry->facilityname; 
					$dRequest->text6 = $chkQry->email;
					$sData = ['name'=>$chkQry->facilityname, 'authorizedsignature'=>$chkQry->authorizedsignature, 'assign'=>$chkQry->assign, 'password'=>NULL, 'token'=>$nToken];

					
					if(DB::table('x08')->where('uid', $uid)->update(['token'=>$nToken])) { //true
						if(FunctionsClientController::sMailVerRetBool('client1.mail', $sData, $dRequest)) {
							return redirect('client1')->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Successfully sent verification to your account.']);
						}
						return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on sending verfication email.']);
					}
					return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error on updating verification account.']);
				}
				return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No user selected.']);
			} else {
				// return redirect()->route('client1.login');
			}
		} catch (Exception $e) {
			return redirect('client1')->with(['errAlt'=>'danger', 'errMsg'=>'An error has occured.']);
		}
	}

	public function __rTbl(Request $request, $tbl) {
		try {
			if($request->isMethod('get')) {
				$toReturn = DB::table($tbl)->select('*');
				if($tbl == 'barangay'){
					$toReturn->orderBy('brgyname','ASC');
				}
				return json_encode($toReturn->get());
			} else {
				$_WHERE = []; $_rTable = []; $_rCol = "";
				if(isset($request->rTbl) && isset($request->rId)) {
					if(is_array($request->rTbl) && is_array($request->rId)) {
						if(count($request->rTbl) == count($request->rId)) {
							for($i = 0; $i < count($request->rTbl); $i++) {
								array_push($_WHERE, [$request->rTbl[$i], $request->rId[$i]]);
							}
						} else {
							return json_encode(["not equal count"]);
						}
					} elseif(is_array($request->rId)) {
						$_rCol = $request->rTbl;
						foreach($request->rId AS $rIdRow) {
							array_push($_WHERE, ((isset($request->rFunc)) ? $rIdRow : [$request->rTbl, $rIdRow]));
						}
					} else {
						array_push($_WHERE, [$request->rTbl, $request->rId]);
					}
				}
				if(count($_WHERE) > 0) {
					if(isset($request->rFunc)) {
						$_rTable = DB::table($tbl)->whereIn($_rCol, $_WHERE)->select('*')->get();
					} else {
						$_rTable = DB::table($tbl)->where($_WHERE)->select('*')->get();
					}
				} else {
					$_rTable = DB::table($tbl)->select('*')->get();
				}
				return json_encode($_rTable);
			}
		} catch (Exception $e) {
			return json_encode($e);
		}
	}
	public function __customQuery(Request $request, $custom) {
		// try {
			$notify = false;
			if(isset($custom)) {
				if($request->isMethod('get')) {
					switch($custom) {
						case 'assessment':
							
							break;
						case 'logoutUser':
							session()->forget('uData'); 
							session()->forget('payment');
							session()->forget('appcharge');
							session()->forget('ambcharge');
							session()->forget('directorSettings');
							return json_encode(true);
							break;
						case 'checkFile':
							$path = 'ra-idlis/public/js/forall.js';
							if(!File::exists($path)) {
							    return json_encode(false);
							} else {
								return json_encode(true);
							}
							break;
						case 'checkDirectory':
							$path = 'ra-idlis/public/js1';
							if(!File::isDirectory($path)) {
							    return json_encode(false);
							} else {
								return json_encode(true);
							}
							break;
						case 'decodeThis':
							break;
						case 'fGetAppformIdLatest':
							return json_encode(FunctionsClientController::fGetAppformIdLatest(FunctionsClientController::getSessionParamObj("uData", "uid")));
							break;
						case 'fTrySubmitPayment':
							break;
						default:
							return json_encode([]);
							break;
					}
				} else {
					switch($custom) {
						case 'checkUser':
							if(isset($request->_userName) && isset($request->_userPass)) {
								$retThis = FunctionsClientController::procLogin($request->_userName, $request->_userPass);
								return json_encode($retThis);
							} else {
								return json_encode('Please input Username and Password');
							}
							return json_encode('No username and/or password.');
							break;
						case 'fPassword':
							$retArr = ["No email supplied."];
							if(isset($request->_email)) {
								$retThis = FunctionsClientController::fPassword(FunctionsClientController::findColGC('email', $request->_email), $request->_token);
								$retArr = json_encode($retThis);
							}
							return $retArr;
							break;
						case 'oldPass':
							if(isset($request->_oPass) && isset($request->_oUid)) {
								$retThis = FunctionsClientController::findColGC('uid', $request->_oUid);
								if(count($retThis) > 0) {
									$__bool = Hash::check($request->_oPass, $retThis[0]->pwd);
									return json_encode($__bool);
								}
								return json_encode(false);
							}
							return json_encode([]);
							break;
						case 'chgPass':
							if(isset($request->_nPass) && isset($request->_oUid)) { //isset($request->_oPass) && 
								$retThis = FunctionsClientController::findColGC('uid', $request->_oUid);
								if(count($retThis) > 0) {
									$__bool = true; //Hash::check($request->_oPass, $retThis[0]->pwd);
									if($__bool) {
										if(!AjaxController::isPreviousPassword($request->_nPass,$request->_oUid)){

											if(DB::table('x08')->where([['uid', $request->_oUid], ['grpid', 'C']])->update(['pwd'=>Hash::make($request->_nPass), 'lastChangePassDate' => Date('Y-m-d'), 'lastChangePassTime' => Date('H:i:s',strtotime('now'))])) {
												DB::table('pwdHistory')->insert(['uid' => $request->_oUid, 'pwd' => Hash::make($request->_nPass)]);
												return json_encode(true);
											} else {
												json_encode("Error on updating password.");
											}

										} else {
											return json_encode("Please input password that does not exist before.");
										}

									} else {
										return json_encode("Old password does not match.");
									}
								}
								return json_encode("No user found in current user id.");
							}
							return json_encode("Old and new password is not set.");
							break;
						case 'fRegister':
							// qwe
							$rToken = Str::random(40);
							$nPassword = (strlen($request->pwd) > 3) ? str_repeat('*', (strlen($request->pwd) - 3)) . substr($request->pwd, -3) : "***";
							$arrData = [/*'rgnid', 'province', 'city_muni', 'barangay', 'streetname', 'zipcode', 'street_number',  */'authorizedsignature', 'assign', 'email', 'contact', 'uid', 'pwd', 'nameofcompany']; 
							$arrCheck = [['uid', 'email'], ['uid'=>"Username already taken.", 'email'=>"Email already used. Please use another email."]]; 
							$makeHash = ['pwd']; 
							$haveAdd = ['grpid'=>'C', 'ipaddress'=>request()->ip(), 't_date'=>Carbon::now()->toDateString(), 't_time'=>Carbon::now()->toTimeString(), 'token'=>$rToken]; 
							$fMail = ['client1.mail', ['name'=>strtoupper($request->uid), 'authorizedsignature'=>$request->authorizedsignature, 'assign'=>$request->assign, 'password'=>$nPassword, 'token'=>$rToken], ['authorizedsignature', 'email']]; 
							$validate = [[/*'rgnid', 'province', 'city_muni', 'barangay', */'authorizedsignature', 'assign', 'email', 'contact', 'uid', 'pwd', 'nameofcompany'], [/*'rgnid'=>'No region selected.', 'province'=>'No province selected', 'city_muni'=>'No city/municipality selected', 'barangay'=>'No barangay selected',*/ 'authorizedsignature'=>'Please input name (to be used as owner)', 'assign'=>'Please Provide Position', 'email'=>'Please input email.', 'contact'=>'Please input contact information', 'uid'=>'Please input your username', 'street_number'=>'no street number', 'pwd'=>'Please input your password', 'nameofcompany'=>'Please provide name of company']];
							return json_encode(FunctionsClientController::fInsData($request->all(), $arrData, $arrCheck, $makeHash, $haveAdd, $fMail, $validate, 'x08'));
							break;
						case 'fEmail':
							if(isset($request->_cEmail)) {
								$retThis = FunctionsClientController::findColGC('email', $request->_cEmail);
								if(count($retThis) > 0) {
									return json_encode("Email already used. Please use another email account.");
								}
								return json_encode(true);
							}
							return json_encode("No email.");
							break;
						case 'fUid':
							if(isset($request->_cUid)) {
								$retThis = FunctionsClientController::findColGC('uid', strtoupper($request->_cUid));
								if(count($retThis) > 0) {
									return json_encode("Username already used. Please use another username.");
								}
								return json_encode(true);
							}
							return json_encode("No username.");
							break;
						case 'fApply':
							$arrData = ['hfser_id', 'facilityname', 'owner', 'rgnid', 'provid', 'cmid', 'brgyid', 'contact', 'email', 'uid', 'street_name', 'street_number', 'faxNumber', 'zipcode', 'landline', 'mailingAddress', 'ownerMobile', 'ownerLandline', 'ownerEmail', 'areacode', 'ocid', 'classid', 'subClassid', 'facmode','funcid','approvingauthority','approvingauthoritypos','draft']; 
							$arrCheck = []; $makeHash = []; $haveAdd = ['ipaddress'=>request()->ip(), 't_date'=>Carbon::now()->toDateString(), 't_time'=>Carbon::now()->toTimeString(), 'status'=>'P', 'assignedRgn'=>$request->rgnid]; 
							$fMail = []; 
							$validate = [
								['hfser_id', 'facilityname', 'owner', 'rgnid', 'provid', 'cmid', 'brgyid', 'contact', 'email',/* 'street_name',*/ 'zipcode', 'ownerMobile','ownerEmail','ocid','classid','facmode','funcid','approvingauthority','approvingauthoritypos'], 
								['hfser_id'=>'Please select type of application', 'facilityname'=>'PLease input/select facility name.', 'owner'=>'Please input owner\'s name', 'rgnid'=>'Please select region', 'provid'=>'Please select province', 'cmid'=>'Please select city/municipality', 'brgyid'=>'Please select barangay', 'contact'=>'Please input contact information', 'email'=>'Please input email.'/*, 'street_name'=>'No street name specified.'*/, 'zipcode'=>'No zipcode specified.', 'mailingAddress'=>'No Mailing Address specified.', 'ownerMobile'=>'No Proponent/Owner Mobile Number specified.', 'ownerEmail'=>'No Proponent/Owner Email specified.', 'ocid' => 'Please provide Ownership details.', 'classid' => 'Please Select Class', 'facmode' => 'Please select Institutional Character','funcid' => 'Please select function','approvingauthority' => 'Please specify the fullname of Approving Authority', 'approvingauthoritypos' => 'Please specify the Position / Designation of Approving Authority']
							];
							$where = [['appid', $request->appid]];
							return json_encode(((isset($request->appid) || !empty($request->appid)) ? FunctionsClientController::fUpdData($request->all(), $arrData, $arrCheck, $makeHash, [], $fMail, $validate, 'appform', $where) : FunctionsClientController::fInsData($request->all(), $arrData, $arrCheck, $makeHash, $haveAdd, $fMail, $validate, 'appform', true)));
							break;
						case 'fPTCApp':
							session()->forget('ambcharge');
							$arrData = [
								[
									'ocid', 
									'classid', 
									'subClassid', 
									'facmode', 
									'funcid', 
									'hfep_funded', 
									'assignedRgn'
								], 
								[
									'appid', 
									'propbedcap', 
									'conCode', 
									'propstation', 
									'incbedcapfrom', 
									'incbedcapto', 
									'incstationfrom', 
									'incstationto', 
									'construction_description', 
									/*'others',*/ 
									'type', 
									'renoOption'
								], 
								[
									'uid', 
									'appid', 
									'facid'
								]
							];
							$arrCheck = [[], [], []];
							$makeHash = [[], [], []];
							$haveAdd = [['ipaddress'=>request()->ip(), 't_date'=>Carbon::now()->toDateString(), 't_time'=>Carbon::now()->toTimeString()], [], []];
							$fMail = [[], [], []];
							$validate = [[['ocid', 'facmode', 'funcid'], ['ocid'=>'No ownership selected.', 'facmode'=>'No Institutional Character selected.', 'funcid'=>'No function selected.']], [['type', 'construction_description'], ['type'=>'Please select Construction type.', 'construction_description' => 'Please provide Scope of works']], [['uid', 'appid', 'facid'], ['uid'=>'No user selected.', 'appid'=>'No application selected.', 'facid'=>'Please select Type of facility and service capability.']]];
							$tbl = ['appform', 'ptc', 'x08_ft'];
							$appid = [$request->appid, $request->ptcid, null]; 
							$where = [[['appid', $request->appid]], [['id', $request->ptcid]], [['appid', $request->appid]]]; 
							$numLoop = [1, 1, count(($request->facid ?? ['no']))];
							$msgRet = [];
							for($i = 0; $i < count($tbl); $i++) { if($i == 2) { DB::table($tbl[$i])->where($where[$i])->delete(); } for($j = 0; $j < $numLoop[$i]; $j++) {
								$rData = [$request->all(), $request->all(), ['uid'=>$request->uid, 'appid'=>$request->appid, 'facid'=>((isset($request->facid[$j])) ? $request->facid[$j] : NULL)]];
								if(isset($appid[$i])){
									DB::table('chgfil')->where([['appform_id', $appid[$i]]])->delete();
									$notify = $appid[$i];
								}
								$stat = ((isset($appid[$i])) ? FunctionsClientController::fUpdData($rData[$i], $arrData[$i], $arrCheck[$i], $makeHash[$i], $haveAdd[$i], $fMail[$i], $validate[$i], $tbl[$i], $where[$i],true) : FunctionsClientController::fInsData($rData[$i], $arrData[$i], $arrCheck[$i], $makeHash[$i], $haveAdd[$i], $fMail[$i], $validate[$i], $tbl[$i]));
								if(! in_array($stat, $msgRet)) {
									array_push($msgRet, $stat);
								}
							} 
							if($stat !== true){
								return json_encode([$stat]);
							}
							}
							if($notify){
								FunctionsClientController::notifyForChange($notify);
							}
							return json_encode(FunctionsClientController::procInsCharges($msgRet));
							break;
						case 'fCOAApp':
							session()->forget('ambcharge');
							$arrData = [['ocid', 'classid', 'subClassid', 'facmode', 'funcid', 'hfep_funded', 'assignedRgn','noofmain','noofsatellite'], ['uid', 'appid', 'facid']];
							$arrCheck = [[], []];
							$makeHash = [[], []];
							$haveAdd = [['ipaddress'=>request()->ip(), 't_date'=>Carbon::now()->toDateString(), 't_time'=>Carbon::now()->toTimeString()], []];
							$fMail = [[], []];
							$validate = [[['ocid', 'facmode', 'funcid'], ['ocid'=>'No ownership selected.', 'facmode'=>'No Institutional Character selected.', 'funcid'=>'No function selected.']], [['uid', 'appid', 'facid'], ['uid'=>'No user selected.', 'appid'=>'No application selected.', 'facid'=>'Please select Type of facility and service capability.']]];
							$tbl = ['appform', 'x08_ft'];
							$appid = [$request->appid, null]; 
							$where = [[['appid', $request->appid]], [['appid', $request->appid]]]; 
							$numLoop = [1, count(($request->facid ?? ['no']))];
							$msgRet = [];
							for($i = 0; $i < count($tbl); $i++) { if($i == 1) { DB::table($tbl[$i])->where($where[$i])->delete(); } for($j = 0; $j < $numLoop[$i]; $j++) { $rData = [$request->all(), ['uid'=>$request->uid, 'appid'=>$request->appid, 'facid'=>((isset($request->facid[$j])) ? $request->facid[$j] : NULL)]];
								if(isset($appid[$i])){
									DB::table('chgfil')->where([['appform_id', $appid[$i]]])->delete();
									$notify = $appid[$i];
								}
								$stat = ((isset($appid[$i])) ? FunctionsClientController::fUpdData($rData[$i], $arrData[$i], $arrCheck[$i], $makeHash[$i], $haveAdd[$i], $fMail[$i], $validate[$i], $tbl[$i], $where[$i], true) : FunctionsClientController::fInsData($rData[$i], $arrData[$i], $arrCheck[$i], $makeHash[$i], $haveAdd[$i], $fMail[$i], $validate[$i], $tbl[$i]));
								// return $stat;
								if(! in_array($stat, $msgRet)) {
									array_push($msgRet, $stat);
								}
							} 
							if($stat !== true){
								return json_encode([$stat]);
							}
							}
							if($notify){
								FunctionsClientController::notifyForChange($notify);
							}
							return json_encode(FunctionsClientController::procInsCharges($msgRet));
							break;
						case 'fDefaultApp':
							session()->forget('ambcharge');
							$arrData = [['ocid', 'classid', 'subClassid', 'facmode', 'funcid', 'hfep_funded', 'assignedRgn'], ['uid', 'appid', 'facid']];
							$arrCheck = [[], []];
							$makeHash = [[], []];
							$haveAdd = [['ipaddress'=>request()->ip(), 't_date'=>Carbon::now()->toDateString(), 't_time'=>Carbon::now()->toTimeString()], []];
							$fMail = [[], []];
							$validate = [[['ocid', 'facmode', 'funcid'], ['ocid'=>'No ownership selected.', 'facmode'=>'No Institutional Character selected.', 'funcid'=>'No function selected.']], [['uid', 'appid', 'facid'], ['uid'=>'No user selected.', 'appid'=>'No application selected.', 'facid'=>'Please select Type of facility and service capability.']]];
							$tbl = ['appform', 'x08_ft'];
							$appid = [$request->appid, null]; 
							$where = [[['appid', $request->appid]], [['appid', $request->appid]]]; 
							$numLoop = [1, count(($request->facid ?? ['no']))];
							$msgRet = [];
							for($i = 0; $i < count($tbl); $i++) { if($i == 1) { DB::table($tbl[$i])->where($where[$i])->delete(); } for($j = 0; $j < $numLoop[$i]; $j++) { $rData = [$request->all(), ['uid'=>$request->uid, 'appid'=>$request->appid, 'facid'=>((isset($request->facid[$j])) ? $request->facid[$j] : NULL)]];
								if(isset($appid[$i])){
									DB::table('chgfil')->where([['appform_id', $appid[$i]]])->delete();
									$notify = $appid[$i];
								}
								$stat = ((isset($appid[$i])) ? FunctionsClientController::fUpdData($rData[$i], $arrData[$i], $arrCheck[$i], $makeHash[$i], $haveAdd[$i], $fMail[$i], $validate[$i], $tbl[$i], $where[$i], true) : FunctionsClientController::fInsData($rData[$i], $arrData[$i], $arrCheck[$i], $makeHash[$i], $haveAdd[$i], $fMail[$i], $validate[$i], $tbl[$i]));
								// return $stat;
								if(! in_array($stat, $msgRet)) {
									array_push($msgRet, $stat);
								}
							} 
							if($stat !== true){
								return json_encode([$stat]);
							}
							}
							if($notify){
								FunctionsClientController::notifyForChange($notify);
							}
							return json_encode(FunctionsClientController::procInsCharges($msgRet));
							break;
						case 'fLTOApp':
							// hospital list
							// $facidForHospital = ['H','H2','H3'];
							// $toUnsetIfHospital = [['noofamb','typeamb','ambtyp','plate_number']];
							// return json_encode($request->all());
							$arrData = [['ocid', 'classid', 'subClassid', 'facmode', 'funcid', 'aptid', 'ptcCode', 'noofbed', 'clab', 'noofsatellite', 'noofmain', 'noofamb', 'hfep_funded', 'assignedRgn', 'typeamb', 'ambtyp', 'plate_number', 'ambOwner'], ['uid', 'appid', 'facid']]; //, 'others_oanc'
							// hospital check if not in required ambulance services
							// foreach($facidForHospital as $facid){
							// 	if(!in_array($facid, $request->all())){
							// 		foreach ($toUnsetIfHospital as $key => $data) {
							// 			for ($i=0; $i < count($data); $i++) { 
							// 				unset($arrData[$key][array_search($toUnsetIfHospital[$key][$i], $arrData[$key])]);
							// 			}
							// 		}
							// 	}
							// }
							$arrCheck = [[], []];
							$makeHash = [[], []];
							$haveAdd = [['ipaddress'=>request()->ip(), 't_date'=>Carbon::now()->toDateString(), 't_time'=>Carbon::now()->toTimeString()], []];
							$fMail = [[], []];
							$validate = [[['ocid', 'facmode', /*'funcid', 'noofbed',*/ 'aptid'/*, 'clab'*/], ['ocid'=>'No ownership selected.', 'facmode'=>'No Institutional Character selected.',/* 'funcid'=>'No function selected.', 'noofbed'=>'Please specify number of bed(s)',*/'aptid'=>'No application type.'/*, 'clab'=>'Please specify clinical laboratory.'*/]], [['uid', 'appid', 'facid'], ['uid'=>'No user selected.', 'appid'=>'No application selected.', 'facid'=>'Please select Type of facility and service capability.']]];
							if(isset($request->aptid) && $request->aptid == 'IN'){
								array_push($validate[0][0], 'ptcCode');
								$validate[0][1]['ptcCode'] = 'Please provide PTC code';
							}
							$tbl = ['appform', 'x08_ft'];
							$appid = [$request->appid, null]; 
							$where = [[['appid', $request->appid]], [['appid', $request->appid]]]; 
							$numLoop = [1, count(($request->facid ?? ['no']))];

							$msgRet = [];
							for($i = 0; $i < count($tbl); $i++) { if($i == 1) { DB::table($tbl[$i])->where($where[$i])->delete(); }
								for($j = 0; $j < $numLoop[$i]; $j++) {
									$rData = [$request->all(), ['uid'=>$request->uid, 'appid'=>$request->appid, 'facid'=>((isset($request->facid[$j])) ? $request->facid[$j] : NULL)]];
									if(isset($appid[$i])){
										DB::table('chgfil')->where([['appform_id', $appid[$i]]])->delete();
										$notify = $appid[$i];
									}
									$stat = ((isset($appid[$i])) ? FunctionsClientController::fUpdData($rData[$i], $arrData[$i], $arrCheck[$i], $makeHash[$i], $haveAdd[$i], $fMail[$i], $validate[$i], $tbl[$i], $where[$i], true) : FunctionsClientController::fInsData($rData[$i], $arrData[$i], $arrCheck[$i], $makeHash[$i], $haveAdd[$i], $fMail[$i], $validate[$i], $tbl[$i]));
									if(! in_array($stat, $msgRet)) {
										array_push($msgRet, $stat);
									}
								}
								if($stat !== true){
									return json_encode([$stat]);
								}
							 }
							 if($notify){
							 	FunctionsClientController::notifyForChange($notify);
							 }
							return json_encode(FunctionsClientController::procInsCharges($msgRet));
							break;
						case 'fCONApp':
							session()->forget('ambcharge');
							$arrData = [
								[
									'ocid', 
									'classid', 
									'subClassid', 
									/*'funcid',*/ 
									'cap_inv', 
									'lot_area', 
									'noofbed', 
									'hfep_funded', 
									'assignedRgn'
								], 
								['uid', 'appid', 'facid'], 
								['appid', 'type', 'location', 'population'], 
								['appid', 'facilityname', 'location1', 'cat_hos', 'noofbed1', 'license', 'validity', 'date_operation', 'remarks']
							];
							$arrCheck = [[], [], [], []];
							$makeHash = [[], [], [], []];
							$haveAdd = [['ipaddress'=>request()->ip(), 't_date'=>Carbon::now()->toDateString(), 't_time'=>Carbon::now()->toTimeString()], [], [], []];
							$fMail = [[], [], [], []];
							$validate = [
								[
									['ocid', 'cap_inv', 'lot_area', 'noofbed'/*, 'funcid'*/], 
									['ocid'=>'No Ownership selected', 
									'cap_inv'=>'Please input capital Investment', 
									'lot_area'=>'Please specify lot area for hospital', 
									'noofbed'=>'Specify number of bed(s)'/*,
									'funcid' => 'Please specify classification of  hospital'*/]
								], 
								[
									['uid', 'appid', 'facid'], 
									['uid'=>'No user selected.', 'appid'=>'No application selected.', 'facid'=>'Please select Type of facility and service capability.']
								], 
								[
									['location'], 
									['location' => 'Cathchment Population field is required']
								], 
								[
									[], 
									[]
								]
							];
							$tbl = ['appform', 'x08_ft', 'con_catch', (isset($request->facilityname) ? 'con_hospital' : null)];
							// return $tbl;
							$appid = [$request->appid, null, null, null]; 
							$where = [
								[
									['appid', $request->appid]
								], 
								[
									['appid', $request->appid]
								], 
								[
									['appid', $request->appid]
								], 
								[
									['appid', $request->appid]
								]
							]; 
							$numLoop = [
								1, 
								count(($request->facid ?? ['no'])), 
								count($request->type), 
								(isset($request->facilityname) ? count($request->facilityname) : [])
							];
							// return $numLoop;
							$msgRet = [];
							for($i = 0; $i < count($tbl); $i++) { 
								if(isset($tbl[$i])){
									if($i > 0) {
									 DB::table($tbl[$i])->where($where[$i])->delete(); 
									} 
									for($j = 0; $j < $numLoop[$i]; $j++) {
										$arr1 = ((isset($request->facid[$j])) ? ['uid'=>$request->uid, 'appid'=>$request->appid, 'facid'=>$request->facid[$j]] : []);
										$arr2 = ((isset($request->type[$j])) ? ['appid'=>$request->appid, 'type'=>$request->type[$j], 'location'=>$request->location[$j], 'population'=>$request->population[$j]] : []);
										$arr3 = ((isset($request->facilityname[$j])) ? ['appid'=>$request->appid, 'facilityname'=>$request->facilityname[$j], 'location1'=>$request->location1[$j], 'cat_hos'=>$request->cat_hos[$j], 'noofbed1'=>$request->noofbed1[$j], 'license'=>$request->license[$j], 'validity'=>$request->validity[$j], 'date_operation'=>$request->date_operation[$j], 'remarks'=>$request->remarks[$j]] : []);
										$rData = [$request->all(), $arr1, $arr2, $arr3];
										if(isset($appid[$i])){
											$notify = $appid[$i];
											DB::table('chgfil')->where([['appform_id', $appid[$i]]])->delete();
										}
										$stat = ((isset($appid[$i])) ? 
											FunctionsClientController::fUpdData(
													$rData[$i], 
													$arrData[$i], 
													$arrCheck[$i], 
													$makeHash[$i], 
													$haveAdd[$i], 
													$fMail[$i], 
													$validate[$i], 
													$tbl[$i], 
													$where[$i], true) : 
											FunctionsClientController::fInsData(
												$rData[$i], 
												$arrData[$i], 
												$arrCheck[$i], 
												$makeHash[$i], 
												$haveAdd[$i], 
												$fMail[$i], 
												$validate[$i], 
												$tbl[$i]));
										if(! in_array($stat, $msgRet)) {
											array_push($msgRet, $stat);
										}
									} 
								}
							}
							if($notify){
								FunctionsClientController::notifyForChange($notify);
							}
							return json_encode(FunctionsClientController::procInsCharges($msgRet));
							break;
						case 'fUploads':
							$arrData = ['filepath', 'type', 'ltotype', 'app_id', 'fileExten', 'fileSize'];
							$arrCheck = []; $makeHash = []; $haveAdd = ['ipaddress'=>request()->ip(), 't_date'=>Carbon::now()->toDateString(), 't_time'=>Carbon::now()->toTimeString()]; $fMail = [];
							$validate = [['filepath', 'type', 'ltotype', 'app_id'], ['filepath'=>'No filepath specified.', 'type'=>'No type.', 'ltotype'=>'No LTO type', 'app_id'=>'No application selected.']];
							$tbl = 'app_upload'; $retData = "";
							if(isset($request->filepath)) {
								$reData = FunctionsClientController::uploadFile($request->filepath);
				                $rData = ['filepath'=>$reData['fileNameToStore'], 'type'=>$request->type, 'ltotype'=>$request->ltotype, 'app_id'=>$request->appid, 'fileExten'=>$reData['fileExtension'], 'fileSize'=>$reData['fileSize']];
				                $retData = FunctionsClientController::fInsData($rData, $arrData, $arrCheck, $makeHash, $haveAdd, $fMail, $validate, $tbl);
				            }
							return json_encode($retData);
							break;
						case 'getServiceCharge':
							$retArr = [];
							// return $request->all();
							if(isset($request->facid) && isset($request->appid)) {
								// session()->forget('ambcharge');
								$retArr = FunctionsClientController::getServiceCharge($request->facid, $request->hfser_id, $request->facmode, $request->extrahgpid, $request->aptid); 
								// $retArr = FunctionsClientController::getServiceCharge($request->facid, $request->hfser_id, $request->facmode, $request->extrahgpid, $request->aptid); 
								$sql = "SELECT 'Application Payment' AS facname, appform_orderofpayment.oop_paid AS amt, '297' AS chgapp_id FROM appform_orderofpayment WHERE appid = '$request->appid'"; 
								$chkOop = DB::select($sql);
								$sessSave = ((count($chkOop) > 0) ? $chkOop : $retArr);
								session()->put('payment', [FunctionsClientController::getSessionParamObj("uData", "uid") => [$retArr, $request->appid]]);
							}else{
								// $retArr = FunctionsClientController::getServiceCharge($request->facid, $request->hfser_id, $request->facmode, $request->extrahgpid); 
								$retArr = FunctionsClientController::getServiceCharge($request->facid, $request->hfser_id, $request->facmode, $request->extrahgpid, $request->aptid); 
							}
							return json_encode($retArr);
							break;
						case 'getAncillary':
							$retArr = [];
							if(isset($request->id) && !empty($request->id)) {
								$hfserid = null;
								if(isset($request->from)){
									switch ($request->from) {
										case 1:
											$hfserid = 'LTO';
											break;
										case 2:
											$hfserid = 'PTC';
											break;
										case 3:
											$hfserid = 'CON';
											break;
										case 4:
											$hfserid = 'ATO';
											break;
										case 5:
											$hfserid = 'COR';
											break;
										case 6:
											$hfserid = 'COA';
											break;
									}
								}
								$retArr = FunctionsClientController::getAncillaryServices($request->id,$request->selected,$hfserid); 

							}
							return json_encode($retArr);
							break;
						case 'getApplyLoc':
							$retArr = [];
							if(isset($request->id) && !empty($request->id)) {
								$retArr = FunctionsClientController::getApplyLocation($request->hfer,$request->id); 
							}
							return json_encode($retArr);
							break;
						case 'getChargesPerApplication':
							$retArr1 = [];
							if(isset($request->hgpid) && isset($request->aptid)) {
								$retArr1 = FunctionsClientController::getChargesPerApplication($request->hgpid, $request->aptid, $request->hfser_id);
								session()->put('appcharge', [FunctionsClientController::getSessionParamObj("uData", "uid") => [$retArr1, $request->appid]]);
							}
							return json_encode($retArr1);
							break;
						case 'getChargesPerAmb':
							$retArr2 = [];
							if(isset($request->ambamt) && isset($request->appid)) {
								if(floatval($request->ambamt) > 0) { $retArr2 = DB::select(DB::raw("SELECT NULL AS chgapp_id, 'Ambulance charge' AS chg_desc, '$request->ambamt' AS amt")); }
								session()->put('ambcharge', [FunctionsClientController::getSessionParamObj("uData", "uid") => [$retArr2, $request->appid]]);
							}
							return $retArr2;
							break;
						case 'getGoAncillary':
							$retArr = [];
							if(isset($request->facid)) {
								$retArr = FunctionsClientController::getGoAncillary($request->facid);
							}
							return json_encode($retArr);
							break;
						default:
							return json_encode([]);
							break;
					}
				}
			} else {
				return json_encode([]);
			}
		// } catch(Exception $e) {
		// 	return json_encode($e);
		// }
	}

	public function viewReport(Request $request,$selected,$appid){
		if(isset($selected) && isset($appid) && in_array(true,AjaxController::isSessionExist(array(['uData']))) && FunctionsClientController::existOnDB('appform',array(['appid',$appid],['uid',session()->get('uData')->uid]))){
			try {

				if($request->isMethod('get')){
					switch ($selected) {
						case 'floorplan':
							$dohC = new DOHController();
							$evalC = new EvaluationController();

							$evaluationData = $dohC->viewhfercresult($request,$appid,(AjaxController::maxRevisionFor($appid) != 0 ? AjaxController::maxRevisionFor($appid) : 1),true);
							if($evaluationData){
								$dataOfEntry = $evalC->FPGenerateReportAssessment($request, $appid, $evaluationData['evaluation']->revision, $evaluationData['evaluation']->HFERC_evalBy, true);
								if($dataOfEntry){
									$evaluationData['dataOfEvaluation'] = true;
									foreach ($dataOfEntry as $key => $value) {
										$evaluationData[$key] = $value;
									}
								}
								$evaluationData['canResub'] = !FunctionsClientController::existOnDB('chgfil',array(['appform_id',$appid],['uid',session()->get('uData')->uid],['revision',(AjaxController::maxRevisionFor($appid) != 0 ? AjaxController::maxRevisionFor($appid) : 1)]));
								
								return view('client1.reports.floorplan',$evaluationData);
							} else {
								return redirect('client1/apply')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Forbidden']);
							}
							break;
						
						default:
							# code...
							break;
					}
				} elseif($request->isMethod('post')){
					switch ($selected) {
						case 'floorplan':

							$main = AjaxController::getHighestApplicationFromX08FT($appid);
							if(isset($main) && !FunctionsClientController::existOnDB('chgfil',array(['appform_id',$appid],['uid',session()->get('uData')->uid],['revision',(AjaxController::maxRevisionFor($appid) != 0 ? AjaxController::maxRevisionFor($appid) : 1)]))){
								$data = DB::table('chg_app')
								->join('charges', 'chg_app.chg_code', '=', 'charges.chg_code')
								->join('orderofpayment', 'chg_app.oop_id', '=', 'orderofpayment.oop_id')
								->join('category', 'charges.cat_id', '=', 'category.cat_id')
								->join('apptype', 'chg_app.aptid', '=', 'apptype.aptid')
								->leftJoin('hfaci_serv_type', 'chg_app.hfser_id', '=', 'hfaci_serv_type.hfser_id')
								->where([['chg_app.hfser_id', 'PTC'],['category.cat_type','C'],['charges.hgpid',$main->hgpid],['charges.fprevision',1]])
								->orderBy('chg_app.chgopp_seq','asc')
								->first();
								$shouldPay = AjaxController::isRequredToPayPTC((AjaxController::maxRevisionFor($appid) != 0 ? AjaxController::maxRevisionFor($appid) : 1));

								$arrDataChgfil = [$data->chgapp_id, $data->chg_num, $appid, NULL, NULL, NULL, NULL, NULL, NULL, $data->chg_desc, $data->amt, Carbon::now()->toDateString(), Carbon::now()->toTimeString(), request()->ip(), FunctionsClientController::getSessionParamObj("uData", "uid"), (AjaxController::maxRevisionFor($appid) != 0 ? AjaxController::maxRevisionFor($appid) : 1)];

								$arrSaveChgfil = ['chgapp_id', 'chg_num', 'appform_id', 'chgapp_id_pmt', 'orreference', 'deposit', 'other', 'au_id', 'au_date', 'reference', 'amount', 't_date', 't_time', 't_ipaddress', 'uid', 'revision'];

								if(!$shouldPay){
									$arrSaveChgfil = ['chgapp_id', 'chg_num', 'appform_id', 'chgapp_id_pmt', 'orreference', 'deposit', 'other', 'au_id', 'au_date', 'reference', 'amount', 't_date', 't_time', 't_ipaddress', 'uid', 'revision','isPaid'];
									$arrDataChgfil = [NULL, NULL, $appid, NULL, NULL, NULL, NULL, NULL, NULL, 'NO CHARGE', 0, Carbon::now()->toDateString(), Carbon::now()->toTimeString(), request()->ip(), FunctionsClientController::getSessionParamObj("uData", "uid"), (AjaxController::maxRevisionFor($appid) != 0 ? AjaxController::maxRevisionFor($appid) : 1),1];
								}

								$insert = DB::table('chgfil')->insert(array_combine($arrSaveChgfil, $arrDataChgfil));
								if($insert) { 

									if($shouldPay){
										$toUpdate = null;
										DB::table('appform')->where('appid',$appid)->update(['isPayEval' => $toUpdate, 'payEvaldate' => $toUpdate, 'payEvaltime' => $toUpdate, 'payEvalip' => $toUpdate, 'payEvalby' => $toUpdate, 'isCashierApprove' => $toUpdate, 'CashierApproveBy' => $toUpdate, 'CashierApproveDate' => $toUpdate, 'CashierApproveTime' => $toUpdate, 'CashierApproveIp' => $toUpdate, 'isRecoForApproval' => $toUpdate, 'isAcceptedFP' => $toUpdate, 'status' => 'REVF']);
									} else {
										DB::table('hferc_evaluation')->where('appid',$appid)->update(['HFERC_eval' => 2]);
									}

									return redirect('client1/apply/app/showResult/'.$selected.'/'.$appid)->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Success. Please wait for evaluation to complete']);
								
								
								}
							}
							return redirect('client1/apply/app/showResult/'.$selected.'/'.$appid)->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error. Please try again later']);

							break;
						
						default:
							# code...
							break;
					}
				}

				
			} catch (Exception $e) {
				dd($e);
			}

		} else {
			return redirect('client1')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Application Not Found']);
		}
	}

	public function generateForCertificate($appid){
		if(isset($appid)){
			return self::generateQR(url('client1/certificates/view/external/').'/'.$appid);
		}
	}

	public static function generateQR($textTOGenerate){
		if(isset($textTOGenerate)){
			// $location = url('ra-idlis/public/img/qrlogo.png');
			$image = \QrCode::format('png')
	                         // ->merge($location, 0.2, true)
	                         ->size(230)->errorCorrection('H')
	                         ->generate($textTOGenerate);
	      	return response($image)->header('Content-type','image/png');
      	}
	}

	public function redirectToApplication($appid){
		if(isset($appid)){
			$data = DB::table('appform')->where('appid',$appid)->first();
			return redirect(url('client1/apply/app/'.$data->hfser_id.'/'.$appid));
		}
	}

}