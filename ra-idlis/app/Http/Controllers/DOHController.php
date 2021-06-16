<?php
namespace App\Http\Controllers;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Database\Query\Builder;
	use Illuminate\Support\Facades\Schema;
	use Carbon\Carbon;
	use Illuminate\Support\Str;
	use Illuminate\Support\Facades\Crypt;
	use Mail;
	use Exception;
	use Hash;
	use Storage;
	use Session;
	use DateTime;
	use DateTimeZone;
	use AjaxController;
	// syrel added
	use Cache;
	use Agent;
	use FunctionsClientController;
	use EvaluationController;

	class DOHController extends Controller
	{
		private $agent;

		public function __construct() // for mobile detection
		{
			$this->middleware(function ($request, $next){
				// dd(session()->all());
		        // if(session()->has('uData')){
		        // 	session()->forget('uData');
		        // }
		        return $next($request);
		    });
			$this->agent = Agent::isMobile();
			// dd(session()->all());
			// if(session()->has('uData')){
			// 	dd('qwe');
			// 	session()->forget('uData');
			// }
		}

		public function sessionForMobile($sessionUID)
		{
			if(!session()->has('employee_login') && $this->agent){
				$employeeData =	DB::table('x08')
	            ->join('region', 'x08.rgnid', '=', 'region.rgnid')
	            ->join('x07', 'x08.grpid', '=', 'x07.grp_id')
	            ->select('x08.*', 'region.rgn_desc', 'x07.grp_desc')
	            ->where('x08.uid', '=', $sessionUID)
	            ->first();
				session()->put('employee_login',$employeeData);
				return 'DONE';
			}
		}

		///////////////////////////////////////////////// GENERAL
		public function login(Request $request, $isMobile = false) // Login 
		{ 
			AjaxController::getHeaderSettings();
			$agent = $this->agent;
			$returnToMobile = $data = $chck = $currentMoment = $employeeData = $name = $bannedForMin = $lastTryDB = $currentTries = $updateToBan = $adjustedBanTime = $checkTempBan = null;
			if ($request->isMethod('get')) 
			{
				return view('employee.login');
			}
			else if($request->isMethod('post'))
			{
				try {
					$currentMoment = strtotime(Date('Y-m-d h:i:sa'));
					$uname = strtoupper($request->uname);
					$pass= $request->pass;
					if(!empty($uname) && !empty($pass)){
						$data = DB::table('x08')->where([ ['uid', '=', $uname], ['grpid', '!=', 'C'] ])->select('*')->first();
						$m99Settings = AjaxController::getAllSettings();
						if($data){ //username exist
							$chck = Hash::check($pass, $data->pwd);
							if ($chck) { //correct password
								if($data->isActive == 1){ //active account, must be 2 equals only, (string to int)
									if(empty($data->token)){ //verified
										if($data->isBanned == 0){ //not banned from system ,must be 2 equals only, (string to int)
											if($data->tries == 0 && empty($data->lastTry)){ // not temporarily banned
												if(!AjaxController::isPasswordExpired($uname)){
													$employeeData =	DB::table('x08')
					                                ->join('region', 'x08.rgnid', '=', 'region.rgnid')
					                                ->join('x07', 'x08.grpid', '=', 'x07.grp_id')
					                                ->select('x08.*', 'region.rgn_desc', 'x07.grp_desc')
					                                ->where('x08.uid', '=', $data->uid)
					                                ->first();
						                            $x = $employeeData->mname;
								                    if ($x != "") 
								                    {
								                    	$mid = strtoupper($x[0]);
								                    	$mid = $mid.'. ';
								                    } 
								                    else 
								                    {
								                    	$mid = ' ';
								                    }
								                    $rights = DB::table('x06')
								                    			->where('grp_id', '=', $employeeData->grpid)
								                    			->get();
								                    $name = $employeeData->fname.' '.$mid.''.$employeeData->lname;
								                    $employeeData->name = $name;
								                    session()->put('employee_right', $rights);
								                    session()->put('employee_login',$employeeData);
								                    return (($agent && $isMobile) ? response()->json(array('status'=> 'success','data' => $employeeData)) : redirect()->route('eDashboard'));
							                	} else {
							                		session()->flash('dohUser_login','Your password has Expired. Please follow <a style="text-decoration: none;" href="'.asset('employee/reset/password').'/'.$data->uid.'">this</a> link to create new password');
													$returnToMobile = 'Your password has Expired. Please follow this link to create new password';
													return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : back());
							                	}
											} else { //temporarily banned
												// if(!empty($data->lastTry) &&){
												$lastTryDB = $data->lastTry;
												$bannedForMin = (int)(($lastTryDB - $currentMoment) / 60);
												if($bannedForMin >= 1){ //block user for certain minutes
													session()->flash('dohUser_login','This account is temporarily banned from  logging in the system due to multiple login attemps. Try again after '. $bannedForMin.($bannedForMin === 1 ? ' minute' : ' minutes'));
													$returnToMobile = 'This account is temporarily banned from  logging in the system due to multiple login attemps. Try again after '. $bannedForMin.($bannedForMin == 1 ? ' minute' : ' minutes');
						            				return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : back());
					            				} else {
					            					DB::table('x08')->where('uid', '=', $data->uid)->update(['tries'=> 0, 'lastTry' => null]);
					            					$employeeData =	DB::table('x08')
						                                ->join('region', 'x08.rgnid', '=', 'region.rgnid')
						                                ->join('x07', 'x08.grpid', '=', 'x07.grp_id')
						                                ->select('x08.*', 'region.rgn_desc', 'x07.grp_desc')
						                                ->where('x08.uid', '=', $data->uid)
						                                ->first();
							                            $x = $employeeData->mname;
									                    if ($x != "") 
									                    {
									                    	$mid = strtoupper($x[0]);
									                    	$mid = $mid.'. ';
									                    } 
									                    else 
									                    {
									                    	$mid = ' ';
									                    }
									                    $rights = DB::table('x06')
									                    			->where('grp_id', '=', $employeeData->grpid)
									                    			->get();
									                    $name = $employeeData->fname.' '.$mid.''.$employeeData->lname;
									                    $employeeData->name = $name;
									                    session()->put('employee_right', $rights);
									                    session()->put('employee_login',$employeeData);
									                    return (($agent && $isMobile) ? response()->json(array('status'=> 'success','data' => $employeeData)) : redirect()->route('eDashboard')); //unblock
					            				}
												// }
											}
										} 

										else { //banned from system
											session()->flash('dohUser_login','This account is permanently banned from  logging in the system.');
											$returnToMobile = 'This account is permanently banned from  logging in the system.';
											return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : back());
										}
									} else { //not verified
										session()->flash('unverified',$data->uid);
										$returnToMobile = 'Account not yet verified. Please Check your email';
			                			return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile, 'link'=> asset('/employee/resend').'/'.$data->uid)) : back());
									}





								} else { //inactive account
									session()->flash('dohUser_login','Account Deactivated, Contact nearest Regional Administrator/National Administrator.');
									$returnToMobile = 'Account Deactivated, Contact nearest Regional Administrator/National Administrator.';
									return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : back());
								}
							} else { //incorrect password

								$currentTries = $data->tries;
								if($currentTries < $m99Settings->pass_ban && $data->isBanned != 1){ //not banned, tries < set
									$checkTempBan = (!empty($data->lastTry) ? ((int)(($data->lastTry - $currentMoment)/60) < 0 ? true : false) : true);
									if($checkTempBan === true){
										$currentTries++;
										DB::table('x08')->where('uid',$data->uid)->update(['tries' => $currentTries]);
									}
									if($currentTries == $m99Settings->pass_temp){
										if(empty($data->lastTry)){
											$adjustedBanTime = strtotime('+'.$m99Settings->pass_min.' minutes',$currentMoment);
											DB::table('x08')->where('uid',$data->uid)->update(['lastTry'=>$adjustedBanTime]);
											$adjustedBanTime = (int)(($adjustedBanTime - $currentMoment)/60);
										} else {
											$adjustedBanTime = (int)(($data->lastTry - $currentMoment)/60);
										}
										session()->flash('dohUser_login','This account is temporarily banned from  logging in the system due to multiple login attemps. Try again after '. $adjustedBanTime.($adjustedBanTime === 1 ? ' minute' : ' minutes'));
										$returnToMobile = 'This account is temporarily banned from  logging in the system due to multiple login attemps. Try again after '. $adjustedBanTime.($adjustedBanTime == 1 ? ' minute' : ' minutes');
			            				return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : back());
									} elseif($currentTries > $m99Settings->pass_temp && $currentTries == ($m99Settings->pass_ban - 1)){
										session()->flash('dohUser_login','You only have 1(one) more time to try again after you will be permanently banned from system.');
											$returnToMobile = 'You only have 1(one) more time to try again after you will be permanently banned from system.';
				            				return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : back());
									} else {
										session()->flash('dohUser_login','Login Failed! Invalid Username/Password, Login Tries = '.$currentTries);
										$returnToMobile = 'Login Failed! Invalid Username/Password, Login Tries = '.$currentTries;
				            			return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : back());
									}
								} else { //banned 
									if($data->isBanned != 1){ //check if banned flag edited
										$updateToBan = DB::table('x08')->where('uid',$data->uid)->update(['isBanned' => 1]);
									}
									session()->flash('dohUser_login','This account is permanently banned from  logging in the system.');
									$returnToMobile = 'This account is permanently banned from  logging in the system.';
									return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : back());
								}

							}


						} else { //username does not exist
							session()->flash('dohUser_login','Invalid Username/Password');
							$returnToMobile = 'Invalid Username/Password';
                			return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : back());
						}


					} else {
						if(empty($pass)){
							session()->flash('dohUser_login','Please enter your password');
							$returnToMobile = 'Please enter your password';
						} else {
							session()->flash('dohUser_login','Please enter your usernmae');
							$returnToMobile = 'Please enter your usernmae';
						}
						return (($agent && $isMobile) ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : back());
					}
					
				} catch (Exception $e) {
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return back();
				}
			}
		}



		public function logout(Request $request) // Logout
		{
			// if ($request->isMethod('post')) 
			// {
				try 
				{
					session()->forget('directorSettings');
					Session::forget('employee_login');
		      		session()->flash('dohUser_logout','Successfully Logout');
					
					//   return view('client1.login');
					return redirect()->route('employee');
				} 
				catch (Exception $e) {
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return redirect()->route('employee');
				}
			// }
			// else if ($request->isMethod('get')) {
			// 	try 
			// 	{
			// 		return redirect()->route('employee');
			// 	} 
			// 	catch (Exception $e) 
			// 	{
			// 		AjaxController::SystemLogs($e);
			// 		session()->flash('system_error','ERROR');
			// 		return redirect()->route('employee');
			// 	}
			// }
		} 
		public function forgotPassword(Request $request)
		{
			$returnToMobile = null;
			if ($request->isMethod('get')) 
			{
				try 
				{
					return view('employee.forgotpassword');
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return back();
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$checkEmail = DB::table('x08')->where('email', '=', $request->email)->first();
					if (!$checkEmail) 
					{
						// return 'NOACCOUNT';
						$returnToMobile = 'NOACCOUNT';
        				return ($this->agent ? response()->json(array('status'=> 'error', 'message' => $returnToMobile)) : $returnToMobile);
					} 
					else 
					{
						$nToken = Str::random(40);
						DB::table('x08')->where('uid', '=', $checkEmail->uid)->update(['token'=>$nToken]);
						$dataToBeSend = array('token'=>$nToken);
						Mail::send('mail4ForgotPassword', $dataToBeSend, function($message) use ($request) 
						{
							$message->to($request->email, 'User')->subject('Password Recovery Request');
							$message->from('doholrs@gmail.com','DOH Support');
						});
						$returnToMobile = 'DONE';
        				return ($this->agent ? response()->json(array('status'=> 'success', 'message' => $returnToMobile)) : $returnToMobile);
					}
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return back();
				}
			}
		}
		public function forgotChangePassword(Request $request, $token)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::checkTokenforChangePassword($token);
					if (isset($data)) {
						// return dd($data->uid);
						return view('employee.forgotChangePass', ['uid'=>$data->uid]);
					} else {
						session()->flash('dohUser_login','Token unavailable.');
						return redirect()->route('employee');

					}
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return redirect()->route('employee');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					//Hash::make($request->pass);
					$hashedPass = Hash::make($request->pass);
					// 
					// $oldPass = DB::table('x08')->where('uid', '=', $request->uid)->select('pwd')->first();
					// return $oldPass->pwd;
					$upd = array('pwd' => $hashedPass, 'token' => null); //
					$data = DB::table('x08')->where('uid', '=', $request->uid)->update($upd);
					if ($data) {
						return 'DONE';
					} else {
						return 'ERROR';
					}
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function resetPassword(Request $request, $uid)
		{
			$chck = $pwd = null;
			if ($request->isMethod('get')) 
			{
				try 
				{
					if (DB::table('x08')->where('uid',$uid)->exists()) {
						if(AjaxController::isPasswordExpired($uid)){
							return view('employee.resetPass');
						} else {
							session()->flash('dohUser_login','Password is not yet expired!');
							return redirect()->route('employee');
						}
					} else {
						session()->flash('dohUser_login','User not found!');
						return redirect()->route('employee');
					}


				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return redirect()->route('employee');
				}
			}
			if ($request->isMethod('post')) 
			{
				return AjaxController::processExpired($uid,$request->pwd,$request->pass);
			}
		}
		public function dashboard(Request $request) // Dashboard
		{
			$countData = $decodedData = $countDecodedData = $data = null;
			$arrayKeys = $subDesc = $filterer = array();
			try 
			{	
				// dd(AjaxController::isPasswordExpired('abfhospital'));
				// dd(AjaxController::getAllFromCond('x08',['']));
				$Cur_data = AjaxController::getCurrentUserAllData();
				$data = AjaxController::getAllApplicants($Cur_data['grpid']);
				foreach ($data as $key => $value) {
					if(!in_array($value->status, $filterer)){
						$filterer[$value->status]['color'] = $value->statColor;
						$filterer[$value->status]['original'] = $value->trns_desc;
						$filterer[$value->status]['statID'] = $value->status;
					}
				}
				$dataJson = json_decode($data, true);
				$allID = array();
				foreach ($data as $key) {
					$curAppid = $key->appid;
					array_push($allID, [DB::select("SELECT hgpdesc FROM hfaci_grp WHERE hgpid IN (SELECT hgpid FROM facilitytyp WHERE facid IN (SELECT facid FROM x08_ft WHERE appid = '$curAppid') GROUP BY hgpid)"), DB::select("SELECT facname FROM facilitytyp WHERE facid IN (SELECT facid FROM x08_ft WHERE appid = '$curAppid')")]);
				}
				// dd($data);
				$x08 = FunctionsClientController::getCol('x08', [['uid', FunctionsClientController::getSessionParamObj('employee_login', 'uid')]]);
		        $grp = ((count($x08) > 0) ? FunctionsClientController::getCol('x07', [['grp_id', $x08[0]->grpid]]) : []);

		        $hfaci_serv_type = DB::table('hfaci_serv_type')->get();
		        $surv = AjaxController::getAllSurveillanceForm();
		        $mon = AjaxController::getAllMonitoringForm();
		        $new_data = array("surv"=>$surv, "mon"=>$mon);

				return view('employee.dashboard', ['BigData'=> $data, 'grpid' => $Cur_data['grpid'], 'subdesc' => $allID, 'filters' => $filterer, 'n_grpid' => $x08, 'r_grpid' => $grp, 'hfaci_serv_type'=>$hfaci_serv_type, 'new_data'=>$new_data]);
			} 
			catch (Exception $e) 
			{
				dd($e);
				// return $e->getMessage();
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.dashboard');
			}
		}
		public function resend_ver(Request $request, $id) // Resend Verification Code
		{
			try 
			{
				$data = DB::table('x08')->where('uid', '=', $id)->first();
				if ($data) // Check if user is registered
				{
					$name = AjaxController::NameSorter($data->fname, $data->fname, $data->lname);
					$dataToBeSend = array('name'=>$name, 'token'=>$data->token);
					try 
					{
						Mail::send('mail4SystemUsers', $dataToBeSend, function($message) use ($data) 
						{
							$message->to($data->email, $data->facilityname)->subject('Verify Email Account');
							$message->from('doholrs@gmail.com','DOH Support');
						});
						session()->flash('dohUser_logout','Successfully resend email, please check your email to verify your account.');
				      	return redirect()->route('employee');
					} 
					catch (Exception $e) // Error in Sending the Email
					{
						AjaxController::SystemLogs($e);
						session()->flash('dohUser_login','An error occured during resending the email, please contact the system administrator.');
			    		return redirect()->route('employee');	
					}
				} 
				else // Return an error if user is not registered
				{
					session()->flash('dohUser_login','An error occured during resending the email, please contact the system administrator.');
			    	return redirect()->route('employee');	
				}
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('dohUser_login','An error occured during resending the email, please contact the system administrator.');
			    return redirect()->route('employee');	
			}
		}
		public function verify_account(Request $request, $id) // Verify Account
		{
			try 
			{
				$updateData = array('token'=>NULL);
				$table = DB::table("x08")->where("token", "=", $id)->update($updateData);
				if ($table) // Check if x08 is Updated
				{
					session()->flash('dohUser_logout','Successfully verified account');
			        return redirect()->route('employee');
				}
				else // No data is Updated
				{
					AjaxController::SystemLogs("No data has been updated in x08 table upon verifying its account.");
			        session()->flash('dohUser_login',"Account not verified! Error on verifying account. Account may have been verified or email doesn't exists");
			        return redirect()->route('employee');
				}
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('dohUser_login','Account not verified! Error on verifying account. Account may have been verified or email doesnt exists');
			    return redirect()->route('employee');
			}
		}
		public function Notification(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllNotifications();
					// return dd($data);
					return view('employee.others.Notification', ['AllData'=> $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.others.Notification');
				}
			}
		}
		public function getGroupRights(Request $request)
		{
			try 
			{
				$grpid = session()->get('employee_login');
				$rights = DB::table('x06')
	    			->where('grp_id', '=', $grpid->grpid)
	    			->select('mod_id','allow','ad_d','upd','cancel', 'print','view')
	    			->get();
				return response()->json($rights);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				return null;
			}
		}
		///////////////////////////////////////////////// GENERAL
		///////////////////////////////////////////////// MASTER FILE
		////// TEAM
		public function MfTeam(Request $request) // Master File - Team
		{ 
			// dd(Cache::get('mods'));
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllRegion();
					$data2 = AjaxController::getAllTeams();
					return view('employee.masterfile.mfTeam', ['region' => $data, 'team' =>$data2]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfTeam');
				}
			}
			else if($request->isMethod('post')) // Add new Team
			{
				try 
				{
					DB::table('team')->insert(['teamid' => $request->id, 'teamdesc' => $request->name, 'rgnid' => $request->rgn]);
					return 'DONE';
				} 
				catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// TEAM
		////// MANAGE TEAM
		public function MfManageTeam(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllRegion();
					return view('employee.masterfile.mfManageTeams', ['regions' => $data]); 
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfManageTeams'); 
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$data = array('team' => $request->team);
					$test = DB::table('x08')->where('uid', '=', $request->id)->update($data);
					if ($test) {return 'DONE';}
					else
					{
						AjaxController::SystemLogs('No data has been updated in x08 table. (MfManageTeam)');
						return 'ERROR';
					}
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// MANAGE TEAM
		////// APPLICATION TYPE
		public function AppType(Request $request) // Master File - Application Type
		{
			if ($request->isMethod('get')) 
			{
				try 
				{	
					$data = AjaxController::getAllApplicationType();
					// return dd($data);
					return view('employee.masterfile.mfApplicationType', ['hfstypes'=>$data]);	
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfApplicationType');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('hfaci_serv_type')->insert(['hfser_id' => $request->id, 'hfser_desc' => $request->name, 'seq_num' => $request->seq, 'terms_condi'=>$request->terms]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		// other ancillary
		public function ancilliary(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = DB::table('serv_type')->get();
					return view('employee.masterfile.mfOtherAncillary', ['data' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfOtherAncillary');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					switch ($request->action) {
						case 'add':
							$returnToUser = DB::table('serv_type')->insert(['anc_name' => $request->name]);
							break;
						case 'edit':
							$returnToUser = DB::table('serv_type')->where('servtype_id', $request->id)->update(['anc_name' => $request->ename]);
							break;
						case 'delete':
							$returnToUser = DB::table('serv_type')->where('servtype_id', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		
		public function fdapharma(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = DB::table('fda_pharmacycharges')->get();
					return view('employee.masterfile.mfFDApharmaCharges', ['data' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfFDApharmaCharges');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$returnToUser = DB::table('fda_pharmacycharges')->where('chargeID', $request->id)->update(['price' => $request->ename]);
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function apploc(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = DB::table('chg_faci')->get();
					return view('employee.masterfile.mfapploc', ['data' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfapploc');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					switch ($request->action) {
						case 'add':
							$returnToUser = DB::table('chg_faci')->insert(['applytofaci' => $request->name]);
							break;
						case 'edit':
							$returnToUser = DB::table('chg_faci')->where('applylocid', $request->id)->update(['applytofaci' => $request->ename]);
							break;
						case 'delete':
							$returnToUser = DB::table('chg_faci')->where('applylocid', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function chgloc(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = DB::table('chg_applyto')->get();
					return view('employee.masterfile.mfchgloc', ['data' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfchgloc');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					switch ($request->action) {
						case 'add':
							$returnToUser = DB::table('chg_applyto')->insert(['applytoLoc' => $request->name]);
							break;
						case 'edit':
							$returnToUser = DB::table('chg_applyto')->where('applytoid', $request->id)->update(['applytoLoc' => $request->ename]);
							break;
						case 'delete':
							$returnToUser = DB::table('chg_applyto')->where('applytoid', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		// license validity
		public function licenseValidity(Request $request) // Master File - Application Type
		{
			if ($request->isMethod('get')) 
			{
				try 
				{	
					$data = AjaxController::getAllApplicationType();
					// return dd($data);
					return view('employee.masterfile.mfLicenseValidity', ['hfstypes'=>$data]);	
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfLicenseValidity');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('hfaci_serv_type')->insert(['hfser_id' => $request->id, 'hfser_desc' => $request->name, 'seq_num' => $request->seq, 'terms_condi'=>$request->terms]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// APPLICATION TYPE
		////// APPLICATION STATUS
		public function AppStatus(Request $request) // Master File - Application Status
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllApplicationStatus();
					$data1 = AjaxController::getApplicationStatusWithoutRequired();
					$data2 = AjaxController::getApplicationStatusWithoutIsUpdate();
					// return dd($data);
					return view('employee.masterfile.mfApplicationStatus', ['apptype'=>$data, 'Stat' => $data1, 'isUpdate' => $data2]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfApplicationStatus');
				}
			}
			if ($request->isMethod('post')) 
			{ 
				try 
				{
					// HALA ANG ERROR, Palihug ko FIX PLEASE
					$test = DB::table('apptype')->where('aptid', '=', $request->id)->get();
					$NewSeq = 0;
					if (count($test) == 0) { // Check if the Same
						
						if (isset($request->upd)) {
							$test3 = DB::table('apptype')->where('aptid', '=', $request->upd)->first();
							if (isset($test3->apt_seq)) {
								$test4 =  explode('.', $test3->apt_seq);
								if(count($test4) > 1){
									$NewSeq = $test3->apt_seq + 0.1;
								} else {
									$NewSeq1 = $test3->apt_seq + 0.1;
									$NewSeq = $NewSeq1 + 0.1;
									$update = array('apt_seq' => $NewSeq1);
									$test5 = DB::table('apptype')->where('aptid',$test3->aptid)->update($update);
								}
							} 
							
						} else {
							$test2 = DB::table('apptype')->orderBy('apt_seq', 'desc')->first();
							// $NewSeq $test->
							if (isset($test2->apt_seq)) {
								$test5 =  explode('.', $test2->apt_seq);
								$NewSeq =  $test5[0] + 1;
							} else {
								$NewSeq = 1;
							}
							
						}
						// return $NewSeq;
						DB::table('apptype')->insert(['aptid' => $request->id, 'aptdesc' => $request->name, 'apt_reqid' => $request->req, 'apt_isUpdateTo' => $request->upd, 'apt_seq'=>$NewSeq]);
						return "DONE";
					} else {
						return 'SAME';
					}
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// APPLICATION STATUS
		////// CLASS
		public function Class(Request $request) // Master File - Class
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$ownership = AjaxController::getAllOwnership();
					$class = AjaxController::getAllClass();
					// return dd($class);
					return view('employee.masterfile.mfClass', ['own'=> $ownership, 'class'=> $class]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfClass');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$iSREMARKS = ($request->isRemarks == 1) ? $request->cls : null;

					DB::table('class')->insert(['classid' => $request->id, 'classname'=> $request->name, 'ocid' => $request->ocid, 'isSub'=> $iSREMARKS]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';		
				}
			}
		}
		////// CLASS
		////// HOLIDAYS
		public function Holidays(Request $request) // Master File - Holidays
		{
			if ($request->isMethod('get')) {
				try 
				{
					$data = AjaxController::getAllHolidays();
					return view('employee.masterfile.mfHolidays', ['holidays' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfHolidays');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$Cur_useData = AjaxController::getCurrentUserAllData();
					$rgn = ($Cur_useData['grpid'] == 'NA') ?  null : $Cur_useData['rgnid'];
					$test = DB::table('holidays')->insert([
							'hdy_id' => $request->code,
							'hdy_date' => $request->dat,
							'hdy_typ' => $request->typ,
							'hdy_desc' => $request->desc,
							'rgnid' => $rgn,
							't_time' => $Cur_useData['time'],
							't_date' => $Cur_useData['date'],
							't_ip' => $Cur_useData['ip'],
							't_added' => $Cur_useData['cur_user'],
						]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// HOLIDAYS
		////// OWNERSHIP
		public function Ownership(Request $request) 
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$ownership = AjaxController::getAllOwnership();;
					return view('employee.masterfile.mfOwnership', ['oShip' => $ownership]);
				}
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfOwnership');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('ownership')->insert(['ocid' => $request->id, 'ocdesc' => $request->name]);
					return 'DONE';
				} catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// OWNERSHIP
		////// FUNCTION
		public static function Functions(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllFunctions();
					return view('employee.masterfile.mfFunctions', ['functions' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfFunctions');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$id = DB::table('funcapf')->insertGetId(['funcdesc'=>$request->name]);
					return $id;
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// FUNCTION
		////// INSTITUTIONAL CHARACTER
		public function InstitutionalCharacter(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllInstitutionalCharacter();
					// return dd($data);
					return view('employee.masterfile.mfInstitutionalCharacter', ['AllData' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfInstitutionalCharacter');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$id = DB::table('facmode')->insertGetId(['facmdesc'=>$request->name]);
					return $id;
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// INSTITUTIONAL CHARACTER
		////// FACILITY
		public function Facility(Request $request)
		{
			if ($request->isMethod('get')) {
				try 
				{
					$data = AjaxController::getAllFacilityGroup();
					// return dd($data);
					return view('employee.masterfile.mfFacility', ['fatypes'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfFacility');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$id = DB::table('hfaci_grp')->insertGetId(['hgpdesc'=> trim($request->name)]);
					return $id;
				} 
				catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// FACILITY
		////// SERVICES
		public function Services(Request $request)
		{
			if ($request->isMethod('get')) {
				try 
				{
					$data = AjaxController::getAllServices();
					$data1 = AjaxController::getAllFacilityGroup();
					$data2 = AjaxController::getAllServiceType();
					$appType = AjaxController::getAllApplicationType();
					return view('employee.masterfile.mfServices', ['services'=>$data, 'facility' => $data1, 'servtype' => $data2, 'apptype' => $appType]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfServices');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					if($request->has('action')){
						switch ($request->action) {
							case 'dispalySpecified':
								$data = DB::table('facilitytyp')
										->leftJoin('hfaci_grp','hfaci_grp.hgpid','facilitytyp.hgpid')
										->leftJoin('serv_type','serv_type.servtype_id','facilitytyp.servtype_id')
										->select('facilitytyp.*','hfaci_grp.*','serv_type.servtype_id','serv_type.facid as servid','serv_type.grp_name','serv_type.seq','serv_type.anc_name')
										->where('facilitytyp.servtype_id', 1)
										->get();
								return json_encode($data);
								break;
						}
					}
					DB::table('facilitytyp')->insert(['facid' => $request->id, 'facname'=> $request->name, 'hgpid'=>$request->ocid, 'facmid' => 1, 'servtype_id' => $request->ocid1, 'specified' => $request->specified, 'forSpecialty' => $request->forSpecialty, 'hfser_id' => $request->hfser_id, 'grphrz_name' => $request->grpz, 'assignrgn' => $request->ocid2]);
					return 'DONE';
				} 
				catch (Exception $e) {
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function ServicesUpload(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$arrDet = [
					'type' => AjaxController::getAllFrom('hfaci_serv_type'),
					'facilities' => AjaxController::getAllFromWhere('facilitytyp',[['servtype_id',1]]),
					'data' => DB::table('facilitytypupload')->join('hfaci_serv_type','hfaci_serv_type.hfser_id','facilitytypupload.hfser_id')->join('facilitytyp','facilitytyp.facid','facilitytypupload.facid')->get()
					];
					return view('employee.masterfile.mfserviceupload', $arrDet);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfserviceupload');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$table = 'facilitytypupload';
					switch ($request->action) {
						case 'add':

							if($request->hasFile('upload')){
								$uploadName = FunctionsClientController::uploadFile($request->upload)['fileNameToStore'];
								$returnToUser = DB::table($table)->insert(['facid' => $request->facid, 'hfser_id' => $request->hfser_id, 'filename' => $uploadName, 'displayname' => $request->dname]);
							}
							break;
						case 'edit':
							if($request->hasFile('upload')){
								// AjaxController::deleteUploadedOnPublic($request->oldFilename);
								$uploadName = FunctionsClientController::uploadFile($request->upload)['fileNameToStore'];
							} else {
								$uploadName = $request->oldFilename;
							}
							$returnToUser = DB::table($table)->where('facilitytypUploadid',$request->id)->update(['facid' => $request->editfacid, 'hfser_id' => $request->edithfser_id, 'filename' => $uploadName, 'displayname' => $request->editdname]);
							break;
						case 'delete':
							// AjaxController::deleteUploadedOnPublic($request->oldFilename);
							$returnToUser = DB::table($table)->where('facilitytypUploadid', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// SERVICES
		////// MANAGE FACILITY
		public function ManageFacility(Request $request)
		{
			if ($request->isMethod('get')) {
				try 
				{
					$data = AjaxController::getAllApplicationType();
					$data1 = AjaxController::getAllFacilityGroup();
					return view('employee.masterfile.mfManageFacility', ['types'=>$data, 'facilitys'=>$data1]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfManageFacility');
				}
			}
			if ($request->isMethod('post')) {
				try 
				{
					$chckSameData = DB::table('type_facility')->where('hfser_id','=',$request->hfser_id)->where('facid','=',$request->facid)->first();
					if (!$chckSameData) {
							$id = DB::table('type_facility')->insertGetId(['hfser_id'=>$request->hfser_id, 'facid'=>$request->facid]);
							return $id;
					} else{
						return "SAME";
					}		
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function GroupFacility(Request $request)
		{
			if ($request->isMethod('get')) {
				try 
				{
					$data = AjaxController::getAllApplicationType();
					$data1 = AjaxController::getAllFacilityGroup();
					return view('employee.masterfile.mfGroupFacility', ['types'=>$data, 'facilitys'=>$data1]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfGroupFacility');
				}
			}
			if ($request->isMethod('post')) {
				try 
				{
					$chckSameData = DB::table('facl_grp')->where('hfser_id','=',$request->hfser_id)->where('hgpid','=',$request->facid)->first();
					if (!$chckSameData) {
							$id = DB::table('facl_grp')->insertGetId(['hfser_id'=>$request->hfser_id, 'hgpid'=>$request->facid]);
							return (string)$id;
					} else{
						return "SAME";
					}		
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function paymentLocation(Request $request)
		{
			if ($request->isMethod('get')) {
				try 
				{
					$data = AjaxController::getAllApplicationType();
					$data1 = AjaxController::getAllServices();
					$data2 = AjaxController::facilityPayment();
					$data3 = AjaxController::applyLocation();
					// dd([$data2,$data3]);
					return view('employee.masterfile.mfPaymentLocation', ['types'=>$data, 'facilitys'=>$data1, 'apploc' => $data2, 'paymentfaci' => $data3]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfPaymentLocation');
				}
			}
			if ($request->isMethod('post')) {
				try 
				{
					// return $request->all();
					$chckSameData = DB::table('chg_loc')->where('hfser_id','=',$request->hfser_id)->where('hgpid','=',$request->facid)->first();
					if (!$chckSameData) {
							$id = DB::table('chg_loc')->insertGetId(['hfser_id'=>$request->hfser_id, 'hgpid'=>$request->facid, 'applyLoc' => $request->apploc, 'paymentLoc' => $request->payfaci]);
							return (string)$id;
					} else{
						return "SAME";
					}		
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// MANAGE FACILITY
		////// MANAGE SERVICES
		public function ManageServices(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try // mfManageServices
				{
					return view('employee.masterfile.mfManageServices');
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfManageServices');
				}
			}
		}
		////// MANAGE SERVICES
		////// MANAGE REQUIREMENTS
		public function ManageRequirements(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllApplicationType();
					$data1 = AjaxController::getAllFacilityGroup();
					$data2 = AjaxController::getAllUploads();
					// return dd($data1);
					return view('employee.masterfile.mfManageRequirements', ['types'=>$data, 'facilitys'=>$data1, 'uploads'=>$data2]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfManageRequirements');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$Same = DB::table('facility_requirements')
							->where([
									['typ_id', '=', $request->typ_id],
									['upid', '=', $request->upid]
								])->get();
					if (count($Same) == 0) {
						$test = DB::table('facility_requirements')->insertGetId(['typ_id' => $request->typ_id, 'upid'=> $request->upid]);
						return $test;
					} else { return 'SAME';}
				} 
				catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// MANAGE REQUIREMENTS
		////// TRANSACTION STATUS
		public function TransactionStatus(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllTransactionStatus();
					// return dd($data);
					return view('employee.masterfile.mfTransaction', ['trans'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfTransaction');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$array = [
						'trns_id' => $request->id,
						'trns_desc' => $request->name,
						'allowedpayment' => $request->allowed,
						'canapply' => $request->apply,
						'allowedlegend' => $request->legend
					];
					if($request->legend == 1){
						$array['color'] = $request->color;
					}
					DB::table('trans_status')->insert($array);	
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// TRANSACTION STATUS
		////// UPLOADS
		public function Uploads(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllUploads();
					return view('employee.masterfile.mfUploads', ['uploads'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfUploads');
				}
			}
			if ($request->isMethod('post')) 
			{
				try {
					$id = DB::table('upload')->insert(['updesc'=>$request->name,'isRequired'=>$request->required, 'office' => ($request->office ?? 'hfsrb')]);
					return (string)$id;
				} catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// UPLOADS
		////// DEPARTMENT
		public function Department(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllDepartments();
					return view('employee.masterfile.mfDepartment',['depts'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfDepartment');
				}
			}
			if ($request->isMethod('post')) 
			{
				try {
					DB::table('department')->insert(['depid' => $request->id, 'depname' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';	
				}
			}
		}
		////// DEPARTMENT
		////// SECTION
		public function Section(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllDepartments();
					$data1 = AjaxController::getAllSections();
					return view('employee.masterfile.mfSection',['depts'=>$data, 'secs'=>$data1]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfSection');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('section')->insert(['secid' => $request->id, 'secname' => $request->name, 'depid' => $request->depid]);
					return 'DONE';
				} 
				catch (Exception $e) {
					AjaxController::SystemLogs($e);
				  	return 'ERROR';	
				}
			}
		}
		////// SECTION
		////// WORK
		public function Work(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllWorks();
					$data1 = AjaxController::getAllPositions();
					return view('employee.masterfile.mfWork', ['works'=>$data1]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfWork');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					// DB::table('pwork')->insert(['pworkid' => $request->id, 'pworkname' => $request->name]);
					DB::table('position')->insert(['posname' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
				  	return 'ERROR';
				}
			}
		}
		////// WORK 
		////// WORK STATUS
		public function WorkStatus(Request $request)
		{
			if ($request->ismethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllWorkStatus();
					return view('employee.masterfile.mfWorkStatus', ['pwStats'=>$data]);
				}
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfWorkStatus');
				}
			}
			if ($request->ismethod('post')) 
			{
				try {
					DB::table('pwork_status')->insert(['pworksid' => $request->id, 'pworksname' => $request->name]);
					return 'DONE';
				} catch (Exception $e) {
					AjaxController::SystemLogs($e);
				  	return 'ERROR';	
				}
			}
		}
		////// WORK STATUS
		////// LICENSE TYPE
		public function LicenseType(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllLicenseType();
					return view('employee.masterfile.mfLicenseType', ['plitype'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfLicenseType');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('plicensetype')->insert(['plid' => $request->id, 'pldesc' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
				  	return 'ERROR';	
				}
			}
		}
		////// LICENSE TYPE
		////// TRAINING
		public function Training(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllTrainings();
					return view('employee.masterfile.mfTraining', ['train'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfTraining');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('ptrainings_trainingtype')->insert(['tt_id'=>$request->id,'ptdesc'=>$request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function branch(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllRegion();
					$inBranches = AjaxController::getAllBranch(2);
					$notInBranches = AjaxController::getAllBranch(1);
					return view('employee.masterfile.mfBranch', ['region'=>$data, 'inBranch' => $inBranches,'notIn' => $notInBranches]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfBranch');
				}
			}
			if ($request->ismethod('post')) 
			{
				try 
				{
					if($request->has('action')){
						if($request->action == 'edit'){
							// return $request->all();
							$test = DB::table('branch')->where('regionid',$request->id)->update(['name' => $request->tname, 'directorInRegion' => $request->dir, 'PTC' => $request->ptc, 'LTO' => $request->lto, 'COR' => $request->cor, 'CON'=>$request->con, 'conBed' => $request->conBed, 'ATO' => $request->ato, 'COA' => $request->coa, 'pos' => $request->pos, 'orprint_x' => $request->orx, 'orprint_y' => $request->ory, 'certificateName' => $request->cname]);
						} else {
							$test = DB::table('branch')->where('regionid',$request->id)->delete();
						}
					} else {
						$test = DB::table('branch')->insert(['regionid' => $request->rgnid, 'name' => $request->tname, 'directorInRegion' => $request->dir, 'PTC' => $request->ptc, 'LTO' => $request->lto, 'COR' => $request->cor, 'CON'=>$request->con, 'conBed' => $request->conBed, 'ATO' => $request->ato, 'COA' => $request->coa, 'pos' => $request->pos, 'orprint_x' => $request->orx, 'orprint_y' => $request->ory, 'certificateName' => $request->cname]);
					}
					return ($test ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function notificationMessages(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$notifAll = DB::table('notification_msg')->get();
					return view('employee.masterfile.mfNotification', compact('notifAll'));
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfNotification');
				}
			}
			if ($request->ismethod('post')) 
			{
				try 
				{
					if($request->has('action')){
						if($request->action == 'edit'){
							// return $request->all();
							$test = DB::table('notification_msg')->where('msg_code',$request->msg_code)->update(['msg_desc' => $request->msg_desc]);
						} else {
							$test = DB::table('notification_msg')->where('regionid',$request->id)->delete();
						}
					} else {
						$test = DB::table('notification_msg')->insert(['msg_loc' => $request->msg_loc, 'msg_desc' => $request->msg_desc, 'needappid' => $request->needappid]);
					}
					return ($test ? back()->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Edited Successfully.']) : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// TRAINING
		////// REGIONS
		public function Regions(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllRegion();
					return view('employee.masterfile.mfRegion', ['region'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfRegion');
				}
			}
			if ($request->ismethod('post')) 
			{
				try 
				{
					$last = DB::table('region')->max('sort') + 1;
					DB::table('region')->insert(['rgnid' => $request->id, 'rgn_desc' => $request->name, 'office' => $request->office, 'director' => $request->director, 'directorDesc' => $request->directorDesc, 'sort' => $last]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// REGIONS
		////// PROVINCES
		public function Provinces(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllProvince();
					$data1 = AjaxController::getAllRegion();
					return view('employee.masterfile.mfProvince', ['province'=>$data],['region'=>$data1]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfProvince');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('province')->insert(['rgnid' => $request->id, 'provname' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// PROVINCES
		////// CITY/MUNICIPALITIES
		public function CityMunicipalities(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllRegion();
					$data1 = AjaxController::getAllProvince();
					$data2 = AjaxController::getAllCityMunicipality();
					return view('employee.masterfile.mfCityMunicipality', ['region'=>$data,'province'=>$data1,'cm'=>$data2]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfCityMunicipality');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('city_muni')->insert(['provid' => $request->id, 'cmname' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// CITY/MUNICIPALITIES
		////// BARANGAY
		public function Barangay(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllRegion();
					$data1 = AjaxController::getAllProvince();
					$data2 = AjaxController::getAllCityMunicipality();
					$data3 = AjaxController::getAllBarangay();
					return view('employee.masterfile.mfBarangay',['region'=>$data, 'province'=>$data1, 'cm'=>$data2, 'brgy' => $data3]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfBarangay');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('barangay')->insert(['cmid' => $request->id, 'brgyname' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		} 
		////// BARANGAY
		////// ORDER OF PAYMENT
		public function OrderOfPayment(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllOrderOfPayment();
					return view('employee.masterfile.mfOrderOfPayment',['oops'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfOrderOfPayment');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('orderofpayment')->insert(['oop_id' => $request->id, 'oop_desc' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// ORDER OF PAYMENT
		////// CATEGORY
		public function Category(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllCategory();
					return view('employee.masterfile.mfCategory', ['category'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfCategory');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('category')->insert(['cat_id' => $request->id, 'cat_desc' => $request->name, 'cat_type' => $request->type]);	
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// CATEGORY
		////// CHARGES
		public function Charges(Request $request)
		{
			if ($request->ismethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllChargesWithCategory();
					$data1 = AjaxController::getAllCategory();
					$data2 = AjaxController::getAllFacilityGroup();
					return view('employee.masterfile.mfCharges', ['Chrges'=>$data,'Categorys'=>$data1,'Facility'=>$data2]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfCharges');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('charges')->insert(['chg_code'=> strtoupper($request->id), 'cat_id' => $request->cat_id, 'chg_desc'=> $request->name, 'chg_exp' => $request->exp,'chg_rmks' => $request->rmk,'hgpid' => $request->hgpid, 'fprevision' => $request->isAssess]);
					return 'DONE';		
				} 
				catch (Exception $e) {
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// CHARGES
		////// MANAGE CHARGES
		public function ManageCharges(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllManageCharge();
					$data1 = AjaxController::getAllOrderOfPayment();
					$data2 = AjaxController::getAllCharges();
					$data3 = AjaxController::getAllApplicationStatus();
					$data4 = AjaxController::getAllCategory();
					$data5 = AjaxController::getAllApplicationType();
					return view('employee.masterfile.mfManageCharges', ['OOPs'=>$data1, 'Chrgs' => $data2, 'BigData' => $data, 'TotalNumber' => count($data), 'IniRen' => $data3,'Cats' => $data4, 'Hfaci'=>$data5]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfManageCharges');					
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$data = DB::table('chg_app')->select('chgopp_seq')->where('oop_id', '=', $request->oop_id)->orderBy('chgopp_seq','desc')->first();
					if (!$data) {$data1 = 1;}
					else { $data1 = $data->chgopp_seq + 1;}
					DB::table('chg_app')->insert(['chg_code'=>$request->chg_code,'oop_id'=>$request->oop_id,'chgopp_seq'=>$data1,'amt'=>0,'aptid'=>$request->aptid,'remarks'=>$request->rmk, 'hfser_id'=>$request->hfser_id]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// MANAGE CHARGES
		////// MODE OF PAYMENT
		public function ModeofPayment(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllModeOfPayment();
					return view('employee.masterfile.mfModeOfPayment', ['Chgs'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfModeOfPayment');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('charges')->insert(['chg_code' => $request->id, 'cat_id' => 'PMT', 'chg_desc' => $request->name, 'forWhom' => $request->forWhom]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function fdaRanges(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::FDACharges();
					return view('employee.masterfile.mfFDACharges', ['Chgs'=>$data]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfFDACharges');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					switch ($request->action) {
						case 'add':
							$returnToUser = DB::table('fdarange')->insert([
								'fchg_desc' => $request->cdescadd, 
								'rangeFrom' => $request->rangeFrom, 
								'rangeTo' => $request->rangeTo, 
								'initial_amnt' => $request->iaadd,
								'renewal_amnt' => $request->raadd,
								'renewal_1' => $request->fadd,
								'renewal_2' => $request->sadd,
								'renewal_3' => $request->tadd,
								'renewal_4' => $request->fadd,
								'renewal_5' => $request->ffadd,
								'type' => $request->typeadd
							]);
							break;
						case 'edit':
							$returnToUser = DB::table('fdarange')->where('id', $request->idtoEdit)->update([
								'fchg_desc' => $request->cdescadd, 
								'rangeFrom' => $request->rangeFrom, 
								'rangeTo' => $request->rangeTo, 
								'initial_amnt' => $request->iaadd,
								'renewal_amnt' => $request->raadd,
								'renewal_1' => $request->fadd,
								'renewal_2' => $request->sadd,
								'renewal_3' => $request->tadd,
								'renewal_4' => $request->fadd,
								'renewal_5' => $request->ffadd,
								'type' => $request->typeadd
							]);
							break;
						case 'delete':
							$returnToUser = DB::table('fdarange')->where('id', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function xraymachine(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = DB::table('fda_xraymach')->get();
					return view('employee.masterfile.mfxraymachine', ['mach'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfxraymachine');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					switch ($request->action) {
						case 'add':
							$returnToUser = DB::table('fda_xraymach')->insert(['xraydesc' => $request->desc]);
							break;
						case 'edit':
							$returnToUser = DB::table('fda_xraymach')->where('xrayid', $request->id)->update(['xraydesc' => $request->desc]);
							break;
						case 'delete':
							$returnToUser = DB::table('fda_xraymach')->where('xrayid', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function xraylocation(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = DB::table('fda_xraylocation')->get();
					return view('employee.masterfile.mfxrayloca', ['mach'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfxrayloca');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					switch ($request->action) {
						case 'add':
							$returnToUser = DB::table('fda_xraylocation')->insert(['locdesc' => $request->desc]);
							break;
						case 'edit':
							$returnToUser = DB::table('fda_xraylocation')->where('locid', $request->id)->update(['locdesc' => $request->desc]);
							break;
						case 'delete':
							$returnToUser = DB::table('fda_xraylocation')->where('locid', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function forAmbulance(Request $request)
		{
			if ($request->isMethod('get')) {
				try 
				{
					$arrRet = [
						'data' => AjaxController::getForAmbulanceList(),
						'faciType' => AjaxController::getAllFacilityGroup()
					];
					return view('employee.masterfile.mfForAmbulance', $arrRet);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfForAmbulance');
				}
			}
			if ($request->isMethod('post')) 
			{
				if(!isset($request->action)){
					try 
					{
						$id = DB::table('forAmbulance')->insertGetId(['hgpid'=> $request->name]);
						return $id;
					} 
					catch (Exception $e) {
						AjaxController::SystemLogs($e);
						return 'ERROR';
					}
				} else if(strtolower($request->action) == 'delete'){
					return DB::table('forAmbulance')->where('ambid',$request->id)->delete();
				}
			}
		}

		public function xraycat(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = DB::table('fda_xraycat')->get();
					return view('employee.masterfile.mfxraycat', ['mach'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfxraycat');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					switch ($request->action) {
						case 'add':
							$returnToUser = DB::table('fda_xraycat')->insert(['catdesc' => $request->desc]);
							break;
						case 'edit':
							$returnToUser = DB::table('fda_xraycat')->where('catid', $request->id)->update(['catdesc' => $request->desc]);
							break;
						case 'delete':
							$returnToUser = DB::table('fda_xraycat')->where('catid', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function xrayserv(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = DB::table('fda_xrayserv')
							->Join('fda_xraycat','fda_xraycat.catid','fda_xrayserv.catid')
							->get();
					$cat = DB::table('fda_xraycat')->get();
					return view('employee.masterfile.mfxrayserv', ['mach'=>$data, 'cat' => $cat]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfxrayserv');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					switch ($request->action) {
						case 'add':
							$returnToUser = DB::table('fda_xrayserv')->insert(['servdesc' => $request->desc, 'catid' => $request->catid]);
							break;
						case 'edit':
							$returnToUser = DB::table('fda_xrayserv')->where('servid', $request->id)->update(['servdesc' => $request->desc, 'catid' => $request->catid]);
							break;
						case 'delete':
							$returnToUser = DB::table('fda_xrayserv')->where('servid', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function xrayReq(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = DB::table('cdrrhrrequirements')->get();
					return view('employee.masterfile.mfxrayReq', ['mach'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfxrayReq');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$arrFields = ['reqName' => $request->reqName, 'isRequired' => ($request->isRequired ? 1 : 0)];
					switch ($request->action) {
						case 'add':
							$returnToUser = DB::table('cdrrhrrequirements')->insertGetId($arrFields);
							break;
						case 'edit':
							$returnToUser = DB::table('cdrrhrrequirements')->where('reqID', $request->id)->update($arrFields);
							break;
						case 'delete':
							$returnToUser = DB::table('cdrrhrrequirements')->where('reqID', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function uacs(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllUACS();
					return view('employee.masterfile.mfUACS', ['Chgs'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfUACS');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					// return $request->all();
					switch ($request->action) {
						case 'add':
							$returnToUser = DB::table('m04')->insert(['m04ID' => $request->id, 'm04Desc' => $request->name]);
							break;
						case 'edit':
							$returnToUser = DB::table('m04')->where('m04IDA', $request->id)->update(['m04Desc' => $request->name,'m04ID' => $request->m04id]);
							break;
						case 'delete':
							$returnToUser = DB::table('m04')->where('m04IDA', $request->id)->delete();
							break;
					}
					
					return ($returnToUser >=1 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// MODE OF PAYMENT
		////// DEFAULT PAYMENT
		public function DefaultPayment(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllDefaultPayment();
					$data1 = AjaxController::getAllFacilityGroup(); // Facility Group
					$data2 = AjaxController::getAllOrderOfPayment();
					$data3 = AjaxController::getAllApplicationStatus();
					$data4 = AjaxController::getAllApplicationType();
					$data5 = AjaxController::getAllPaymentWithCharges();
					// return dd($data);
					return view('employee.masterfile.mfDefaultPayment', ['default'=>$data, 'facilitys' =>$data1, 'OOP' => $data2, 'aptype' => $data3, 'app' => $data4, 'payment' => $data5]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfDefaultPayment');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('facoop')->insert([
						'oop_id'=>$request->oop, 
						'hfser_id'=>$request->hfser_id,
						'facid'=>$request->facid,
						'aptid'=>$request->aptid,
						'chgapp_id'=>$request->chg_app
					]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// DEFAULT PAYMENT
		////// SERVICE CHARGES
		public static function ServiceCharges(Request $request)
		{
			// if ($request->isMethod('get')) 
			// {
				try  // mfServiceCharges
				{
					if ($request->isMethod('post')) 
					{
						try 
						{
							$test = DB::table('serv_chg')->where('facid', '=', $request->facid)->where('chgapp_id', '=', $request->id)->first();
							// if (!$test) {
								DB::table('serv_chg')->insert(['facid' => $request->facid, 'chgapp_id' => $request->id, 'hfser_id' => $request->faciType, 'facmid' => $request->facmid, 'extrahgpid' => $request->extrahgpid]);
								return 'DONE';
							// }
							// else {
							// 	return 'SAME';
							// }
						} 
						catch (Exception $e) 
						{
							AjaxController::SystemLogs($e);
							return 'ERROR';
						}
					}
					// $data = AjaxController::getAllServiceChargers();
					$wherIn = [6];
					$data1 = AjaxController::getAllOrderOfPayment();
					$data2 = AjaxController::getAllFacilityGroup();
					$facmode = AjaxController::getAllFrom('facmode');
					$hosp = DB::table('facilitytyp')->where([['servtype_id',1]])->whereIn('hgpid',$wherIn)->get();
					$facilities = DB::table('hfaci_serv_type')->get();
					// return dd($facilities);
					return view('employee.masterfile.mfServiceCharges', ['oop' =>$data1, 'faci' => $data2, 'facilities' => $facilities, 'facmode' => $facmode, 'hosp' => $hosp]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfServiceCharges');
				}
			// }
		}
		////// SERVICE CHARGES
		////// ASSESSMENT CATEGORY
		public function AssessmentCategory(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllAssessmentCategory();
					return view('employee.masterfile.mfAssessmentCategory', ['cat' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfAssessmentCategory');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('cat_assess')->insert(['caid'=> $request->id, 'categorydesc' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// ASSESSMENT CATEGORY
		////// ASSESSMENT SUBCATEGORY A
		public function SubCategoryA(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllAssessmentCategory();
					// return dd($data);
					return view('employee.masterfile.mfAssessmentSubCategoryA');
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfAssessmentSubCategoryA');
				}
			}
		}
		////// ASSESSMENT SUBCATEGORY A
		////// ASSESSMENT SUBCATEGORY B
		public function SubCategoryB(Request $request)
		{
			if ($request->isMethoD('get')) 
			{
				try 
				{
					return view('employee.masterfile.mfAssessmentSubCategoryB');
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfAssessmentSubCategoryB');
				}
			}
		}
		////// ASSESSMENT SUBCATEGORY B
		////// ASSESSMENT PART
		public function AssessmentPart(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllAssessmentPart();
					return view('employee.masterfile.mfAssessmentPart', ['parts' => $data]);
				} 
				catch (Exception $e)
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfAssessmentPart');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('part')->insert(['partid' => $request->id, 'partdesc' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// ASSESSMENT PART
		////// ASSESSMENT (Master File)
		public function AssessmentMf(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllAssessmentJoined();
					$data1 = AjaxController::getAllAssessmentPart();
					$data2 = AjaxController::getAllFacilityGroup();
					$data3 = AjaxController::getAllAssessmentCategory();
					return view('employee.masterfile.mfAssessment', ['asments'=>$data, 'parts'=>$data1, 'faci'=>$data2,'cat'=> $data3]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfAssessment');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('assessment')->insert(['asmt_name' => $request->name, 'partid' => $request->partid, 'facid' => $request->faci, 'caid' => $request->caid]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		} 
		////// ASSESSMENT (Master File)
		////// ASSESSMENT 2 (Master File)
		public static function Assessment2MF(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllAssement2();
					$data1 = AjaxController::getAllHeader();
					$data2 = AjaxController::getAllSubDescription();
					// $data3 = AjaxController::getAllAssessmentTitle();
					// , 'parts'=>$data1, 'faci'=>$data2,'cat'=> $data3
					// return dd($data);
					return view('employee.masterfile.mfAssessment2', ['asments'=>$data, 'header' => $data1, 'sdesc'=>$data2]/*, 'title' => $data3]*/);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfAssessment2');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$data = DB::table('asmt2')->where('asmt2_id', '=', $request->id)->first();
					if (!$data) {
						DB::table('asmt2')->insert([
													'asmt2_id' => $request->id, 
													// 'asmt2_title' => $request->title, 
													'asmt2_loc' => $request->header,
													'asmt2_desc' => $request->desc,
													'asmt2sd_id' => $request->sub_desc,
												]);
						return 'DONE';
					} else {
						return 'SAME';
					}
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// ASSESSMENT 2 (Master File)
		//////
		public static function AssessmentSubDesc2MF(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllSubDescription();
					// return dd($data);
					return view('employee.masterfile.mfSubDesc', ['sdesc'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfSubDesc');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('asmt2_sdsc')->insert(['asmt2sd_id' => $request->id, 'asmt2sd_desc' => $request->desc]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// ASSESSMENT Column
		public static function AssessmentColumn(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try // mfAssessmentColumn
				{
					$data = AjaxController::getAllColumn();
					return view('employee.masterfile.mfAssessmentColumn', ['colmn' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfAssessmentColumn');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('asmt2_col')->insert(['asmt2c_id' => $request->id, 'asmt2c_desc' => $request->desc, 'asmt2c_type'=> $request->inputype]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// MANAGE ASSESSMENT
		public static function ManageAssessment(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllManageAssessment();
					$data1 = AjaxController::getAllFacilityGroup();
					$data2 = AjaxController::getAllAssessment();
					// return dd($data);
					return view('employee.masterfile.MngAssessment', ['MngAsmt' => $data, 'Facilitys' => $data1, 'Asmt' => $data2]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.MngAssessment');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('facassessment')->insert(['asmt_id'=>$request->asmt_id, 'facid' => $request->hgpid]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// MANAGE ASSESSMENT
		////// MANAGE ASSESSMENT 2
		public static function ManageAssessment2(Request $request) 
		{
			if ($request->isMethod('get')) 
			{
				try //mfManageAssessment2
				{
					$data = AjaxController::getAllApplicationType();
					$data1 = AjaxController::getAllAssement2();
					$data2 = AjaxController::getAllColumn();
					$data3 = AjaxController::getAllAssessmentTitle();
					// return dd($data3);
					return view('employee.masterfile.mfManageAssessment2', ['hfaci_serv_type'=>$data, 'asments' => $data1, 'colmn' => $data2, 'part' => $data3]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfManageAssessment2');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					if (isset($request->facid)) { // With Service
						$test = DB::table('serv_asmt')->where([['facid', '=', $request->facid], ['asmt2_id', '=', $request->asmt2_id], ['hfser_id', '=', $request->hfser_id], ['hgpid', '=', $request->hgpid], ['part_id', '=', $request->part_id]])->first();
						if (!isset($test)) // Check if has Duplicate
						{ 
							$data = DB::table('serv_asmt')->where([['facid', '=', $request->facid], ['hfser_id', '=', $request->hfser_id], ['hgpid', '=', $request->hgpid], ['part_id', '=', $request->part_id]])->orderBy('srvasmt_seq', 'desc')->first();
							$seq = 0; // Check Numbers
							if (isset($data->srvasmt_seq)) {
								$seq = ((int)$data->srvasmt_seq) + 1;
							} else {
								$seq = 1;
							}

							DB::table('serv_asmt')->insert(['facid'=> $request->facid, 'asmt2_id' => $request->asmt2_id, 'hfser_id' => $request->hfser_id, 'srvasmt_seq' => $seq, 'hgpid' => $request->hgpid, 'hasRemarks' => $request->hasRemarks, 'srvasmt_col' => json_encode($request->clm), 'part' => $request->part, 'part_id' => $request->part_id]);
							return 'DONE';

						} 
						else
						{
							return 'DUPLICATE';
						}
					}
					else  // Without Service
					{
						$test = DB::table('serv_asmt')->where([['facid', '=', null], ['asmt2_id', '=', $request->asmt2_id], ['hfser_id', '=', $request->hfser_id], ['hgpid', '=', $request->hgpid]])->first();
						if (!isset($test))  // Check if has Duplicate
						{
							$data = DB::table('serv_asmt')->where([['facid', '=', null], ['hfser_id', '=', $request->hfser_id], ['hgpid', '=', $request->hgpid], ['part_id', '=', $request->part_id]])->orderBy('srvasmt_seq', 'desc')->first();
							$seq = 0; // Check Numbers
							if (isset($data->srvasmt_seq)) {
								$seq = ((int)$data->srvasmt_seq) + 1;
							} else {
								$seq = 1;
							}
							DB::table('serv_asmt')->insert(['facid'=> null, 'hgpid' => $request->hgpid, 'asmt2_id' => $request->asmt2_id, 'hfser_id' => $request->hfser_id, 'srvasmt_seq' => $seq, 'hasRemarks' => $request->hasRemarks, 'srvasmt_col' => json_encode($request->clm), 'part' => $request->part, 'part_id' => $request->part_id]);
							return 'DONE';
						}
						else
						{
							return 'DUPLICATE';
						}
					}
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function previewAssessment(Request $request){
			if ($request->isMethod('get')) 
			{
				try 
				{
					$title = DB::table('asmt_title')->get();
					$fac = DB::table('facilitytyp')->where('servtype_id',1)->get();
					return view('employee.masterfile.mfAssessmentPreview', ['titles' => $title, 'fac' => $fac]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.previewAssessment');
				}
			}
		}

		public function previewAssessmentUnmanage(Request $request){
			try {
				// dd('qwe');
				$asmt2_loc = array();
				$countColoumn = DB::SELECT("SELECT count(*) as 'all' FROM information_schema.columns WHERE table_name = 'asmt2'")[0]->all -1;
				$sql = "SELECT * FROM `asmt2` LEFT JOIN `asmt2_loc` ON asmt2.asmt2_loc = asmt2_loc.asmt2l_id LEFT JOIN `asmt2_sdsc` ON asmt2.asmt2sd_id = asmt2_sdsc.asmt2sd_id WHERE `asmt2_id` NOT IN (SELECT `asmt2_id` FROM `serv_asmt`)";
				$joinedData = DB::select($sql);
				foreach ($joinedData as $data) {
					if($countColoumn){
						for ($i=1; $i <= $countColoumn ; $i++) {
							$actualHeader = 'header_lvl'.$i;
							if(Schema::hasColumn('asmt2_loc', $actualHeader))
							{
								if($data->$actualHeader !== NULL){
									if(!in_array($data->$actualHeader, $asmt2_loc)){
										array_push($asmt2_loc, $data->$actualHeader);
									}
								}
							}
						}
					}
				}
				foreach ($asmt2_loc as $locData) {
	            	$dataAll = DB::table('asmt2_loc')->where('asmt2l_id' , '=', $locData)->select('asmt2l_desc')->get()->first();
	            	$joinedData[$locData.'Desc'] = $dataAll->asmt2l_desc;
	            	$dataAll = null;
				}
				// dd($joinedData);
				return view('employee.masterfile.mfAssessmentPreviewUnmanaged', ['data' => $joinedData]);
			} catch (Exception $e) {
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.masterfile.mfAssessmentPreviewUnmanaged');
			}
		}

		public function previewAssessmentwithTitle(Request $request, $title){
			if ($request->isMethod('get')) 
			{
				try 
				{	
					$onWhere = 'part';
					if(DB::table('asmt_title')->where('title_code',$title)->doesntExist()){
						if(DB::table('facilitytyp')->where('facid',$title)->doesntExist()){
							return back()->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'Item does not exist.']);
						} else {
							$onWhere = 'facid';
						}
					}
					$asmt2_col = $asmt2_loc = array();
					$countColoumn = DB::SELECT("SELECT count(*) as 'all' FROM information_schema.columns WHERE table_name = 'asmt2'")[0]->all -1;
					$joinedData = DB::table('serv_asmt')
					->leftJoin('asmt_title','asmt_title.title_code','serv_asmt.part')
					->leftJoin('hfaci_serv_type','hfaci_serv_type.hfser_id','serv_asmt.hfser_id')
					->leftJoin('hfaci_grp','hfaci_grp.hgpid','serv_asmt.hgpid')
					->leftJoin('facilitytyp','facilitytyp.facid','serv_asmt.facid')
					->leftJoin('asmt2','asmt2.asmt2_id','serv_asmt.asmt2_id')
					->leftJoin('asmt2_loc','asmt2_loc.asmt2l_id','asmt2.asmt2_loc')
					->leftJoin('asmt2_sdsc','asmt2_sdsc.asmt2sd_id','asmt2.asmt2sd_id')
					->orderBy('serv_asmt.srvasmt_seq','ASC')
					->where('serv_asmt.'.$onWhere,$title)
					->get();
					foreach ($joinedData as $data) {
			            if($countColoumn){
			            	for ($i=1; $i <= $countColoumn ; $i++) {
								$actualHeader = 'header_lvl'.$i;
								if($data->srvasmt_col !== null){
									foreach (json_decode($data->srvasmt_col) as $json) {
									 	if(!in_array($json, $asmt2_col)){
									 		array_push($asmt2_col, $json);
									 	}
									}
								}
								if(Schema::hasColumn('asmt2_loc', $actualHeader))
								{
									if($data->$actualHeader !== NULL){
										if(!in_array($data->$actualHeader, $asmt2_loc)){
											array_push($asmt2_loc, $data->$actualHeader);
										}
									}
								}
			            	}
			            }
					}
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
					// dd($joinedData);
					return view('employee.masterfile.mfAssessmentPreviewWithTitle', ['data' => $joinedData]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfAssessmentPreviewWithTitle');
				}
			}
		}

		////// MANAGE ASSESSMENT 2
		////// OTHERS (MASTER FILE)
		////// COMPLAINTS
		public static function Complaints(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllComplaints();
					// return dd($data);
					return view('employee.masterfile.mfComplaints', ['AllData'=> $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfComplaints');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('complaints')->insert(['cmp_desc' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// REQUEST FOR ASSISTANCE
		public static function RequestForAssistance(Request $request)
		{
			if ($request->isMethod('get')) 
			{ 
				try 
				{
					$data = AjaxController::getAllRequestForAssistance();
					return view('employee.masterfile.mfRequestforAssistance', ['AllData'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.mfRequestforAssistance');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('req_ast')->insert(['rq_desc' => $request->name]);
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// SYSTEM SETTINGS
		public function SystemSetting(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllSettings();
					return view('employee.masterfile.SystemSettings', ['BigData' => $data]);
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.masterfile.SystemSettings');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$update = array('mtny'=> $request->mtny, 'sec_name' => $request->sec_name, 'app_exp'=>$request->app_exp, 'acc_exp' => $request->acc_exp, 'pass_exp' => $request->pass_exp, 'pass_temp' => $request->pass_temp, 'pass_min' => $request->pass_min, 'pass_ban' => $request->pass_ban, 'no_tries' => $request->no_tries, 'appdeadline' => $request->appDead, 'neardeadline' => $request->nearDead, 'dohiso' => $request->isoCert);
					$data = DB::table('m99')->where('id', '=', 1)->update($update);
					if ($data) { return 'DONE'; } 
					else 
					{
						AjaxController::SystemLogs('No data updated in m99 table. (SystemSetting)');
						return 'ERROR';
					}	
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// SYSTEM SETTINGS
		///////////////////////////////////////////////// MASTER FILE
		///////////////////////////////////////////////// PROCESS FLOW
		////// VIEW ALL
		public function ViewProcessFlow(Request $request, $filter = false)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$arrType = array();
					$data = AjaxController::getAllApplicantsProcessFlow();
					if(!$filter){
						$allType = DB::table('hfaci_serv_type')->select('hfser_id')->get();
						foreach ($allType as $key) {
							array_push($arrType, $key->hfser_id);
						}
					} else {
						array_push($arrType, strtoupper($filter));
					}
					return view('employee.processflow.viewprocessflow', ['LotsOfDatas' => $data, 'serv' => $arrType]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.viewprocessflow');
				}
			}
		}

		public function listofpersonnel(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllApplicantsProcessFlow();
					// return dd($data);
					return view('employee.processflow.viewlistofpersonnel', ['LotsOfDatas' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.viewlistofpersonnel');
				}
			}
		}
		////// VIEW ALL
		////// EVALUATE
		public function EvaluateProcessFlow(Request $request)
		{
			try 
			{
				$data = AjaxController::getAllApplicantsProcessFlow();
				// dd(AjaxController::getCurrentUserAllData());
				return view('employee.processflow.pfevaluate', ['BigData'=>$data]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pfevaluate');
			}
		}

		public function EvaluateProcessFlowFDA(Request $request,$clientChoice = 'machines')
		{
			try 
			{
				$clientChoice = AjaxController::isRequestForFDA($clientChoice);
				$data = AjaxController::getAllApplicantsProcessFlow();
				return view('employee.FDA.pfevaluateFDA', ['BigData'=>$data,'request' => $clientChoice]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.FDA.pfevaluateFDA');
			}
		}

		public function pre_assessmentFDA(Request $request,$clientChoice = 'machines')
		{
			try 
			{
				$clientChoice = AjaxController::isRequestForFDA($clientChoice);
				$data = AjaxController::getAllApplicantsProcessFlow();
				return view('employee.FDA.pfpreassessment', ['BigData'=>$data,'request' => $clientChoice]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.FDA.FDA.pfevaluateFDA');
			}
		}
		////// EVALUATE
		////// EVALUATE ONE
		public function EvaluateOneProcessFlow(Request $request, $appid, $office = 'hfsrb')
		{
			$office = AjaxController::listsofapproved(['hfsrb','xray','pharma'],strtolower($office),'hfsrb');
			$forhfsrb = ($office == 'hfsrb');
			$boolFlag = false;
			$boolRedirect = true;
			// if(DB::table('appform')->where([['appid', $appid],['isrecommended',null]])->orWhere([['appid', $appid],['isrecommended',2]])->count() <= 0){
			// 	return redirect('employee/dashboard/processflow/evaluate');
			// }

			$data = AjaxController::getAllDataEvaluateOne($appid);
			$coaFlag = ($forhfsrb && strtolower($data->hfser_id) == 'coa');
			if ($request->isMethod('get')) 
			{
				$Cur_useData  = AjaxController::getCurrentUserAllData();
				$curForm = FunctionsClientController::getUserDetailsByAppform($appid);
				$documentDate = (isset($curForm[0]->documentSent) ? $curForm[0]->documentSent :  Date('Y-m-d',strtotime('now')));
				$linkToEdit = asset('client1/apply/employeeOverride/app/'.$data->hfser_id.'/'.$data->appid);
				$data8 = Carbon::parse($Cur_useData['date']);
				$data9 = Carbon::parse($Cur_useData['date']);
				$data10 = Carbon::parse($Cur_useData['date']);
				$data10 = $data10->addDays(30);
				$data8 = $data8->addDays(1);
				do {
					// $temp = $data8->toDateString();	
					if ($data8->isWeekday()) { // true
						$temp = $data8->toDateString();
						$check = DB::table('holidays')->where('hdy_date', '=', $temp)->first();
						if ($check) {
							$data8 = $data8->addDays(1);
							$test = false;
						} else {
							$test = true;
						}
					} else { // false
						$data8 = $data8->addDays(1);
						$test = false;
					}
				} while ($test == false);

				// for filtering per hfser
				switch (strtolower($data->hfser_id)) {
					case 'lto':
						$boolFlag = false;
						break;
					
					case 'coa':
						if($coaFlag && !isset($data->coaflag)){
							$boolFlag = true;
						} else {
							$boolRedirect = false;
						}
						break;
					default:
						$boolFlag = true;
					break;
				}

				// if( strtolower($data->hfser_id) != 'lto' /*|| $office != 'hfsrb'*/ /*&& ( $coaFlag && !isset($data->coaflag) ) */){
				if($boolFlag){
					try 
					{
						$data1 = AjaxController::getAllDataEvaluateOneUploads($appid, 0, $office);
						$data2 = AjaxController::getAllDataEvaluateOneUploads($appid, 1);
						$data3 = AjaxController::getAllDataEvaluateOneUploads($appid, 2);
						$data4 = AjaxController::getAllDataEvaluateOneUploads($appid, 3);
						$data5 = AjaxController::getAllDataEvaluateOneUploads($appid, 4);
						$data6 = AjaxController::getAllOrderOfPayment();
						// $data7 = AjaxController::getAllDataEvaluateOneUploads($appid, 5);
						
						
						$test = false;
						$isApproved = [1, null]; $isAllUpload = []; $isTrue = true;
						// dd($curForm);
						// if(count($curForm) > 0) {
						// 	foreach(FunctionsClientController::getReqUploads($curForm[0]->hfser_id, $appid) AS $each) {
						// 		if(! isset($each->filepath)) {
						// 			array_push($isAllUpload, $each->upid);
						// 		} else {
						// 			if(! in_array($each->evaluation, $isApproved)) {
						// 				if(in_array($curForm[0]->canapply, [1])) {
						// 					array_push($isAllUpload, $each->upid);
						// 				}
						// 			}
						// 		}
						// 	}
						// 	if(count($isAllUpload) < 1) {
						// 		$isTrue = true;
						// 	}
						// }
						
							
						// $clientUID = DB::select("SELECT `uid` FROM x08 WHERE uid IN (SELECT uid FROM appform WHERE appid = '$appid')")['0']->uid;
						// if(!session()->has('uData') && session()->get('uData')['uid'] != $clientUID){
						// 	session()->forget('uData');
						// 	$clientData = DB::select("SELECT * FROM x08 WHERE uid IN (SELECT uid FROM appform WHERE appid = '$appid')")['0'];
						// 	session()->put('uData',$clientData);
						// }
						$acceptedExt = array('pdf','jpg','png','jpeg','gif');
						return view('employee.processflow.pfevaluteone', ['AppData'=> $data, 'UploadData' => $data1, 'numOfX' => count($data2), 'numOfApp' => count($data3), 'numOfAprv'=> count($data4), 'numOfNull' => count($data5), 'OOPS'=>$data6, /*'OPPok' => $data7,*/ 'ActualString' => $data8->toDateString(), 'DateString' => $data8->toFormattedDateString(),'appID' => $appid, 'DateNow' => $data9->toDateString(), 'AfterDay'=> $data10->toDateString(), 'linkToEdit' => $linkToEdit, 'documentDate' => $documentDate/*,'allSent' => $isTrue*/, 'accepted' => $acceptedExt, 'forhfsrb' => $forhfsrb, 'office' => $office, 'coaFlag' => $coaFlag, 'redirect' => $boolRedirect]);
					} 
					catch (Exception $e) 
					{
						return $e;
						AjaxController::SystemLogs($e);
						session()->flash('system_error','ERROR');
						return view('employee.processflow.pfevaluteone');
					}
				} else {

					$tables = array();
					$arrTemp = [];
					$req = AjaxController::getAllRequirementsLTO($appid);
					if($office != 'hfsrb'){
						$adjustedName = ($office == 'pharma' ? 'CDRR' : 'CDRRHR');
						$req = AjaxController::getRequirementsFDA($appid);
						$count = count($req);
						if(isset($req)){
							for ($i=0; $i < $count; $i++) {
								if(isset($req[$i]) && ($req[$i][4] == $adjustedName) && $req[$i][2]->isNotEmpty()){
									array_push($arrTemp, $req[$i][3]);
								}
								if(isset($req[$i]) && ($req[$i][4] != $adjustedName)){
									unset($req[$i]);
								}
							}
						}
					}
					if(count($arrTemp) <= 0){
						foreach($req as $key => $datas){
							if(!in_array(trim($datas[3]), $tables)){
								array_push($tables, trim($datas[3]));
							}
						}
					} else {
						$tables = $arrTemp;
					}
					return view('employee.processflow.pfevaluateoneLTO', ['AppData'=> $data, 'requirements' => $req, 'appID' => $appid, 'documentDate' => $documentDate, 'linkToEdit' => $linkToEdit, 'ActualString' => $data8->toDateString(), 'DateString' => $data8->toFormattedDateString(),'appID' => $appid, 'DateNow' => $data9->toDateString(), 'AfterDay'=> $data10->toDateString(), 'tables' => json_encode($tables), 'forhfsrb' => $forhfsrb, 'office' => $office, 'coaFlag' => $coaFlag, 'redirect' => $boolRedirect]);
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					if($request->has('addUpload')){
						foreach ($request->addUpload as $key => $value) {
							DB::table('app_upload')->insert(['app_id' => $appid,'upid' => 1, 'upDesc' => $value, 'upDescRemarks' => $request->addUploadRemarks[$key]]);
						}
						$uid = AjaxController::getUidFrom($data->appid);
						$idForNotify = AjaxController::getNotificationIDfromCases($data->hfser_id,'additionalRequirments',1);
						AjaxController::notifyClient($data->appid,$uid,$idForNotify);
						return redirect('employee/dashboard/processflow/evaluate/'.$appid);
					}

					if(empty($request->checkFiles)){
						$addedby = session()->get('employee_login');
						$dt = Carbon::now();
			          	$dateNow = $dt->toDateString();
			          	$timeNow = $dt->toTimeString();
			          	for ($i=0; $i < count($request->ifChk) ; $i++) { 
			          		if (isset($request->ifChk[$i])) {
			          			$updateData = array(
									'evaluation'=>$request->ifChk[$i],
									'evaluatedBy' => $addedby->uid,
									'evaltime' => $timeNow, 
									'evaldate' => $dateNow,
									'remarks' => $request->ChkRmk[$i],
								);
								$test = DB::table('app_upload')->where('apup_id', '=', $request->ids[$i])->update($updateData);
			          		}
			          	}
			          	// return ($request->ifChk[4]);
						
						// 
						// return back();	
						
						return ($test ? 'DONE' : 'ERROR');
					} else {
						$test = DB::table('appform')->where('appid',$appid)->update(['documentSent' => Carbon::now()->toDateString()]);
						if($test){
							$uid = AjaxController::getUidFrom($appid);
							AjaxController::notifyClient($appid,$uid,23);
						}
						return $test;
					}

				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					// session()->flash('system_error','ERROR');
					return 'ERROR';
				}
			}
		}

		public function evaluateLTOReq(Request $request){
			try {
				$ret = DB::table($request->table)->where('appid',$request->appid)->update(['evaluation' => $request->eval, 'remarks' => $request->remarks]);
				if($ret){
					return 'done';
				} 
				// else {
				// 	return $request->all();
				// 	return 'error';
				// }
			} catch (Exception $e) {
				AjaxController::SystemLogs($e);
				return $e;
			}
		}

		////// EVALUATE ONE
		////// EVALUATE MODIFY
		public function EditEvaluationOneProcessFlow(Request $request, $appid)
		{
			if ($request->isMethod('get')) 
			{
				try  // pfevaluateeditone
				{
					return view('employee.processflow.pfevaluateeditone');
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfevaluteone');
				}
			}
		}
		// public function EvaluateOneProcessFlowFDA(Request $request, $appid)
		// {
		// 	if ($request->isMethod('get')) 
		// 	{
		// 		try 
		// 		{
		// 			$Cur_useData  = AjaxController::getCurrentUserAllData();
		// 			$data = AjaxController::getAllDataEvaluateOne($appid);
		// 			$curForm = FunctionsClientController::getUserDetailsByAppform($appid);
		// 			$documentDate = $curForm[0]->documentSent;
		// 			$linkToEdit = asset('client1/apply/employeeOverride/app/'.$data->hfser_id.'/'.$data->appid);
		// 			$data8 = Carbon::parse($Cur_useData['date']);
		// 			$data9 = Carbon::parse($Cur_useData['date']);
		// 			$data10 = Carbon::parse($Cur_useData['date']);
		// 			$data10 = $data10->addDays(30);
		// 			$data8 = $data8->addDays(1);
		// 			$tables = array();
		// 			$req = AjaxController::getRequirementsFDA($appid);
		// 			foreach($req as $key => $datas){
		// 				if(!in_array(trim($datas[3]), $tables)){
		// 					array_push($tables, trim($datas[3]));
		// 				}
		// 			}

		// 			do {	
		// 				if ($data8->isWeekday()) { // true
		// 					$temp = $data8->toDateString();
		// 					$check = DB::table('holidays')->where('hdy_date', '=', $temp)->first();
		// 					if ($check) {
		// 						$data8 = $data8->addDays(1);
		// 						$test = false;
		// 					} else {
		// 						$test = true;
		// 					}
		// 				} else { // false
		// 					$data8 = $data8->addDays(1);
		// 					$test = false;
		// 				}
		// 			} while ($test == false);	
		// 			return view('employee.FDA.pfevaluateoneFDA', ['AppData'=> $data, 'ActualString' => $data8->toDateString(), 'requirements' => $req, 'DateString' => $data8->toFormattedDateString(),'appID' => $appid, 'DateNow' => $data9->toDateString(), 'AfterDay'=> $data10->toDateString(), 'tables' => json_encode($tables)]);
		// 		} 
		// 		catch (Exception $e) 
		// 		{
		// 			AjaxController::SystemLogs($e);
		// 			session()->flash('system_error','ERROR');
		// 			return view('employee.FDA.pfevaluateeditoneFDA');
		// 		}
		// 	}
		// 	if ($request->isMethod('post')) 
		// 	{
		// 		try 
		// 		{
		// 			$addedby = session()->get('employee_login');
		// 			$dt = Carbon::now();
		//           	$dateNow = $dt->toDateString();
		//           	$timeNow = $dt->toTimeString();
		//           	for ($i=0; $i < count($request->ifChk) ; $i++) { 
		//           		if (isset($request->ifChk[$i])) {
		//           			$updateData = array(
		// 						'evaluation'=>$request->ifChk[$i],
		// 						'evaluatedBy' => $addedby->uid,
		// 						'evaltime' => $timeNow, 
		// 						'evaldate' => $dateNow,
		// 						'remarks' => $request->ChkRmk[$i],
		// 					);
		// 					DB::table('app_upload')->where('apup_id', '=', $request->ids[$i])->update($updateData);
		//           		}
		//           	}
		// 			return 'DONE';
		// 		} 
		// 		catch (Exception $e) 
		// 		{
		// 			AjaxController::SystemLogs($e);
		// 			return 'ERROR';
		// 		}
		// 	}
		// }

		public function EvaluateOneProcessFlowFDA(Request $request, $appid, $requestOfClient = 'machines')
		{
			if(in_array(true, AjaxController::isSessionExist(['employee_login'])) && FunctionsClientController::hasRequirementsFor((AjaxController::isRequestForFDA($requestOfClient) == 'machines' ? 'cdrrhr' : 'cdrr'),$appid)){
				$requestOfClient = AjaxController::isRequestForFDA($requestOfClient);
				if($request->isMethod('get')){
					try {
						$arrRet = [
							'choosen' => $requestOfClient,
							'AppData' => AjaxController::getAllDataEvaluateOne($appid),
							'eval' => DB::table('fdaevaluation')->where([['appid',$appid],['requestFrom',($requestOfClient == 'machines' ? 'machines' : 'pharma')]])->first(),
							'list' => AjaxController::getRequirementsFDA($appid),
							'appid' => $appid
						];
						// dd($arrRet);
						return view('employee.FDA.pfevaluateoneFDANew',$arrRet);
					} catch (Exception $e) {
						dd($e);
					}

				}

				if($request->isMethod('post')){
					$uData = AjaxController::getCurrentUserAllData();
					if($requestOfClient == 'machines'){
						$forAppform = [
							'isrecommendedFDA',
							'recommendedbyFDA',
							'recommendedtimeFDA',
							'recommendeddateFDA',
							'recommendedippaddrFDA',
							'FDAStatMach'
						];
					} else {
						$forAppform = [
							'isrecommendedFDAPharma',
							'recommendedbyFDAPharma',
							'recommendedtimeFDAPharma',
							'recommendeddateFDAPharma',
							'recommendedippaddrFDAPharma',
							'FDAStatPhar'
						];
					}

					$fstat = $requestOfClient == 'machines' ? 'For Recommendation' : 'For Final Decision';
					$answers = [
						1,
						$uData['cur_user'],
						$uData['time'],
						$uData['date'],
						$uData['ip'],
						$fstat
					];
					if($request->hasFile('fileUp')){
						$uploadName = FunctionsClientController::uploadFile($request->fileUp)['fileNameToStore'];
						$toInsertFieldsAndValue = array([
							'requestFrom' => $requestOfClient,
							'uploadfilename' => $uploadName,
							'decision' => $request->recommendation,
							'remarks' => $request->remarks,
							't_ip' => $request->ip(),
							't_eval' => session()->get('employee_login')->uid,
							'appid' => $appid
						]);

						
						$isRedirect = DB::table('fdaevaluation')->insert($toInsertFieldsAndValue);

						if($isRedirect){
							if(strtolower($request->recommendation) != 'rfc'){
								DB::table('appform')->where('appid',$appid)->update(array_combine($forAppform, $answers));
							}
							if($request->recommendation == 'COC'){
								$department = ($requestOfClient == 'machines' ? 'cdrrhr' : 'cdrr');
								DB::table('fdacert')->insert(['appid' => $appid, 'department' => $department, 'certtype' => 'COC', 'issueby' => session()->get('employee_login')->uid]);
							}
							return redirect('employee/dashboard/processflow/evaluate/FDA/'.$appid.'/'.$requestOfClient);
						}

					}
					if(array_key_exists('isupdate', $request->all())){
						$oldData = DB::table('fdaevaluation')->where([['appid',$appid],['requestFrom',$requestOfClient]])->first();
						if(isset($oldData)){
							DB::table('fdaevaluationhistory')->insert(['evaluation' => $oldData->decision, 'requestfrom' => $oldData->requestFrom, 'remarks' => $oldData->remarks, 'old_ip' => $oldData->t_ip, 'old_tdate' => $oldData->t_det, 't_ip' => $uData['ip'], 'old_teval' => $oldData->t_eval,  'appid' => $appid, 'changedby' => $uData['cur_user']]);
							if(DB::table('fdaevaluation')->where('evalid', $oldData->evalid)->update(['decision' => $request->recommendation, 't_det' => $uData['date'], 't_ip' => $uData['ip'], 't_eval' => $uData['cur_user']])){
								if(strtolower($request->recommendation) != 'rfc'){
									DB::table('appform')->where('appid',$appid)->update(array_combine($forAppform, $answers));
								}
								return back()->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Application Updated']);
							}
						}
					}
				}
			}
		}

		////// EVALUATE MODIFY
		////// ORDER OF PAYMENT
		public function OrderOfPaymentProcessFlow(Request $request)
		{
			try 
			{
				$data = AjaxController::getAllApplicantsProcessFlow();
				return view('employee.processflow.pforderofpayment', ['BigData'=>$data]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pforderofpayment');
			}
		}
		// FDA
		public function OrderOfPaymentProcessFlowFDA(Request $request, $requestOfClient = 'machines')
		{
			try 
			{
				$requestOfClient = AjaxController::isRequestForFDA($requestOfClient);
				$data = AjaxController::getAllApplicantsProcessFlow();
				return view('employee.FDA.pforderofpaymentFDA', ['BigData'=>$data, 'request' => $requestOfClient]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.FDA.pforderofpaymentFDA');
			}
		}
		////// ORDER OF PAYMENT
		////// ORDER OF PAYMENT ONE
		public function OrderOfPaymentOneProcessFlow(Request $request, $appid)
		{
				// if(DB::table('appform')->where([['appid', $appid],['isrecommended',1], ['isPayEval',null]])->count() <= 0){
				// 	return redirect('employee/dashboard/processflow/orderofpayment/');
				// }
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$data1 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,5);
					$data2 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,4);
					$data3 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,2);
					$data4 = AjaxController::getAllOrderOfPayment();
					$data5 = AjaxController::getAllDataOrderOfPaymentUploads(isset($data->aptid) ? $data->aptid : 'IN' ,3);
					$code = $data->hfser_id.'R'.$data->rgnid.'-'.$data->appid;
					$uacs = AjaxController::getAllUACS();
					// dd($data1);
					return view('employee.processflow.pforderofpaymentone',['AppData'=>$data, 'Payments' => $data1, 'Sum' => $data2, 'OOPs' =>$data4, 'Chrges' =>$data5, 'APPID' => $appid, 'code'=>$code, 'uacs' => $uacs]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pforderofpaymentone');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					if(empty($request->action)){
						$Cur_useData = AjaxController::getCurrentUserAllData();
				  		$getData = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->select('chg_num')->first();
				  		$test = DB::table('chgfil')->insert([
				  						'chgapp_id' => $request->id,
				  						'chg_num' => $getData->chg_num,
				  						'appform_id' => $request->appid,
				  						'reference' => $request->desc,
				  						'amount' => $request->amount,
				  						't_date' => $Cur_useData['date'],
				  						't_time' => $Cur_useData['time'],
				  						't_ipaddress' => $Cur_useData['ip'],
				  						'uid' => $Cur_useData['cur_user'],
				  						'sysdate' => $Cur_useData['date'],
				  						'systime' => $Cur_useData['time'],
				  					]);
				  		$upd = array('chg_num'=>(intval($getData->chg_num) + 1));
				  		$test2 = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->update($upd);
			  		}
			  		return ($test2 > 0 ? 'DONE' : 'ERROR');
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function OrderOfPaymentOneProcessFlowMachinesFDA(Request $request, $appid)
		{

			if(FunctionsClientController::hasRequirementsFor('cdrrhr',$appid)){
				if ($request->isMethod('get')) 
				{
					try 
					{
						$data = AjaxController::getAllDataEvaluateOne($appid);
						$getOrderOfPayment = DB::table('fda_chgfil')
						->leftJoin('fdarange','fda_chgfil.fchg_code','fdarange.id')
						->where([['appid',$appid],['amount', '>', 0]])
						->whereNotNull('MAvalue')
						->Orwhere([['appid',$appid],['amount', '>', 0],['uid','SYSTEM']])
						->get();
						$charges = DB::table('fdarange')->get();
						$sum = DB::table('fda_chgfil')
								->where([['appid',$appid],['amount', '>', 0]])
								->whereNotNull('MAvalue')
								->Orwhere([['appid',$appid],['amount', '>', 0],['uid','SYSTEM']])
								->sum('amount');
						// $lrf = ($sum / 100 > 10 ? $sum / 100 : 10);
						// $sum += $lrf;
						// dd($getOrderOfPayment);
						$canView = AjaxController::canViewFDAOOP($appid);
						$code = $data->hfser_id.'R'.$data->rgnid.'-'.$data->appid;
						return view('employee.FDA.pforderofpaymentoneFDA',['AppData' => $data, 'payables' => $getOrderOfPayment, 'code' => $code, 'Sum' => $sum, 'appid' => $appid, 'charges' => $charges, 'canView' => $canView, 'request' => 'Machines']);
					} 
					catch (Exception $e) 
					{
						dd($e);
						AjaxController::SystemLogs($e);
						session()->flash('system_error','ERROR');
						return view('employee.FDA.pforderofpaymentoneFDA');
					}
				}
				if ($request->isMethod('post')) 
				{
					try 
					{
						$Cur_useData = AjaxController::getCurrentUserAllData();
						if(empty($request->getCharge)){
					  		$getData = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->select('chg_num')->first();
					  		$test = DB::table('chgfil')->insert([
					  						'chgapp_id' => $request->id,
					  						'chg_num' => $getData->chg_num,
					  						'appform_id' => $request->appid,
					  						'reference' => $request->desc,
					  						'amount' => $request->amount,
					  						't_date' => $Cur_useData['date'],
					  						't_time' => $Cur_useData['time'],
					  						't_ipaddress' => $Cur_useData['ip'],
					  						'uid' => $Cur_useData['cur_user'],
					  						'sysdate' => $Cur_useData['date'],
					  						'systime' => $Cur_useData['time'],
					  					]);
					  		$upd = array('chg_num'=>(intval($getData->chg_num) + 1));
					  		$test2 = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->update($upd);
					  		return 'DONE';
				  		} elseif($request->getCharge == 'charges') {
				  			$choosen = $request->selected;
				  			return json_encode(DB::table('fdarange')->where('id',$request->id)->select($choosen)->first()->$choosen);
				  		} elseif($request->getCharge == 'newpayment'){
				  			$data = AjaxController::getAllDataEvaluateOne($appid);
				  			switch (true) {
								case ($data->aptid == 'IN'):
									$findIn = 'initial_amnt';
									break;
								case ($data->aptid == 'R'):
									$findIn = 'renewal_amnt';
									break;
							}
				  			$fdarange = DB::table('fdarange')->where('id',$request->id)->select($findIn)->first()->$findIn;
				  			$test = DB::table('fda_chgfil')->insert(['appid' => $appid, 'fchg_code' => $request->id, 'amount' => $fdarange, 't_date' => $Cur_useData['date'], 't_time' => $Cur_useData['time'], 'ipaddress' => $Cur_useData['ip'], 'uid' => $Cur_useData['cur_user']]);
				  			if($test){
				  				return 'done';
				  			}
				  		}
					} 
					catch (Exception $e) 
					{
						return $e;
						AjaxController::SystemLogs($e);
						return 'ERROR';
					}
				}
			} else {
				return redirect('employee/dashboard')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Application has no machines registered']);
			}
		}


		public function OrderOfPaymentOneProcessFlowPharmaFDA(Request $request, $appid)
		{
			if(FunctionsClientController::hasRequirementsFor('cdrr',$appid)){
				if ($request->isMethod('get')) 
				{
					try 
					{
						$data = AjaxController::getAllDataEvaluateOne($appid);
						$getOrderOfPayment = DB::table('fda_chgfil')
						->leftJoin('fdarange','fda_chgfil.fchg_code','fdarange.id')
						->where([['appid',$appid],['amount', '>', 0],['uid','<>','SYSTEM']])
						->whereNull('MAvalue')
						->get();
						$charges = DB::table('fdarange')->get();
						$sum = DB::table('fda_chgfil')->where([['appid',$appid],['amount', '>', 0],['uid', '<>', 'SYSTEM']])->whereNull('MAvalue')->sum('amount');
						// $lrf = ($sum / 100 > 10 ? $sum / 100 : 10);
						// $sum += $lrf;
						// dd($getOrderOfPayment);
						$canView = AjaxController::canViewFDAOOP($appid);
						$code = $data->hfser_id.'R'.$data->rgnid.'-'.$data->appid;
						return view('employee.FDA.pforderofpaymentoneFDA',['AppData' => $data, 'payables' => $getOrderOfPayment, 'code' => $code, 'Sum' => $sum, 'appid' => $appid, 'charges' => $charges, 'canView' => $canView, 'request' => 'Pharma']);
					} 
					catch (Exception $e) 
					{
						dd($e);
						AjaxController::SystemLogs($e);
						session()->flash('system_error','ERROR');
						return view('employee.FDA.pforderofpaymentoneFDA');
					}
				}
				if ($request->isMethod('post')) 
				{
					try 
					{
						$Cur_useData = AjaxController::getCurrentUserAllData();
						if(empty($request->getCharge)){
					  		$getData = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->select('chg_num')->first();
					  		$test = DB::table('chgfil')->insert([
					  						'chgapp_id' => $request->id,
					  						'chg_num' => $getData->chg_num,
					  						'appform_id' => $request->appid,
					  						'reference' => $request->desc,
					  						'amount' => $request->amount,
					  						't_date' => $Cur_useData['date'],
					  						't_time' => $Cur_useData['time'],
					  						't_ipaddress' => $Cur_useData['ip'],
					  						'uid' => $Cur_useData['cur_user'],
					  						'sysdate' => $Cur_useData['date'],
					  						'systime' => $Cur_useData['time'],
					  					]);
					  		$upd = array('chg_num'=>(intval($getData->chg_num) + 1));
					  		$test2 = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->update($upd);
					  		return 'DONE';
				  		} elseif($request->getCharge == 'charges') {
				  			$choosen = $request->selected;
				  			return json_encode(DB::table('fdarange')->where('id',$request->id)->select($choosen)->first()->$choosen);
				  		} elseif($request->getCharge == 'newpayment'){
				  			$data = AjaxController::getAllDataEvaluateOne($appid);
				  			switch (true) {
								case ($data->aptid == 'IN'):
									$findIn = 'initial_amnt';
									break;
								case ($data->aptid == 'R'):
									$findIn = 'renewal_amnt';
									break;
							}
				  			$fdarange = DB::table('fdarange')->where('id',$request->id)->select($findIn)->first()->$findIn;
				  			$test = DB::table('fda_chgfil')->insert(['appid' => $appid, 'fchg_code' => $request->id, 'amount' => $fdarange, 't_date' => $Cur_useData['date'], 't_time' => $Cur_useData['time'], 'ipaddress' => $Cur_useData['ip'], 'uid' => $Cur_useData['cur_user']]);
				  			if($test){
				  				return 'done';
				  			}
				  		}
					} 
					catch (Exception $e) 
					{
						return $e;
						AjaxController::SystemLogs($e);
						return 'ERROR';
					}
				}
			} else {
				return redirect('employee/dashboard')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Application has no machines registered']);
			}
		}

		//////Inspection
		public function inspection(Request $request, $appid = false)
		{
			try {
				if($request->isMethod('get')){
					$Cur_useData = AjaxController::getCurrentUserAllData();
					$data = AjaxController::getAllApplicantsProcessFlow();
					if($appid === false){
						// return view('employee.processflow.pfevaluteone', ['AppData'=> $data, 'UploadData' => $data1, 'numOfX' => count($data2), 'numOfApp' => count($data3), 'numOfAprv'=> count($data4), 'numOfNull' => count($data5), 'OOPS'=>$data6, 'OPPok' => $data7, 'ActualString' => $data8->toDateString(), 'DateString' => $data8->toFormattedDateString(),'appID' => $appid, 'DateNow' => $data9->toDateString(), 'AfterDay'=> $data10->toDateString(), 'linkToEdit' => $linkToEdit]);
						return view('employee.processflow.pfinspection', ['applicant' => $data]);
					} else {
						$teams = DB::table('app_team')
							->join('x08', 'app_team.uid', '=' , 'x08.uid' )
							->select('app_team.*', 'x08.fname', 'x08.mname', 'x08.lname')
							->where('app_team.appid', '=', $appid)->get();
						if (count($teams) != 0) {
							for ($i=0; $i < count($teams) ; $i++) { 
									$x = $teams[$i]->mname;
								if ($x != "") {
							    	$mid = strtoupper($x[0]);
							    	$mid = $mid.'. ';
							    } else {
						    		$mid = ' ';
						 		}
									$teams[$i]->wholename = $teams[$i]->fname.' '.$mid.''.$teams[$i]->lname;
								}
						}
						$data1 = AjaxController::getAllDataEvaluateOne($appid); 
						if(!isset($data1)){
							return 'Forbidden';
						}
						$data8 = Carbon::parse($Cur_useData['date']);
						$data9 = Carbon::parse($Cur_useData['date']);
						$data10 = Carbon::parse($Cur_useData['date']);
						$data10 = $data10->addDays(30);
						$data8 = $data8->addDays(1);
						$test = false;
							do {
								// $temp = $data8->toDateString();	
								if ($data8->isWeekday()) { // true
									$temp = $data8->toDateString();
									$check = DB::table('holidays')->where('hdy_date', '=', $temp)->first();
									if ($check) {
										$data8 = $data8->addDays(1);
										$test = false;
									} else {
										$test = true;
									}
								} else { // false
									$data8 = $data8->addDays(1);
									$test = false;
								}
							} while ($test == false);	
							
						return view('employee.processflow.inspectionShow', ['appdata'=>$data1,'applicant' => $data,'ActualString' => $data8->toDateString(), 'DateString' => $data8->toFormattedDateString(), 'teams' => $teams]);
					}
				} else {
					if(/*isset($request->time) && */isset($request->date)){
						$upd = DB::table('appform')->where('appid',$appid)->update(['proposedWeek' => json_encode(addslashes($request->date))/*, 'proposedTime' => $request->time*/]);
						$members = DB::table('app_team')->where('appid',$appid)->get();
						foreach ($members as $key) {
							AjaxController::notifyClient($appid,$key->uid,54);
						}
						$selected = AjaxController::getUidFrom($appid);
						AjaxController::notifyClient($appid,$selected,55);
						if($upd){
							return redirect('employee/dashboard/processflow/inspection/'.$appid);
						}
					} else {
						return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Please Provide time and Date']);
					}
				}
			} catch (Exception $e) {
				dd($e);
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pfinspection');
			}
		}


		////// ORDER OF PAYMENT ONE
		////// CASHIER
		public function CashierProcessFlow(Request $request)
		{
			try 
			{
				$data = AjaxController::getAllApplicantsProcessFlow();
				$paymentMethod = DB::table('charges')->where('cat_id','PMT')->get();
				$cur_user = AjaxController::getCurrentUserAllData();
				// $aptID = $cur_user['cur_user'];
				return view('employee.processflow.pfcashier', ['BigData'=>$data,'loggedIn'=>$cur_user,'paymentMethod'=>$paymentMethod]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pfcashier');
			}
		}
		////// CASHIER
		////// CASHIER ONE
		public function CashierOneProcessFlow(Request $request, $appid,$aptid)
		{
			if(DB::table('appform')->where([['appid', $appid],['isrecommended',1], ['isPayEval',1], ['isCashierApprove', null]])->count() <= 0){
				return redirect('employee/dashboard/processflow/cashier');
			}
			if ($request->isMethod('get')) 
			{
				try 
				{
					$paymentMethod = DB::table('charges')->where('cat_id','PMT')->get();
					$cur_user = AjaxController::getCurrentUserAllData();
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$data1 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,0);
					$data2 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,1);
					$data3 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,2);
					$data4 = AjaxController::getAllOrderOfPayment();
					$data5 = AjaxController::getAllDataOrderOfPaymentUploads($data->aptid ,3);
					// dd($data);
					return view('employee.processflow.pfcashierone', ['AppData'=>$data, 'paymentMethod'=>$paymentMethod, 'loggedIn'=>$cur_user, 'Payments' => $data1, 'Sum' => $data2, 'OOPs' =>$data4, 'Chrges' =>$data5, 'appform_id'=> $appid, 'aptid'=>$aptid]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfcashierone');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$Cur_useData = AjaxController::getCurrentUserAllData();
			  		$getData = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->select('chg_num')->first();
			  		$test = DB::table('chgfil')->insert([
			  						'chgapp_id' => $request->id,
			  						'chg_num' => $getData->chg_num,
			  						'appform_id' => $request->appid,
			  						'reference' => $request->desc,
			  						'amount' => $request->amount,
			  						't_date' => $Cur_useData['date'],
			  						't_time' => $Cur_useData['time'],
			  						't_ipaddress' => $Cur_useData['ip'],
			  						'uid' => $Cur_useData['cur_user'],
			  						'sysdate' => $Cur_useData['date'],
			  						'systime' => $Cur_useData['time'],
			  					]);
			  		$upd = array('chg_num'=>(intval($getData->chg_num) + 1));
			  		$test2 = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->update($upd);
			  		return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		////// FDA CASHIER
		////// ORDER OF PAYMENT ONE
		////// CASHIER
		public function CashierProcessFlowFDA(Request $request)
		{
			try 
			{
				$data = AjaxController::getAllApplicantsProcessFlow();
				$paymentMethod = DB::table('charges')->where('cat_id','PMT')->get();
				$cur_user = AjaxController::getCurrentUserAllData();
				// $aptID = $cur_user['cur_user'];
				return view('employee.FDA.pfcashier', ['BigData'=>$data,'loggedIn'=>$cur_user,'paymentMethod'=>$paymentMethod]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.FDA.pfcashier');
			}
		}

		//pharma
		public function CashierProcessFlowPharmaFDA(Request $request)
		{
			try 
			{
				$data = AjaxController::getAllApplicantsProcessFlow();
				$paymentMethod = DB::table('charges')->where('cat_id','PMT')->get();
				$cur_user = AjaxController::getCurrentUserAllData();
				// $aptID = $cur_user['cur_user'];
				return view('employee.FDA.pfcashierPharma', ['BigData'=>$data,'loggedIn'=>$cur_user,'paymentMethod'=>$paymentMethod]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.FDA.pfcashier');
			}
		}

		////// CASHIER
		////// CASHIER ONE
		public function CashierOneProcessFlowFDA(Request $request, $appid,$aptid)
		{
			if(DB::table('appform')->where([['appid', $appid],['isrecommendedFDA',1], ['isPayEvalFDA',1]])->count() <= 0){
				return redirect('employee/dashboard/processflow/cashier');
			}
			if ($request->isMethod('get')) 
			{
				try 
				{
					$paymentMethod = DB::table('charges')->where('cat_id','PMT')->get();
					$cur_user = AjaxController::getCurrentUserAllData();
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$data1 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,0);
					$data2 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,1);
					$data3 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,2);
					$data4 = AjaxController::getAllOrderOfPayment();
					$data5 = AjaxController::getAllDataOrderOfPaymentUploads($data->aptid ,3);
					// dd($data5);
					return view('employee.processflow.pfcashierone', ['AppData'=>$data, 'paymentMethod'=>$paymentMethod, 'loggedIn'=>$cur_user, 'Payments' => $data1, 'Sum' => $data2, 'OOPs' =>$data4, 'Chrges' =>$data5, 'appform_id'=> $appid, 'aptid'=>$aptid]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfcashierone');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$Cur_useData = AjaxController::getCurrentUserAllData();
			  		$getData = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->select('chg_num')->first();
			  		$test = DB::table('chgfil')->insert([
			  						'chgapp_id' => $request->id,
			  						'chg_num' => $getData->chg_num,
			  						'appform_id' => $request->appid,
			  						'reference' => $request->desc,
			  						'amount' => $request->amount,
			  						't_date' => $Cur_useData['date'],
			  						't_time' => $Cur_useData['time'],
			  						't_ipaddress' => $Cur_useData['ip'],
			  						'uid' => $Cur_useData['cur_user'],
			  						'sysdate' => $Cur_useData['date'],
			  						'systime' => $Cur_useData['time'],
			  					]);
			  		$upd = array('chg_num'=>(intval($getData->chg_num) + 1));
			  		$test2 = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->update($upd);
			  		return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		////// CASHIER
		////// CASHIER ONE
		///// PHARMA
		public function CashierOneProcessFlowFDApharma(Request $request, $appid,$aptid)
		{
			if(DB::table('appform')->where([['appid', $appid],['isrecommendedFDA',1], ['isPayEvalFDA',1]])->count() <= 0){
				return redirect('employee/dashboard/processflow/cashier');
			}
			if ($request->isMethod('get')) 
			{
				try 
				{
					$paymentMethod = DB::table('charges')->where('cat_id','PMT')->get();
					$cur_user = AjaxController::getCurrentUserAllData();
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$data1 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,0);
					$data2 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,1);
					$data3 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,2);
					$data4 = AjaxController::getAllOrderOfPayment();
					$data5 = AjaxController::getAllDataOrderOfPaymentUploads($data->aptid ,3);
					// dd($data5);
					return view('employee.processflow.pfcashierone', ['AppData'=>$data, 'paymentMethod'=>$paymentMethod, 'loggedIn'=>$cur_user, 'Payments' => $data1, 'Sum' => $data2, 'OOPs' =>$data4, 'Chrges' =>$data5, 'appform_id'=> $appid, 'aptid'=>$aptid]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfcashierone');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$Cur_useData = AjaxController::getCurrentUserAllData();
			  		$getData = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->select('chg_num')->first();
			  		$test = DB::table('chgfil')->insert([
			  						'chgapp_id' => $request->id,
			  						'chg_num' => $getData->chg_num,
			  						'appform_id' => $request->appid,
			  						'reference' => $request->desc,
			  						'amount' => $request->amount,
			  						't_date' => $Cur_useData['date'],
			  						't_time' => $Cur_useData['time'],
			  						't_ipaddress' => $Cur_useData['ip'],
			  						'uid' => $Cur_useData['cur_user'],
			  						'sysdate' => $Cur_useData['date'],
			  						'systime' => $Cur_useData['time'],
			  					]);
			  		$upd = array('chg_num'=>(intval($getData->chg_num) + 1));
			  		$test2 = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->update($upd);
			  		return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// CASHIER ONE
		////// ASSIGNMENT OF TEAM
		public function AssignmentofTeamProcessFlow(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllApplicantsProcessFlow();
					$data1 = AjaxController::getAllRegion();
					return view('employee.processflow.pfassignmentofteam', ['BigData' => $data, 'regions'=> $data1]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfassignmentofteam');
				}
			}
			if ($request->isMethod('post')) 
			{
				if(!isset($request->action)){
					try 
					{
						for ($i=0; $i < count($request->ids) ; $i++) { 
							DB::table('app_team')->insert([
									'appid' => $request->SelectedID,
									'teamid' => $request->teams[$i],
									'uid' => $request->ids[$i],
									'remarks' => $request->rmks[$i],
							]);
						}
						$selected = AjaxController::getUidFrom($request->SelectedID);
						// AjaxController::notifyClient($request->SelectedID,$selected,37);
						return 'DONE';	
					} 
					catch (Exception $e) 
					{
						AjaxController::SystemLogs($e);
						return 'ERROR';
					}
				} else {
					switch ($request->action) {
						case 'transfer':
							if(isset($request->appid)){
								$currentAssigned = DB::table('appform')->where('appid',$request->appid)->select('assignedRgn')->first();
								if(isset($currentAssigned)){
									if(AjaxController::transferRegion($currentAssigned->assignedRgn,$request->regions, $request->appid)){
										return back()->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Successfully transferred facility to region '. $request->regions]);
									}
									return back()->with('errRet', ['errAlt'=>'error', 'errMsg'=>'Error Occured. Action not continued']);
								}
							}
							break;
						
						default:
							return 'ERROR';
							break;
					}
				}

			}
		}

		public function hfercTeam(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllApplicantsProcessFlow();
					// dd($data);
					return view('employee.processflow.pfassignmentofhferc', ['BigData' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfassignmentofhferc');
				}
			}
		}

		public function hfercTeamAssignment(Request $request,$appid, $revision = null)
		{
			if(DB::table('appform')->where('appid',$appid)->exists()){

				// $revision = $revision == 1 ? 0 : $revision;

				if( $revision > 2 && AjaxController::isRequredToPayPTC($revision) && !FunctionsClientController::existOnDB('chgfil',array(['appform_id',$appid],['uid',AjaxController::getUidFrom($appid)],['revision',$revision],['isPaid',1])) && !AjaxController::isSessionExist(['employee_login'])){
					return redirect('employee/dashboard/processflow/assignmentofhferc/'.$appid.'/'.(AjaxController::maxRevisionFor($appid) != 0 ? AjaxController::maxRevisionFor($appid)-1 : 1))->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Payment is not settled.']);
				}

				if(isset($revision)){
					if ($request->isMethod('get')) 
					{
						$membersDoneEv = array();
						try 
						{
							$count = $canViewOthers = 0;
							$data = AjaxController::getAllDataEvaluateOne($appid);
							// $evaluationResult = [];
							$evaluationResult = AjaxController::maxRevisionFor($appid, (isset($revision) ? ['revision',$revision] : []), 1);
							// $evaluationResult = AjaxController::maxRevisionFor($appid, (isset($revision) ? ['revision',$revision] : []), 1);
							$members = AjaxController::getMembersInHFERC($data->appid,$data->rgnid,2,(isset($evaluationResult->revision) ? $evaluationResult->revision : AjaxController::maxRevisionFor($appid)+1));
							$notin = AjaxController::getMembersInHFERC($data->appid,$data->rgnid,1,(isset($evaluationResult->revision) ? $evaluationResult->revision : AjaxController::maxRevisionFor($appid)+1));
							if(count($members) > 0){
								foreach ($members as $key) {
									if($key->permittedtoInspect > 0 && $key->hasInspected > 0 && !isset($evaluationResult->HFERC_eval)){
										$count +=1;
									} if($key->permittedtoInspect > 0 && $key->hasInspected){
										$canViewOthers +=1;
										array_push($membersDoneEv, $key->uid);
									}
								}
							}
							// $canEvaluate = true;
							$canEvaluate = ($count >=1 ? true : false);
							$membersDoneEv = (DB::table('x08')->whereIn('uid',$membersDoneEv)->get() ?? []);
							$currentLoggedIn = (session()->has('employee_login') ? session()->get('employee_login') :null);
							// dd($members);
							$arrRet = [
								'AppData' => $data,
								'hferc' => $members, 
								'free' => $notin, 
								'appid'=>$appid, 
								'apptype' => $data->hfser_id, 
								'canEval' => $canEvaluate, 
								'membDone' => $membersDoneEv, 
								'evaluation' => $evaluationResult, 
								'revisionCountCurent' => (isset($evaluationResult->revision) ? $evaluationResult->revision : AjaxController::maxRevisionFor($appid) + 1),
								'maxRevision' => AjaxController::maxRevisionFor($appid) + 1,
								'canViewOthers' => ($canViewOthers >=1 ? true : false), 
								'revision' => $revision, 
								'currentUser' => $currentLoggedIn,
								'canProcessAction' => (isset($currentLoggedIn->uid) && $currentLoggedIn->uid == 'ADMIN' ? true : DB::table("hferc_team")->where([['appid',$appid],['uid',$currentLoggedIn->uid]])->whereIn('pos',['C','VC','NA','E'])->exists()),
								'isHead' => (isset($currentLoggedIn->uid) && $currentLoggedIn->uid == 'ADMIN' ? true : DB::table("hferc_team")->where([['appid',$appid],['uid',$currentLoggedIn->uid]])->whereIn('pos',['C','VC', 'NA'])->exists()),
								'customRights' => (isset($currentLoggedIn->uid) && $currentLoggedIn->uid == 'ADMIN' ? true :  DB::table("hferc_team")->where([['appid',$appid],['uid',$currentLoggedIn->uid]])->whereIn('pos',['NA','PO'])->exists())
						,'count'=>$count
							];
							return view('employee.processflow.pfassignmentofhfercaction', $arrRet);
						} 
						catch (Exception $e) 
						{
							dd($e);
							AjaxController::SystemLogs($e);
							session()->flash('system_error','ERROR');
							return view('employee.processflow.pfassignmentofhfercaction');
						}
					} else {
						if($request->isMethod('post')){

							if($request->action == 'add'){

									if($request->type == "PTC" ){
										$mem = json_decode($request->members, true);
										foreach($mem as $m){
											$ret = DB::table('hferc_team')->insert(['appid' => $appid, 'uid' => $m['uid'], 'pos' => $m['pos'], 'revision' => (isset($evaluationResult->revision) ? $evaluationResult->revision : AjaxController::maxRevisionFor($appid) + 1), 'permittedtoInspect' => 1]);
											AjaxController::notifyClient($appid,$m['uid'],41);
										}
									}else{
										$ret = DB::table('hferc_team')->insert(['appid' => $appid, 'uid' => $request->uid, 'pos' => $request->pos, 'revision' => (isset($evaluationResult->revision) ? $evaluationResult->revision : AjaxController::maxRevisionFor($appid) + 1), 'permittedtoInspect' => 1]);
										AjaxController::notifyClient($appid,$request->uid,41);
									}
																
																
						
							
							} else if($request->action == 'edit'){
								$ret = DB::table('hferc_team')->where('hfercid',$request->id)->update(['pos' => $request->pos]);
							} else if($request->action == 'delete'){
								$selected = DB::table('hferc_team')->select('uid')->where('hfercid',$request->id)->first()->uid;
								AjaxController::notifyClient($appid,$selected,40);
								$ret = DB::table('hferc_team')->where('hfercid',$request->id)->delete();
							} else if($request->action == 'permit'){
								$ret = DB::table('hferc_team')->where('hfercid',$request->id)->update(['permittedtoInspect' => $request->permit]);
								$selected = DB::table('hferc_team')->select('uid')->where('hfercid',$request->id)->first()->uid;
								AjaxController::notifyClient($appid,$selected,41);
							} else if($request->action == 'evaluate'){
								$cur = AjaxController::getCurrentUserAllData();
								$maxID = AjaxController::maxRevisionFor($appid);

								// $rev = $maxID;
								// if($request->evaluation == 2){
									$rev =	$maxID + 1;
								// }

								$ret = DB::table('hferc_evaluation')->insert(['HFERC_eval' => $request->evaluation, 'HFERC_comments' => $request->comments, 'HFERC_evalBy' => $cur['cur_user'], 'revision' => $rev, 'appid' => $appid]);
								
								// $ret = DB::table('hferc_evaluation')->insert(['HFERC_eval' => $request->evaluation, 'HFERC_comments' => $request->comments, 'HFERC_evalBy' => $cur['cur_user'], 'revision' => $maxID + 1, 'appid' => $appid]);

								$notifyAllHere = DB::table('hferc_team')->where('appid',$appid)->get();
								foreach ($notifyAllHere as $value) {
									AjaxController::notifyClient($appid,$value->uid,($request->evaluation == 1 ? 42 : 43));
								}

								if($request->evaluation == 1){
									DB::table('appform')->where('appid',$appid)->update(['status' => 'FR']);
								}else if($request->evaluation == 2){
									DB::table('appform')->where('appid',$appid)->update(['status' => 'REVF']);
								}

							} else if($request->action == 'FP'){
								$cur = AjaxController::getCurrentUserAllData();
								$ret = DB::table('appform')->where('appid',$appid)->update(['isAcceptedFP' => $request->fpselect, 'FPacceptedDate' => $cur['date'], 'FPacceptedTime' => $cur['time'], 'FPacceptedBy' => $cur['cur_user'], 'fpcomment' => $request->fpremark, 'status' => 'FPE']);
								$selected = AjaxController::getUidFrom($appid);
								AjaxController::notifyClient($appid,$selected,53);
							}

							return ($ret ? 'done' : 'error');
						}
					}
				} else {
					return redirect('employee/dashboard/processflow/assignmentofhferc/'.$appid.'/'.(AjaxController::maxRevisionFor($appid) == 0 ? 1 :AjaxController::maxRevisionFor($appid)));
				}
			}


		}

		public function viewhfercresult(Request $request, $appid, $revision, $isSelfAssess = false){
			try {
				// $revision =  0;

				// $revision = $revision == 1 ? 1 : 0;

				$data = AjaxController::getAllDataEvaluateOne($appid);
				$evaluation = AjaxController::maxRevisionFor($appid, ['revision',$revision], 1);
				// $evaluation = AjaxController::maxRevisionFor($appid, ['revision',$revision], 1);
				$dataOfEntry = null;
				if(!isset($evaluation->HFERC_eval)){
					return ($isSelfAssess ? false : back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Evaluation Doesnt\' exist.']) );
				}
				if(!$isSelfAssess){
					$evalC = new EvaluationController();
					$dataOfEntry = $evalC->FPGenerateReportAssessment($request, $appid, $evaluation->revision, $evaluation->HFERC_evalBy, true);
					if(empty($dataOfEntry)){
						return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Application record Doesnt\' exist.']);
					}

				}
				$members = AjaxController::getMembersInHFERC($data->appid,$data->rgnid,2,$revision);
				return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee.processflow.hferceval',['appdata'=>$data, 'members'=>$members, 'evaluation' => $evaluation, 'data' => $dataOfEntry]);
			} catch (Exception $e) {
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return ($this->agent ? response()->json(array('error' => $e)) :view('employee.processflow.hferceval'));
			}
		}

		public function committeTeam(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllApplicantsProcessFlow();
					
					return view('employee.processflow.pfassignmentofcommittee', ['BigData' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfassignmentofcommittee');
				}
			}
		}

		public function committeTeamAssignment(Request $request,$appid)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$count = 0;
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$members = AjaxController::getMembersIncommittee($data->appid,$data->rgnid,2);
					$notin = AjaxController::getMembersIncommittee($data->appid,$data->rgnid,1);
					// dd([$members,$notin]);
					$canEvaluate = true;
					$hfercEvalData = DB::table('hferc_evaluation')->where('appid',$appid)->get();
					return view('employee.processflow.pfassignmentofcommitteeaction', ['hferc_data' => $hfercEvalData,'AppData' => $data,'hferc' => $members, 'free' => $notin, 'appid'=>$appid, 'apptype' => $data->hfser_id, 'canEval' => $canEvaluate]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfassignmentofcommitteeaction');
				}
			} else {
				if($request->isMethod('post')){
					if($request->action == 'add'){
						if($request->type == "CON" ){
							$mem = json_decode($request->members, true);

							foreach($mem as $m){
								$ret = DB::table('committee_team')->insert(['appid' => $appid, 'uid' => $m['uid'], 'pos' => $m['pos']]);
								AjaxController::notifyClient($appid,$m['uid'],39);
							}
									
						}else{
							$ret = DB::table('committee_team')->insert(['appid' => $appid, 'uid' => $request->uid, 'pos' => $request->pos]);
							AjaxController::notifyClient($appid,$request->uid,39);
						}
						
					} else if($request->action == 'edit'){
						$ret = DB::table('committee_team')->where('committee',$request->id)->update(['pos' => $request->pos]);
					} else if($request->action == 'delete'){
						$selected = DB::table('committee_team')->select('uid')->where('committee',$request->id)->first()->uid;
						AjaxController::notifyClient($appid,$selected,40);
						$ret = DB::table('committee_team')->where('committee',$request->id)->delete();
					} 
					// else if($request->action == 'permit'){
					// 	$ret = DB::table('committee_team')->where('hfercid',$request->id)->update(['permittedtoInspect' => $request->permit]);
					// 	$selected = DB::table('committee_team')->select('uid')->where('hfercid',$request->id)->first()->uid;
					// 	AjaxController::notifyClient($appid,$selected,41);
					// } else if($request->action == 'evaluate'){
					// 	$cur = AjaxController::getCurrentUserAllData();
					// 	$ret = DB::table('appform')->where('appid',$appid)->update(['HFERC_eval' => $request->evaluation, 'HFERC_comments' => $request->comments, 'HFERC_evalDate' => $cur['date'], 'HFERC_evalTime' => $cur['time'],'HFERC_evalBy' => $cur['cur_user']]);
					// 	$notifyAllHere = DB::table('committee_team')->where('appid',$appid)->get();
					// 	foreach ($notifyAllHere as $value) {
					// 		AjaxController::notifyClient($appid,$value->uid,($request->evaluation == 1 ? 42 : 43));
					// 	}
					// }

					return ($ret ? 'done' : 'error');
				}
			}
		}

		public function coneval(Request $request, $appid){
			$data = AjaxController::getAllDataEvaluateOne($appid);
			if(isset($data->concommittee_eval)){
				return redirect('employee/dashboard/processflow/view/conevalution/'.$appid);
			}
			try {
				if($request->isMethod('get')){
					$members = AjaxController::getMembersIncommittee($data->appid,$data->rgnid,2);
					// if(count($members) <= 0){
					// 	return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Please add members first before proceeding']);
					// }
					$bed = DB::table('branch')->where('regionid',$data->rgnid)->select('conBed')->first()->conBed;
					$brp = AjaxController::getConCatchFormatted($appid);
					$track = DB::table('con_hospital')->where('appid',$appid)->select('id','facilityname','location1')->get();
					$savedData = DB::table('con_evalsave')->where([['appid',$appid],['draft',1]])->get();
					return view('employee.processflow.pfconevalone',['brp' => $brp, 'AppData' => $data, 'track' => $track, 'members' => $members, 'bed' => $bed, 'savedData' => json_encode($savedData)]);
				}
				else if($request->isMethod('POST')){
					$cUser = AjaxController::getCurrentUserAllData();
					if( (count($request->existHospabc) + count($request->locabc)) == (count($request->abc) + count($request->typeabc)) ){

						DB::table('con_evalsave')->where([['appid',$appid],['draft',1]])->delete();

						for ($i=0; $i < (isset($request->existHospcde) ? (count($request->existHospabc) > count($request->existHospcde) ? count($request->existHospabc) : count($request->existHospcde)) : count($request->existHospabc)) ; $i++) { 
							if(isset($request->existHospabc[$i])){
								if(isset($request->tya)){
									$insert = ['appid'=> $appid,'facilityname' => $request->existHospabc[$i],'location' => $request->locabc[$i],'cat_hos' => $request->typeabc[$i],'noofbed' => $request->abc[$i], 'tya' => $request->tya[$i], 'aya' => $request->aya[$i], 'apty' => $request->apty[$i], 'ttph' => $request->ttph[$i], 'fromWhere' => 'dib'];
								}

								if($request->has('draft')){
									$insert['draft'] = 1;
								}

								DB::table('con_evalsave')->insert($insert);
							}	
							if(/*isset($request->existHospcde) && */isset($request->existHospcde[$i])){
								$existHospital = ['appid'=> $appid, 'facilityname' => $request->existHospcde[$i],'location' => $request->loccde[$i],'cat_hos' => $request->typecde[$i],'noofbed' => $request->cde[$i], 'fromWhere' => 'ihb'];
								if($request->has('draft')){
									$existHospital['draft'] = 1;
								}
								DB::table('con_evalsave')->insert($existHospital);
							}
						}
						// if(isset($request->addr) && isset($request->catchment) && isset($request->type) && count($request->addr) == count($request->catchment)){
						if(isset($request->addr) && isset($request->catchment)&& isset($request->est) && isset($request->type) && count($request->addr) == count($request->catchment)){
						
						$ccatch = 	DB::table('con_catch')->where([['appid', $appid]])->first();//6-12-2021
						 
						if(!is_null($ccatch)){
							DB::table('con_catch')->where([['appid', $appid]])->delete();//6-12-2021
						}
							// DB::table('con_catch')->where([['appid', $appid]])->delete();//6-12-2021

							for ($j=0; $j < count($request->addr); $j++) { 
								// DB::table('con_catch')->insert(['appid' => $appid, 'type' => ($request->type[$j] == strtolower('primary') ? 0 : 1), 'location' => $request->addr[$j], 'population' => $request->catchment[$j], 'isfrombackend' => 1]);
								DB::table('con_catch')->insert(['appid' => $appid, 'type' => ($request->type[$j] == strtolower('primary') ? 0 : 1), 'location' => $request->addr[$j], 'population' => $request->catchment[$j], 'eval_est' => $request->est[$j], 'isfrombackend' => 1]);
						
						
							}

						}

						if($request->has('draft')){
							return 'DONE';
						}

						if(!$request->has('draft')){

							DB::table('con_evaluate')->insert(['appid' => $appid, 'acc' => $request->acc, 'remarksacc' => $request->remarksacc, 'st' => $request->st, 'remarksst' => $request->remarksst, 'hdp' => $request->hdp, 'remarkshdp' => $request->remarkshdp, 'tph' => $request->tph, 'remarkstph' => $request->remarkstph ,'ihb' => $request->ihbval, 'bpr' => $request->bprval, 'pbn' => $request->pbnval, 'ubn' => $request->ubnval, 'psc' => $request->pscaval, 'bpp' => $request->bpp, 'remarksbpp' => $request->remarksbpp, 'tt' => $request->tt, 'remarkstt' => $request->remarkstt, 'asl' => $request->asl, 'remarksasl' => $request->remarksasl, 'ilh' => $request->ilh, 'remarksilh' => $request->remarksilh, 'atr' => $request->atr, 'remarksatr' => $request->remarksatr, 'comments' => $request->comments,'membersPart' => (isset($request->membersPart) ? implode(',',$request->membersPart) : null) ]);

							if($request->has('id')){
								for ($j=0; $j < count($request->id) ; $j++) { 
									$complaints = 'fvc'.$request->id[$j];
									$compliance = 'gclr'.$request->id[$j];
									$remarks = 'remarks'.$request->id[$j];
									DB::table('con_hospital')->where('id',$request->id[$j])->update(['compliance' => $request->$compliance,'complaints' => $request->$complaints, 'evalRemarks' => $request->$remarks]);
								}
							}
							DB::table('appform')->where('appid',$appid)->update(['concommittee_eval' => $request->verd, 'concommittee_evaltime' => $cUser['time'], 'concommittee_evaldate' => $cUser['date'], 'concommittee_evalby' => $cUser['cur_user']]);

						}

					}
					return redirect('employee/dashboard/processflow/view/conevalution/'.$appid);
				}
				
			} catch (Exception $e) {
				return $e;
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pfconevalone');
			}
		}

		public function conEvalView($appid){
			$data = AjaxController::getAllDataEvaluateOne($appid);
			if(!isset($data->concommittee_eval) || DB::table('con_evalsave')->where('appid',$appid)->doesntExist()){
				return redirect('employee/dashboard/processflow/conevaluation')->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No evaluation data found']);
			}
			$members = null;
			$data = AjaxController::getAllDataEvaluateOne($appid);
			$dataArray = array();
			$brp = AjaxController::getConCatchFormatted($appid,1);
			$where = array('appid' => $appid);
			$hospitals = DB::table('con_evalsave')->leftJoin('facilitytyp','facilitytyp.facid','con_evalsave.cat_hos')->where($where)->get();
			$evalResult = DB::table('con_evaluate')->where('appid',$appid)->first();
			// dd($evalResult);
			$existHosp = DB::table('con_hospital')->where($where)->get();
			if(isset($evalResult->membersPart)){
				$sql = "SELECT * FROM `x08` LEFT JOIN `committee_team` ON committee_team.uid = x08.uid WHERE x08.uid IN(SELECT committee_team.uid from committee_team WHERE committee_team.committee IN ($evalResult->membersPart))";
				$members = DB::select($sql);
			}
			// dd($evalResult->membersPart);
			// dd(DB::select($sqee));
			return view('employee.processflow.pfconevaloneview',['brp' => $brp, 'hospitalData' => $hospitals, 'evalRes' => $evalResult, 'existHosp' => $existHosp, 'appdata' => $data, 'members' => $members]);
		}

		////// ASSIGNMENT OF TEAM
		////// ASSESSMENT
		public function EvaluationProcessFlow(Request $request, $isMobile = false)
		{
			AjaxController::createMobileSessionIfMobile($request);
			try 
			{	
				if(in_array(true, AjaxController::isSessionExist(['employee_login']))){
					$arrRet = [
						'BigData' => AjaxController::getAllApplicantsProcessFlow(),
						'user' => AjaxController::getCurrentUserAllData()
					];
					return AjaxController::sendTo(false,$this->agent,$request->all(),'employee.processflow.pfevaluationtool',$arrRet);
	                // return ($this->agent ? response()->json(array('data' => $data,'user' => $user)) : view('employee.processflow.pfevaluationtool', ['BigData' => $data, 'user' => $user]));
            	}
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return ($this->agent ? response()->json(array('error' => $e)) :view('employee.processflow.pfevaluationtool'));
			}
		}
		public function EvaluationProcessFlowCON(Request $request, $session_equiv = false)
		{
			try 
			{	
				($session_equiv !== false ? self::sessionForMobile($session_equiv) : null);
				$data = AjaxController::getAllApplicantsProcessFlow();
				$user = AjaxController::getCurrentUserAllData();
				// dd($data);
				// return response()->json($data);
				// dd(json_encode($data));
                return view('employee.processflow.pfevaluationtoolCON', ['BigData' => $data, 'user' => $user]);
				
				/*return ($this->agent ? response()->json(array('status'=> 'success','response' => $data)) : view('employee.processflow.pfassessment', ['BigData' => $data]));*/
				// return view('employee.processflow.pfassessment', ['BigData' => $data]);
				// return ($agent ? response()->json(array('status'=> 'success','data' => $employeeData)) : redirect()->route('eDashboard'));
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return ($this->agent ? response()->json(array('error' => $e)) :view('employee.processflow.pfevaluationtool'));
			}
		}
		////// Evaluation One

		public function EvaluationShowEach(Request $request, $currentuser, $appid, $apptype)
		{	
			try {
				$joinedData = $whereClause = $user = $assesed = $toSelect = $toFind = null;
				$firstLevel = array();
				if(DB::table('appform')->where([['appid', $appid],['isrecommended',1], ['isInspected',null], ['isCashierApprove', 1]])->count() <= 0 || DB::table('hferc_team')->where([['uid',$currentuser],['permittedtoInspect',1],['appid',$appid]])->doesntExist()){
					return ($this->agent ? response()->json(array('status' => 'error', 'message' => 'forbidden')) : back());
				}
				$toSelect = 'evaluation';
				$toFind = 'appid';
				$table = 'ptc_evaluation';
				$data = AjaxController::getAllDataEvaluateOne($appid);
				$selected = $data->uid.'_'.$data->hfser_id.'_'.$data->appid.'_'.$currentuser;
				$assesed = (DB::table($table)->where($toFind,$selected)->whereNotNull($toSelect)->exists() ? json_encode(array_keys(json_decode(DB::table($table)->where($toFind,$selected)->select($toSelect)->first()->$toSelect,true))) : null);

				$whereClause = [['x08_ft.appid','=',$appid],['serv_asmt.hfser_id', '=',$apptype]];

				$joinedData = DB::table('x08_ft')
	            ->leftJoin('facilitytyp', 'x08_ft.facid', '=', 'facilitytyp.facid')
	            ->leftJoin('serv_asmt', 'x08_ft.facid', '=', 'serv_asmt.facid')
				->leftJoin('asmt_title', 'serv_asmt.part', '=', 'asmt_title.title_code')
				->leftJoin('asmt2', 'serv_asmt.asmt2_id', '=', 'asmt2.asmt2_id')
				->leftJoin('asmt2_loc', 'asmt2.asmt2_loc', '=', 'asmt2_loc.asmt2l_id')
	            ->select(
					'asmt2_loc.header_lvl1 as headers'
				)
	            ->orderBy('asmt_title.title_name','ASC')->orderBy('serv_asmt.srvasmt_seq','ASC')
	            ->where($whereClause)
	            ->distinct()
	            ->get();
	            foreach ($joinedData as $key) {
					if(!in_array($key->headers, $firstLevel)){
						array_push($firstLevel,$key->headers);
					}
				}
	            $headers = DB::table('asmt2_loc')->whereIn('asmt2l_id',$firstLevel)->select('asmt2l_id','asmt2l_desc')->get();
				$headers['hasNull'] = false;
				if(in_array(null,$firstLevel,true)){
					$headers['hasNull'] = true;
				}
				$urlToRedirect = asset('employee/dashboard/processflow/evalution/each/'.$currentuser.'/'.$appid.'/'.$apptype.'/');
	            return ($this->agent ? response()->json(array('AppData'=>$data, 'appId'=> $appid, 'apptype' => $apptype, 'headers'=>$headers, 'address' => $urlToRedirect, 'assesed'=> $assesed, 'assessor' => $currentuser)) : view('employee.processflow.pfevaluateptc', ['AppData'=>$data, 'appId'=> $appid, 'apptype' => $apptype, 'headers'=>$headers, 'address' => $urlToRedirect, 'assesed'=> $assesed, 'assessor' => $currentuser]));	
            } catch (Exception $e) {
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return ($this->agent ? response()->json(array('error' => $e)) : view('employee.processflow.pfevaluateptc'));
			}
			
		}

		public function EvaluationOneProcessFlow(Request $request, $currentuser, $appid, $apptype, $choosen){
			try {
				$asmt2_col = $asmt2_loc = $filenames = array();
				$countColoumn = $conditionNull = $whereClause = $joinedData = $selected = null;
				if(DB::table('appform')->where([['appid', $appid],['isrecommended',1], ['isInspected',null], ['isCashierApprove', 1]])->count() <= 0 || DB::table('hferc_team')->where([['uid',$currentuser],['permittedtoInspect',1],['appid',$appid]])->doesntExist()){
					return ($this->agent ? response()->json(array('status' => 'error', 'message' => 'forbidden')) : back());
				}
				$countColoumn = DB::SELECT("SELECT count(*) as 'all' FROM information_schema.columns WHERE table_name = 'asmt2'")[0]->all -1;
				$conditionNull = (strtoupper($choosen) !== 'OTHERS'? ['asmt2_loc.header_lvl1',$choosen] : ['asmt2_loc.asmt2l_id','<>',null]);
				$whereClause = [['x08_ft.appid','=',$appid],['serv_asmt.hfser_id', '=',$apptype], $conditionNull];
				$joinedData = DB::table('x08_ft')
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
	            ->where($whereClause);
	            if(strtolower($choosen) === 'others'){
	            	$joinedData->whereNull('asmt2_loc.header_lvl1');
	            }
	            $joinedData = json_decode($joinedData->get(),true);
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
				$selected = $data->uid.'_'.$data->hfser_id.'_'.$data->appid.'_'.$currentuser;
				$linkToSend = asset('employee/dashboard/processflow/evaluation/view/'.$currentuser.'/'.$appid.'/'.$apptype.'/');
				return ($this->agent ? response()->json(array('AppData'=>$data, 'appId'=> $appid, 'joinedData'=>$joinedData, 'apptype' => $apptype, 'filenames'=>$filenames, 'header'=>$choosen,'org'=>$selected, 'assessor' => $currentuser, 'linkToSend' => $linkToSend)) : view('employee.processflow.pfassessmentone', ['AppData'=>$data, 'appId'=> $appid, 'joinedData'=>$joinedData, 'apptype' => $apptype, 'filenames'=>$filenames, 'header'=>$choosen,'org'=>$selected, 'assessor' => $currentuser, 'linkToSend' => $linkToSend]));
			} catch (Exception $e) {
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return ($this->agent ? response()->json(array('error' => $e)) : view('employee.processflow.pfassessmentone'));
			}
		}


		public function EvaluationOneViewProcessFlow(Request $request, $currentuser, $appid, $apptype){
			try {
				$tableNames = $table = $whereClause = $urlToRedirect = $selectFromDB = null;
				if(DB::table('appform')->where([['appid', $appid],['isrecommended',1], ['isInspected',null], ['isCashierApprove', 1]])->count() <= 0 || DB::table('hferc_team')->where([['uid',$currentuser],['permittedtoInspect',1],['appid',$appid]])->doesntExist()){
					return ($this->agent ? response()->json(array('status' => 'error', 'message' => 'forbidden')) : back());
				}
				$sendDB = true;
				$exceptData = array('_token','appID','facilityname','monType','org','header');
				($request->isMobile === "true" && $this->agent ? self::sessionForMobile($request->uid) : null);
				if($this->agent){
					$apptype = $request->apptype;
					$appid = $request->appid;
					$request = json_decode($request->assessment);
				}
				$tableNames = 'appform';
				$table = 'ptc_evaluation';
				$whereClause = 'appid';
				$urlToRedirect = asset('employee/dashboard/processflow/evalution/'.$currentuser.'/'.$appid.'/'.$apptype);
				$selectFromDB = array('evaluation');
				if(DB::table('appform')->where('appid',$appid)->count() < 1){
					return redirect('employee/dashboard/processflow/evaluation/');
					dd('redirecting you to page');
				}
				if((!empty($request) && $this->agent) || !empty($request->all())){
					$charCompiled = $request->org;
				} else {
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$charCompiled = $data->uid.'_'.$data->hfser_id.'_'.$data->appid.'_'.$currentuser;
				}

				$dataToView = DB::table($table)->where($whereClause,$charCompiled)->select($selectFromDB)->get()->first();
				$selectFromDB = implode('', $selectFromDB);
				if(empty($dataToView->$selectFromDB) || $dataToView === null){
					if((!empty($request) && $this->agent) || !empty($request->all())){
						$recordsToCheck = (!empty($request) && $this->agent ? $request: $request->all());
						$exceptedData = (!empty($request) && $this->agent ? $recordsToCheck: $request->all());
						foreach ($recordsToCheck as $key => $value) {
							if($value == false && $value != null){
								if(!in_array($key, $uncompliedList)){
									array_push($uncompliedList, $key);
								}
							}
							if(in_array($key, $exceptData)){
								unset($exceptedData->key);
							}
						}
						$jsonToDB = json_encode(array($request->header => ($this->agent ? $exceptedData : $request->except($exceptData))));
						if(DB::table($table)->where($whereClause,$charCompiled)->count() <= 0 ){
							DB::table($table)->insert([
						    ['appid' => $charCompiled,'t_date' => AjaxController::getCurrentUserAllData()['date'],'t_time' => AjaxController::getCurrentUserAllData()['time'],'evaluatedby' => AjaxController::getCurrentUserAllData()['cur_user'],'evaluation' => $jsonToDB]
							]);
						} else {
							DB::table($table)->where($whereClause,$charCompiled)
							->update(['appid' => $charCompiled,'t_date' => AjaxController::getCurrentUserAllData()['date'],'t_time' => AjaxController::getCurrentUserAllData()['time'],'evaluatedby' => AjaxController::getCurrentUserAllData()['cur_user'],'evaluation' => $jsonToDB]);
						}
						$dataToView = json_encode($exceptedData);
					} else {
						return redirect('employee/dashboard/processflow/assessment');
						dd('ERROR, PLEASE CONTACT ADMIN IMMEDIATELY');
					}
				} else {
					$dataToView = $dataToView->$selectFromDB;
					$exceptedData = (!empty($request) && $this->agent ? $request: $request->all());
					foreach ($exceptedData as $key => $value) {
						if(in_array($key, $exceptData)){
							unset($exceptedData->key);
						}
					}
					if((!empty($request) && $this->agent) || !empty($request->all())){
						$storedAssessment = json_decode($dataToView,true);
						$currentAssessment = array($request->header => ($this->agent ? $exceptedData : $request->except($exceptData)));
						if(!array_key_exists($request->header,$storedAssessment)){
							$merged = json_encode(array_merge($currentAssessment,$storedAssessment));
							DB::table($table)
								->where($whereClause,$charCompiled)							
								->update([$selectFromDB => $merged]);
						}
						// $sendDB = true;
						
					}
				}
				return (($sendDB  ? ($this->agent ? response()->json(array('status' => 'success', 'message' => 'success')) : view('employee/assessment/operationSuccess', ['redirectTo' => $urlToRedirect])) : ($this->agent ? response()->json(array('status' => 'error', 'message' => 'error')) : view('employee/assessment/operationSuccess', ['redirectTo' => $urlToRedirect]))));	
			} catch (Exception $e) {
				dd($e);
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return ($this->agent ? response()->json(array('error' => $e)) : view('employee/assessment/operationSuccess'));
			}
		}

		public function EvaluationDisplay(Request $request, $currentuser, $appid, $apptype){
			try {

				$charCompiled = $noCharCompiled = $appform = $table = $selectFromDB = $whereClause = $fieldsOnUpdate = $checkExistMon = $checkStatus = $checkForStatus = $compliedToString = $dataFromDB = $mergedData = $unsortedData = $isEmptyAssess = $checkInspected = $exploded = null;
				$assessor = $filenames = array();
				$allUserDetails = AjaxController::getCurrentUserAllData();
				// if(DB::table('appform')->where([['appid', $appid],['isrecommended',1], ['isInspected',null], ['isCashierApprove', 1]])->count() <= 0 || DB::table('hferc_team')->where([['uid',$currentuser],['permittedtoInspect',1],['appid',$appid]])->doesntExist()){
				// 	return back();
				// }
				$exceptData = array('_token','appID','facilityname','monType','org','header','assessor');
				$selectFromDB = array('evaluation');
				if(DB::table('appform')->where('appid',$appid)->count() < 1){
					return redirect('employee/dashboard/processflow/evaluation/');
					dd('redirecting you to page');
				}	
				$table = 'ptc_evaluation';
				$whereClause = 'appid';
				$data = AjaxController::getAllDataEvaluateOne($appid);
				$charCompiled = $data->uid.'_'.$data->hfser_id.'_'.$data->appid.'_'.$currentuser;

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
							$exploded = explode(',', $value['filename']);
							foreach ($exploded as $explodedkey => $explodedvalue) {
								if(!in_array(trim($explodedvalue), $filenames)){
									array_push($filenames, trim($explodedvalue));
									unset($dataFromDB[$key]['filename']);
								}
							}
						}
					}
					$unsortedData = call_user_func_array("array_merge", $dataFromDB);
					$testArray = array();
					foreach ($unsortedData as $key => $value) {
						$stringToFind = '!/*headCode';
						if($key !== 'filename'){
							$string = $key;
							$testArray[$part][$key] = AjaxController::filterAssessmentData($key,$str,'!/*');
						}
					}
					$testFinalArray = array();
					$sortArray = array(); 
					foreach ($testArray as $key => $value) {
						$testHere = $testArray[$key];
						ksort($testHere,SORT_NATURAL);
						array_push($testFinalArray, $testHere);
					}
					$tryLng = call_user_func_array("array_merge", $testFinalArray);
					$dataToView = $tryLng;
					$toDir = $filenames;
					if(DB::table('hferc_team')->where([['uid',$currentuser],['hasInspected',0]])->exists()){
						DB::table('hferc_team')->where('uid',$currentuser)->update(['hasInspected' => 1]);
					}
					return view('employee.processflow.pfevaluateoneview',['data' => json_encode($dataToView),'file'=>$toDir, 'assessor' => $assessor]);
				} else {
					// return redirect('employee/dashboard/processflow/assessment/');
					return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Assessment Doesnt\' exist.']);
				}
		
				
			} catch (Exception $e) {
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return ($this->agent ? response()->json(array('error' => $e)) : view('employee.processflow.pfevaluateoneview'));
			}
		}


		public function AssessmentParts(Request $request){
			$data = AjaxController::getAllApplicantsProcessFlow();
			return view('employee.processflow.pfassessment', ['BigData' => $data]);
		}

		// public function AssessmentShowPart(Request $request, $appid, $isMon = false, $isSelfAssess = false){
		// 	if(isset($appid) && FunctionsClientController::isExistOnAppform($appid) && in_array(true, AjaxController::isSessionExist(['uData','employee_login']))){
		// 		try {
		// 			$data = AjaxController::getAllDataEvaluateOne($appid);
		// 			$toViewArr = [
		// 				'data' => $data,
		// 				'head' => AjaxController::forAssessmentHeaders(array(['appform.appid',$appid],['asmt_h1.apptype',$data->hfser_id]),array('asmt_title.title_name as desc','asmt_title.title_code as id')),
		// 				'address' => ($isSelfAssess ? url('client1/apply/HeaderOne/'.$appid.'/') : url('employee/dashboard/processflow/HeaderOne/'.$appid.'/')),
		// 				'isMain' => true,
		// 				'assesed' => AjaxController::assessedDone(3,$appid,$isMon,$isSelfAssess),
		// 				'isMon' => $isMon
		// 			];

		// 			return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee.processflow.pfassessmentShowPart',$toViewArr);
		// 		} catch (Exception $e) {
		// 			return $e;
		// 		}

		// 	} else {
		// 		return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Application not exist.']);
		// 	}

		// }

		public function AssessmentShowPart(Request $request, $appid, $isMon = false, $isSelfAssess = false){
			AjaxController::createMobileSessionIfMobile($request);
			if(isset($appid) && FunctionsClientController::isExistOnAppform($appid) && in_array(true, AjaxController::isSessionExist(['uData','employee_login']))){
				try {
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$toViewArr = [
						'data' => $data,
						'head' => AjaxController::forAssessmentHeaders(array(['appform.appid',$appid],['asmt_h1.apptype',$data->hfser_id]),array('asmt_title.title_name as desc','asmt_title.title_code as id')),
						'address' => ($isSelfAssess ? url('client1/apply/HeaderOne/'.$appid.'/') : url('employee/dashboard/processflow/HeaderOne/'.$appid.'/')),
						'isMain' => true,
						'assesed' => AjaxController::assessedDone(3,$appid,$isMon,$isSelfAssess),
						'isMon' => $isMon,
						'isSentFromMobile' => DB::table('frommobileinspection')->where([['appid',$appid],['monid',($isMon ? $isMon : null)]])->exists(),
						'hasselfassess' => DB::table('assessmentcombinedduplicate')->where([['appid',$appid],['selfassess',1]])->exists(),
						'appid' => $appid
					];
					return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee.processflow.pfassessmentShowPart',$toViewArr);
				} catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return $e;
				}

			} else {
				return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Application not exist.']);
			}

		}


		// public function AssessmentShowH1(Request $request,$appid,$part,$isMon = false,$isSelfAssess = false){
		// 	if(isset($appid) && FunctionsClientController::isExistOnAppform($appid) && FunctionsClientController::existOnDB('asmt_title',[['title_code',$part]]) && in_array(true, AjaxController::isSessionExist(['uData','employee_login']))){
		// 		try {
		// 			$data = AjaxController::getAllDataEvaluateOne($appid);
		// 			$whereClause = array(['appform.appid',$appid],['asmt_h1.apptype',$data->hfser_id],['asmt_h1.partID',$part]);
		// 			$headData = AjaxController::forAssessmentHeaders($whereClause,array('asmt_h1.h1name as desc','asmt_h1.asmtH1ID as id','asmt_title.title_name as h1HeadBack','asmt_title.title_code as h1HeadID'));
		// 			$toViewArr = [
		// 				'data' => $data,
		// 				'head' => $headData,
		// 				'address' => ($isSelfAssess ? url('client1/apply/HeaderTwo/'.$appid.'/') : url('employee/dashboard/processflow/HeaderTwo/'.$appid.'/')),
		// 				'customAddress' => ($isSelfAssess ? url('client1/apply/assessmentReady/'.$appid.'/') :url('employee/dashboard/processflow/parts/'.$appid)),
		// 				'assesed' => AjaxController::assessedDone(2,$appid,$isMon,$isSelfAssess),
		// 				'neededData' => array('level' => 1,'id' => $part),
		// 				'isMon' => $isMon,
		// 				'crumb' => [array('id' => $headData[0]->h1HeadID,'desc' => $headData[0]->h1HeadBack, 'beforeAddress' => 'MAIN')]
		// 			];
		// 			return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee.processflow.pfassessmentShowPart',$toViewArr);
		// 		} catch (Exception $e) {
		// 			return $e;
		// 		}

		// 	} else {
		// 		return ($isSelfAssess ? false : back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Part does not exist.']));
		// 	}
		// }

		public function AssessmentShowH1(Request $request,$appid,$part,$isMon = false,$isSelfAssess = false){
			AjaxController::createMobileSessionIfMobile($request);
			if(isset($appid) && FunctionsClientController::isExistOnAppform($appid) && FunctionsClientController::existOnDB('asmt_title',[['title_code',$part]]) && in_array(true, AjaxController::isSessionExist(['uData','employee_login']))){
				try {

					$data = AjaxController::getAllDataEvaluateOne($appid);
					$whereClause = array(['appform.appid',$appid],['asmt_h1.apptype',$data->hfser_id],['asmt_h1.partID',$part]);
					$headData = AjaxController::forAssessmentHeaders($whereClause,array('asmt_h1.h1name as desc','asmt_h1.asmtH1ID as id','asmt_title.title_name as h1HeadBack','asmt_title.title_code as h1HeadID'));
					$toViewArr = [
						'data' => $data,
						'head' => $headData,
						// 'address' => ($isSelfAssess ? url('client1/apply/HeaderTwo/'.$appid.'/') : url('employee/dashboard/processflow/HeaderTwo/'.$appid.'/')),
						'address' => ($isSelfAssess ? url('client1/apply/ShowAssessments/'.$appid.'/') : url('employee/dashboard/processflow/ShowAssessments/'.$appid.'/')),
						'customAddress' => ($isSelfAssess ? url('client1/apply/assessmentReady/'.$appid.'/') :url('employee/dashboard/processflow/parts/'.$appid)),
						// 'assesed' => AjaxController::assessedDone(2,$appid,$isMon,$isSelfAssess),
						'assesed' => AjaxController::forDoneHeaders($appid,$isMon,$isSelfAssess)['h3'],
						'neededData' => array('level' => 3,'id' => $part),
						'isMon' => $isMon,
						'crumb' => isset($headData[0]->h1HeadID) ? [array('id' => $headData[0]->h1HeadID,'desc' => $headData[0]->h1HeadBack, 'beforeAddress' => 'MAIN')] : null
					];
					// dd($toViewArr);
					return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee.processflow.pfassessmentShowPart',$toViewArr);
				} catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return $e;

				}

			} else {
				return ($isSelfAssess ? false : back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Part does not exist.']));
			}
		}

		public function AssessmentShowH2(Request $request,$appid,$h1, $isMon = false,$isSelfAssess = false){
			AjaxController::createMobileSessionIfMobile($request);
			if(isset($appid) && FunctionsClientController::isExistOnAppform($appid) && FunctionsClientController::existOnDB('asmt_h1',[['asmtH1ID',$h1]]) && in_array(true, AjaxController::isSessionExist(['uData','employee_login']))){
				try {
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$whereClause = array(['appform.appid',$appid],['asmt_h1.apptype',$data->hfser_id],['asmt_h2.asmtH1ID_FK',$h1]);
					$headData = AjaxController::forAssessmentHeaders($whereClause,array('asmt_h2.h2name as desc','asmt_h2.asmtH2ID as id','asmt_title.title_code as idForBack','asmt_title.title_name as h1HeadBack','asmt_title.title_code as h1HeadID','asmt_h1.h1name as h2HeadBack','asmt_h1.partID as h2HeadID'));
					$toViewArr = [
						'data' => $data,
						'head' => $headData,
						'address' => ($isSelfAssess ? url('client1/apply/HeaderThree/'.$appid.'/') : url('employee/dashboard/processflow/HeaderThree/'.$appid.'/')),
						'assesed' => AjaxController::assessedDone(1,$appid,$isMon,$isSelfAssess),
						'beforeAddress' => 'HeaderOne',
						'neededData' => array('level' => 2,'id' => $h1),
						'isMon' => $isMon,
						'crumb' => [array('id' => $headData[0]->h1HeadID,'desc' => $headData[0]->h1HeadBack, 'beforeAddress' => 'MAIN'),array('id' => $headData[0]->h2HeadID,'desc' => $headData[0]->h2HeadBack, 'beforeAddress' => 'HeaderOne')]
					];
					return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee.processflow.pfassessmentShowPart',$toViewArr);
				} catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return $e;
				}

			} else {
				return ($isSelfAssess ? false : back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Header does not exist.']));
			}
			return 'forbidden';

		}

		public function AssessmentShowH3(Request $request,$appid,$h2, $isMon = false,$isSelfAssess = false){
			AjaxController::createMobileSessionIfMobile($request);
			if(isset($appid) && FunctionsClientController::isExistOnAppform($appid) && FunctionsClientController::existOnDB('asmt_h2',[['asmtH2ID',$h2]]) && in_array(true, AjaxController::isSessionExist(['uData','employee_login']))){
				try {
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$whereClause = array(['appform.appid',$appid],['asmt_h1.apptype',$data->hfser_id],['asmt_h3.asmtH2ID_FK',$h2]);
					$headData = AjaxController::forAssessmentHeaders($whereClause,array('asmt_h3.h3name as desc','asmt_h3.asmtH3ID as id','asmt_h2.asmtH1ID_FK as idForBack','asmt_title.title_name as h1HeadBack','asmt_title.title_code as h1HeadID','asmt_h1.h1name as h2HeadBack','asmt_h1.partID as h2HeadID','asmt_h2.h2name as h3HeadBack','asmt_h2.asmtH2ID as h3HeadID'),1);
					$toViewArr = [
						'data' => $data,
						'head' => $headData,
						'address' => ($isSelfAssess ? url('client1/apply/ShowAssessments/'.$appid.'/') : url('employee/dashboard/processflow/ShowAssessments/'.$appid.'/')),
						'assesed' => AjaxController::forDoneHeaders($appid,$isMon,$isSelfAssess)['h3'],
						'beforeAddress' => 'HeaderTwo',
						'neededData' => array('level' => 3,'id' => $h2),
						'isMon' => $isMon,
						'crumb' => [array('id' => $headData[0]->h1HeadID,'desc' => $headData[0]->h1HeadBack, 'beforeAddress' => 'MAIN'),array('id' => $headData[0]->h2HeadID,'desc' => $headData[0]->h2HeadBack, 'beforeAddress' => 'HeaderOne'),array('id' => $headData[0]->idForBack,'desc' => $headData[0]->h3HeadBack,'beforeAddress' => 'HeaderTwo')]
					];
					return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee.processflow.pfassessmentShowPart',$toViewArr);
				} catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return $e;
				}
			} else {
				return ($isSelfAssess ? false  : back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Area does not exist.']));
			}
			return 'forbidden';

		}

		// public function ShowAssessments(Request $request,$appid,$h3, $isMon = false,$isSelfAssess = false){
		// 	if(isset($appid) && FunctionsClientController::isExistOnAppform($appid) && FunctionsClientController::existOnDB('asmt_h3',[['asmtH3ID',$h3]]) && !FunctionsClientController::existOnDB('assessmentcombinedduplicate',[['asmtH3ID_FK',$h3],['appid',$appid],['monid',$isMon],['selfassess',($isSelfAssess ? 1 : null)]]) && in_array(true, AjaxController::isSessionExist(['uData','employee_login']))){
		// 		try {
		// 			$data = AjaxController::getAllDataEvaluateOne($appid);
		// 			$whereClause = array(['assessmentcombined.assessmentStatus',1],['appform.appid',$appid],['asmt_h1.apptype',$data->hfser_id],['assessmentcombined.asmtH3ID_FK',$h3]);
		// 			$toSelect = array('assessmentcombined.asmtComb as id','assessmentcombined.assessmentName as description','assessmentcombined.asmtH3ID_FK as h3Header','assessmentcombined.headingText as otherHeading', 'assessmentcombined.assessmentSeq as sequence','asmt_h3.asmtH2ID_FK as idForBack','asmt_title.title_name as h1HeadBack','asmt_title.title_code as h1HeadID','asmt_h1.h1name as h2HeadBack','asmt_h1.partID as h2HeadID','asmt_h2.h2name as h3HeadBack','asmt_h2.asmtH2ID as h3HeadID','asmt_h3.h3name as h4HeadBack','asmt_h3.asmtH3ID as h4HeadID');
		// 			$headData = AjaxController::forAssessmentHeaders($whereClause,$toSelect,1);
		// 			$toViewArr = [
		// 				'data' => $data,
		// 				'head' => $headData,
		// 				'address' => url('employee/dashboard/processflow/HeaderThree/'.$appid.'/'),
		// 				'part' => $h3,
		// 				'isMon' => $isMon,
		// 				'crumb' => [array('id' => $headData[0]->h1HeadID,'desc' => $headData[0]->h1HeadBack, 'beforeAddress' => 'MAIN'),array('id' => $headData[0]->h2HeadID,'desc' => $headData[0]->h2HeadBack, 'beforeAddress' => 'HeaderOne'),array('id' => $headData[0]->h3HeadID,'desc' => $headData[0]->h3HeadBack,'beforeAddress' => 'HeaderTwo') ,array('id' => $headData[0]->idForBack,'desc' => $headData[0]->h4HeadBack,'beforeAddress' => 'HeaderThree')]
		// 			];
		// 			// dd($toViewArr);
		// 			// return view('employee.processflow.pfassessmentShowAssessment',$toViewArr);
		// 			return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee.processflow.pfassessmentShowAssessment',$toViewArr);
		// 		} catch (Exception $e) {
		// 			return $e;
		// 		}

		// 	} else {
		// 		return ($isSelfAssess ? false  : redirect('employee/dashboard/processflow/HeaderThree/'.$appid.'/'.$h3)->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Sub Category does not exist or has been assessed.']));
		// 	}
		// }

		public function ShowAssessments(Request $request,$appid,$h3, $isMon = false,$isSelfAssess = false){
			AjaxController::createMobileSessionIfMobile($request);
			// return response()->json([!FunctionsClientController::existOnDB('assessmentcombinedduplicate',[['asmtH3ID_FK',$h3],['appid',$appid],['monid',$isMon],['selfassess',($isSelfAssess ? 1 : null)]])]);
			if(isset($appid) && FunctionsClientController::isExistOnAppform($appid) && FunctionsClientController::existOnDB('asmt_h1',[['asmtH1ID',$h3]]) && !FunctionsClientController::existOnDB('assessmentcombinedduplicate',[['asmtH3ID_FK',$h3],['appid',$appid],['monid',$isMon],['selfassess',($isSelfAssess ? 1 : null)]]) && in_array(true, AjaxController::isSessionExist(['uData','employee_login']))){
				try {
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$whereClause = array(['assessmentcombined.assessmentStatus',1],['appform.appid',$appid],['asmt_h1.apptype',$data->hfser_id],['asmt_h1.asmtH1ID',$h3]);
					$toSelect = array('assessmentcombined.asmtComb as id','assessmentcombined.assessmentName as description','assessmentcombined.asmtH3ID_FK as h3Header','assessmentcombined.headingText as otherHeading', 'assessmentcombined.assessmentSeq as sequence','asmt_h3.asmtH2ID_FK as idForBack','asmt_title.title_name as h1HeadBack','asmt_title.title_code as h1HeadID','asmt_h1.h1name as h2HeadBack','asmt_h1.partID as h2HeadID','asmt_h2.h2name as h3HeadBack','asmt_h2.asmtH2ID as h3HeadID','asmt_h3.h3name as h4HeadBack','asmt_h3.asmtH3ID as h4HeadID');
					$headData = AjaxController::forAssessmentHeaders($whereClause,$toSelect,2);
					$toViewArr = [
						'data' => $data,
						'head' => $headData,
						'address' => url('employee/dashboard/processflow/HeaderThree/'.$appid.'/'),
						'part' => $h3,
						'isMon' => $isMon,
						'crumb' => (isset($headData[0]) ? [array('id' => $headData[0]->h1HeadID,'desc' => $headData[0]->h1HeadBack, 'beforeAddress' => 'MAIN'),array('id' => $headData[0]->h2HeadID,'desc' => $headData[0]->h2HeadBack, 'beforeAddress' => 'HeaderOne')/*,array('id' => $headData[0]->h3HeadID,'desc' => $headData[0]->h3HeadBack,'beforeAddress' => 'HeaderTwo') ,array('id' => $headData[0]->idForBack,'desc' => $headData[0]->h4HeadBack,'beforeAddress' => 'HeaderThree')*/] : [])
					];
					return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee.processflow.pfassessmentShowAssessment',$toViewArr);
				} catch (Exception $e) {
					return $e;
				}

			} else {
				return ($isSelfAssess ? false  : redirect('employee/dashboard/processflow/HeaderThree/'.$appid.'/'.$h3)->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Sub Category does not exist or has been assessed.']));
			}
		}

		// public function SaveAssessments (Request $request, $isSelfAssess = false){
		// 	$arrOfUnneeded = array('_token','appid','part');
		// 	$arrForCheck = $request->except($arrOfUnneeded);
		// 	if(!isset($request->appid) || count($arrForCheck) <= 0 ){
		// 		return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No items to pass.']);
		// 	}
			
		// 	$getOnDBID = $sample = array();
		// 	$res = null;
		// 	if(isset($request->appid) && FunctionsClientController::isExistOnAppform($request->appid) && FunctionsClientController::existOnDB('asmt_h3',[['asmtH3ID',$request->part]]) && in_array(true, AjaxController::isSessionExist(['uData','employee_login']))){
		// 		try {
		// 			if(DB::table('assessmentcombinedduplicate')->where([['asmtH3ID_FK',$request->part],['appid',$request->appid],['monid',$request->monid],['selfassess',($isSelfAssess ? 1 : null)]])->count() <= 0){

		// 				$data = AjaxController::getAllDataEvaluateOne($request->appid);
		// 				$filteredAssessment = $request->except($arrOfUnneeded);
		// 				$dataFromDB = AjaxController::forAssessmentHeaders(array(['appform.appid',$request->appid],['asmt_h1.apptype',$data->hfser_id],['asmt_h3.asmtH3ID',$request->part]),array('asmt_h1.*','asmt_h2.*','asmt_h3.*'))[0];
		// 				$uData = AjaxController::getCurrentUserAllData();
		// 				foreach ($filteredAssessment as $key => $value) {
		// 					if(is_numeric($key) && !in_array($key, $getOnDBID)){
		// 						$res = DB::table('assessmentcombined')->whereIn('asmtComb',[$key])->select('asmtComb','assessmentName','assessmentSeq','headingText')->first();
		// 						$forInsertArray = array('asmtComb_FK' => $res->asmtComb, 'assessmentName' => $res->assessmentName, 'asmtH3ID_FK' => $request->part, 'h3name' => $dataFromDB->h3name, 'asmtH2ID_FK' => $dataFromDB->asmtH2ID, 'h2name' => $dataFromDB->h2name, 'asmtH1ID_FK' => $dataFromDB->asmtH1ID, 'h1name' => $dataFromDB->h1name, 'evaluation' => ($value['comp'] == 'false' ? 0 : ($value['comp'] == 'NA' ? 'NA' : 1)), 'remarks' => $value['remarks'], 'assessmentSeq' => $res->assessmentSeq, 'evaluatedBy'=> ($uData['cur_user'] != 'ERROR' ? $uData['cur_user'] : (session()->has('uData') ? session()->get('uData')->uid :'UNKOWN, '.$request->ip())), 'assessmentHead' => $res->headingText, 'monid' => $request->monid, 'selfassess' => ($isSelfAssess ? $isSelfAssess : null), 'appid' => $request->appid);
		// 						// (isset($request->monid) && $request->monid > 0 ? $forInsertArray['monid'] = $request->monid : '');
		// 						DB::table('assessmentcombinedduplicate')->insert($forInsertArray);
		// 						array_push($getOnDBID, $key);
		// 					}
		// 				}
		// 				$urlToRedirect = ($isSelfAssess ? url('client1/apply/HeaderThree/'.$request->appid.'/'.$dataFromDB->asmtH2ID) : url('employee/dashboard/processflow/HeaderThree/'.$request->appid.'/'.$dataFromDB->asmtH2ID.'/'.(isset($request->monid) && $request->monid > 0 ? $request->monid : '')));
		// 				$toViewArr = [
		// 					'redirectTo' => $urlToRedirect
		// 				];
		// 				return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee/assessment/operationSuccess',$toViewArr);
		// 			}
		// 		} catch (Exception $e) {
		// 			return $e;
		// 		}
		// 	}
		// 	return ($isSelfAssess ? false : back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Item not found on DB.']));
			
		// }

		public function SaveAssessments (Request $request, $isSelfAssess = false){
			$arrOfUnneeded = array('_token','appid','part');
			$arrForCheck = $request->except($arrOfUnneeded);
			if(!isset($request->appid) || count($arrForCheck) <= 0 ){
				return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'No items to pass.']);
			}
			
			$getOnDBID = $sample = array();
			$res = null;
			if(isset($request->appid) && FunctionsClientController::isExistOnAppform($request->appid) && FunctionsClientController::existOnDB('asmt_h1',[['asmtH1ID',$request->part]]) && in_array(true, AjaxController::isSessionExist(['uData','employee_login']))){
				try {
					if(DB::table('assessmentcombinedduplicate')->where([['asmtH3ID_FK',$request->part],['appid',$request->appid],['monid',$request->monid],['selfassess',($isSelfAssess ? 1 : null)]])->count() <= 0){

						$data = AjaxController::getAllDataEvaluateOne($request->appid);
						$filteredAssessment = $request->except($arrOfUnneeded);
						$dataFromDB = AjaxController::forAssessmentHeaders(array(['appform.appid',$request->appid],['asmt_h1.apptype',$data->hfser_id],['asmt_h1.asmtH1ID',$request->part]),array('asmt_h1.*','asmt_h2.*','asmt_h3.*','asmt_title.title_code'))[0];
						$uData = AjaxController::getCurrentUserAllData();
						foreach ($filteredAssessment as $key => $value) {
							if(is_numeric($key) && !in_array($key, $getOnDBID)){
								$res = DB::table('assessmentcombined')->whereIn('asmtComb',[$key])->select('asmtComb','assessmentName','assessmentSeq','headingText')->first();
								$forInsertArray = array('asmtComb_FK' => $res->asmtComb, 'assessmentName' => $res->assessmentName, 'asmtH3ID_FK' => $request->part, 'h3name' => $dataFromDB->h3name, 'asmtH2ID_FK' => $dataFromDB->asmtH2ID, 'h2name' => $dataFromDB->h2name, 'asmtH1ID_FK' => $dataFromDB->asmtH1ID, 'partID' => $dataFromDB->title_code, 'h1name' => $dataFromDB->h1name, 'evaluation' => ($value['comp'] == 'false' ? 0 : ($value['comp'] == 'NA' ? 'NA' : 1)), 'remarks' => $value['remarks'], 'assessmentSeq' => $res->assessmentSeq, 'evaluatedBy'=> ($uData['cur_user'] != 'ERROR' ? $uData['cur_user'] : (session()->has('uData') ? session()->get('uData')->uid :'UNKOWN, '.$request->ip())), 'assessmentHead' => $res->headingText, 'monid' => $request->monid, 'selfassess' => ($isSelfAssess ? $isSelfAssess : null), 'appid' => $request->appid);
								// (isset($request->monid) && $request->monid > 0 ? $forInsertArray['monid'] = $request->monid : '');
								DB::table('assessmentcombinedduplicate')->insert($forInsertArray);
								array_push($getOnDBID, $key);
							}
						}
						$urlToRedirect = ($isSelfAssess ? url('client1/apply/HeaderOne/'.$request->appid.'/'.$dataFromDB->title_code) : url('employee/dashboard/processflow/HeaderOne/'.$request->appid.'/'.$dataFromDB->title_code.'/'.(isset($request->monid) && $request->monid > 0 ? $request->monid : '')));
						$toViewArr = [
							'redirectTo' => $urlToRedirect
						];
						return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee/assessment/operationSuccess',$toViewArr);
					}
				} catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return $e;
				}
			}
			return ($isSelfAssess ? false : back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Item not found on DB.']));
			
		}



		public function SaveAssessmentsMobile (Request $request,$isMobile = false){
			if($isMobile && $isMobile = '8c4ec756c0b63a005843002be03e068c' && $this->agent){
				AjaxController::SystemLogs($request->all());
				return json_encode(array('qwe'));
			}
		}



		public function GenerateReportAssessment (Request $request, $appid, $monid = null, $isSelfAssess = null){
			$reco = $otherDet = null;
			if(FunctionsClientController::isExistOnAppform($appid) && FunctionsClientController::existOnDB('assessmentcombinedduplicate',array(['assessmentcombinedduplicate.appid',$appid]))){
				$uInf = AjaxController::getAllDataEvaluateOne($appid);


				if(isset($request->reco) && $request->isMethod('post') && in_array(true, AjaxController::isSessionExist(['employee_login'])) && !FunctionsClientController::existOnDB('assessmentrecommendation',array(['appid',$appid],['selfAssess',$isSelfAssess],['monid',$monid]))){
					$uData = AjaxController::getCurrentUserAllData();

					$isSent = DB::table('assessmentrecommendation')->insert(['choice' => $request->choice, 'details' => $request->details, 'valfrom' => $request->vf, 'valto' => $request->vto, 'days' => $request->days, 'appid' => $request->appid, 'selfAssess' => $isSelfAssess , 'monid' => $monid, 'noofbed' => $request->noofbed, 'noofdialysis' => $request->noofdialysis, 'conforme' => $request->conformee, 'conformeDesignation' => $request->conformeeDes, 'evaluatedby' => $uData['cur_user']]);

					if(!$isSent){
						return redirect('employee/dashboard/processflow/parts/'.$appid)->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error Occured. Please try again later']);
					}
				}

				// if(!isset($monid)){
					if(!FunctionsClientController::existOnDB('assessmentrecommendation',array(['appid',$appid],['selfAssess',$isSelfAssess],['monid',$monid])) && in_array(true, AjaxController::isSessionExist(['employee_login'])) && $isSelfAssess == null){
						$uInf->isMon = $monid;
						$arrRet = [
							'uInf' => $uInf,
							'mon' =>  $monid
						];
						return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee.processflow.pfassessmentrecommendation',$arrRet);
					}

					$reco = DB::table('assessmentrecommendation')->where([['appid',$appid],['selfAssess',$isSelfAssess],['monid',$monid]])->first();
				// }

				if(!FunctionsClientController::existOnDB('mon_form',[['monid',$monid],['assessmentStatus',1]])){
					DB::table('mon_form')->where('monid',$monid)->update(['assessmentStatus' => 1]);
				}

				if(!isset($isSelfAssess) && FunctionsClientController::existOnDB('appform',array(['appid',$appid],['isInspected',null]))){
					$evaluation = (DB::table('assessmentcombinedduplicate')->where([['appid',$appid],['monid',$monid],['selfassess',$isSelfAssess]])->whereNotin('evaluation',[1,'NA'])->exists() == true ? 2 : 1);
					$empData = AjaxController::getCurrentUserAllData();
					DB::table('appform')->where('appid',$appid)->update(['isInspected' => $evaluation, 'inspecteddate' => $empData['date'], 'inspectedtime' => $empData['time'], 'inspectedipaddr' => $empData['ip'], 'inspectedby' => $empData['cur_user']]);
				}

				$assessor = array();
				$dataFromDB = DB::table('assessmentcombinedduplicate')->where([['assessmentcombinedduplicate.appid',$appid],['selfAssess',$isSelfAssess],['monid',$monid]])->orderBy('assessmentSeq','ASC')->get();	
				foreach ($dataFromDB as $key) {
					if(!in_array($key->evaluatedBy, $assessor)){
						array_push($assessor, $key->evaluatedBy);
					}	
				}
				$onWhereClause = (count($assessor) > 0 ? $assessor : []);

				$arrForImprovement = $arrForCompliance = array();

				if(count($dataFromDB) > 0){
					foreach ($dataFromDB as $key => $value) {
						if($value->evaluation === 1 && isset($value->remarks) && !empty($value->remarks)){
							$arrForImprovement[$key] = $value;
						}
						if(!in_array($value->evaluation, [1,2,'NA'])){
							$arrForCompliance[$key] = $value;
						}
					}
				}

				if(isset($monid)){
					$novForm = DB::table('nov_issued')->where('monid',$monid)->first();
					if($novForm != null){
						$novFormArr = explode(',', $novForm->novdire);
						foreach ($novFormArr as $key) {
							$otherDet['mon']['NOV'][] = (DB::table('nov')->select('novdesc','novid_directions')->where('novid_directions',$key)->first() ?? null);
						}
						$otherDet['mon']['NOVDetails'] = $novForm;
					}
				}

				$data = [
					'reports' => $dataFromDB,
					'assessor' => DB::table('x08')->whereIn('uid',$onWhereClause)->get(),
					'reco' => $reco,
					'uInf' => $uInf,
					'otherReports' => [$arrForImprovement,$arrForCompliance],
					'isMon' => $monid,
					'otherDetails' => $otherDet
				];
				return AjaxController::sendTo($isSelfAssess,$this->agent,$request->all(),'employee/processflow/pfassessmentgeneratedreport',$data);

			} else {
				return ($isSelfAssess ? false : back()->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'Assessment records not found.']));
			}
		}


		public function AssessmentProcessFlow(Request $request , $session_equiv = false)
		{
			try 
			{
				($session_equiv !== false ? self::sessionForMobile($session_equiv) : null);
				$data = AjaxController::getAllApplicantsProcessFlow();
				$arrfilter = [['isPayEval','==',1],['isrecommended','==',1],['isCashierApprove','==',1],['isInspected','==',null],['proposedWeek','!=',null], ['hfser_id','in_array',['LTO','COA']]];
				$currentuser = AjaxController::getCurrentUserAllData();
                return ($this->agent && $session_equiv ? response()->json(array('data' => AjaxController::filterApplicantData($data,$arrfilter))) : view('employee.processflow.pfassessment', ['BigData' => $data, 'currentuser' => $currentuser]));
			} 
			catch (Exception $e) 
			{
				dd($e);
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pfassessment');
			}
		}

		public function AssessmentShowEach(Request $request, $appid, $apptype, $otherApplication = false)
		{
			// if(DB::table('appform')->where([['appid', $appid],['isrecommended',1], ['isInspected',null], ['isCashierApprove', 1]])->count() <= 0){
			// 	return back();
			// }
			//temporary
			$charCombined = $checkExistMon = $checkHeadersDB = $assesed = $selectFromDB = $whereAnswer = $table = $whereClauseDB = $field = $otherhfserID = null;
			$appidReal = $appid;
			$applyType = 'license';
			$firstLevel = array();
			if ($request->isMethod('get')) 
			{
				if(strtolower($apptype) !== 'mon' && strtolower($apptype) !== 'surv'){
					$table = 'app_assessment';
					$selectFromDB = array('DOHAssessment');
					$whereClause = [['x08_ft.appid','=',$appid],['serv_asmt.hfser_id', '=',$apptype]];
					$appformFetch = DB::table('appform')->where('appid',$appid)->select('uid','hfser_id','aptid')->get()->first();
					if(!empty($appformFetch)){
						$charCombined = $appformFetch->uid.'_'.$appformFetch->hfser_id.'_'.$appformFetch->aptid.'_'.$appidReal;
						if(DB::table('app_assessment')->where('appid',$charCombined)->count() > 0 || $appformFetch->hfser_id != $apptype){
							// return redirect('employee/dashboard/processflow/assessment/view/'.$appid.'/'.$apptype);
							// dd('Redirecting you to page');
						}
						$whereAnswer = $charCombined;
						$field = 'DOHAssessment';
						$whereClauseDB = 'appid';
						
					} else {
						return redirect('employee/dashboard/processflow/assessment/');
						dd('Wrong appid');
					}
				} else if(strtolower($apptype) == 'mon') {
					$table = 'mon_form';
					$selectFromDB = array('DOHMonitoring');
					$checkExistMon = DB::table('mon_form')->where([['monid',$appid],['type_of_faci',$otherApplication]])->count();
					if($checkExistMon < 1){
						return redirect('employee/dashboard/others/monitoring/inspection');
						dd('wrong monitoring');
					}
					$appformFetch = DB::table('mon_form')->where('monid',$appid)->select('DOHMonitoring')->get()->first();
					if(!empty($appformFetch->DOHMonitoring)){
						// return redirect('employee/dashboard/processflow/assessment/view/'.$appid.'/'.$apptype.'/'.$otherApplication);
						// dd('Redirecting you to page');
					}
					$whereAnswer = $appidReal;
					$field = 'DOHMonitoring';
					$whereClauseDB = 'monid';
					$applyType = 'mon';
					// dd(DB::table($table)->where($whereClauseDB,$whereAnswer)->select($field)->get()->first());
					$appid = DB::table('mon_form')->select('appid')->where('monid','=',$appid)->first()->appid;
					if(empty($appid)){
						return redirect('employee/dashboard/others/monitoring');
						dd('Redirecting you to page');
					}
					$otherhfserID = "SELECT `hfser_id` FROM `appform` WHERE `appid` = (SELECT `appid` FROM `mon_form` WHERE `monid` = '$appidReal')";
					$otherhfserID = DB::select($otherhfserID)[0]->hfser_id;
					$whereClause = [['x08_ft.appid','=',$appid],['facilitytyp.facid', '=',$otherApplication],['serv_asmt.hfser_id', '=',$otherhfserID]];
				}
				else if(strtolower($apptype) == 'surv') {
					$table = 'surv_form';
					$selectFromDB = array('DOHSurveillance');
					$checkExistSurv = DB::table('surv_form')->where([['survid',$appid],['type_of_faci',$otherApplication]])->count();
					if($checkExistSurv < 1){
						return redirect('employee/dashboard/others/monitoring/inspection');
						dd('wrong monitoring');
					}
					$appformFetch = DB::table('surv_form')->where('survid',$appid)->select('DOHSurveillance')->get()->first();
					if(!empty($appformFetch->DOHSurveillance)){
						// return redirect('employee/dashboard/processflow/assessment/view/'.$appid.'/'.$apptype.'/'.$otherApplication);
						// dd('Redirecting you to page');
					}
					$whereAnswer = $appidReal;
					$field = 'DOHSurveillance';
					$whereClauseDB = 'survid';
					$applyType = 'surv';
					// dd(DB::table($table)->where($whereClauseDB,$whereAnswer)->select($field)->get()->first());
					$appid = DB::table('surv_form')->select('appid')->where('survid','=',$appid)->first()->appid;
					if(empty($appid)){
						return redirect('employee/dashboard/processflow/assessment/'.$appid.'/'.$apptype.'/view');
						dd('Redirecting you to page');
					}
					$otherhfserID = "SELECT `hfser_id` FROM `appform` WHERE `appid` = (SELECT `appid` FROM `surv_form` WHERE `survid` = '$appidReal')";
					$otherhfserID = DB::select($otherhfserID)[0]->hfser_id;
					$whereClause = [['x08_ft.appid','=',$appid],['facilitytyp.facid', '=',$otherApplication],['serv_asmt.hfser_id', '=',$otherhfserID]];
				}
				try 
				{
					$asmt2_col = $asmt2_loc = $levelFirst = array();
					$joinedData = null;
					$allAccess = array();
		            $joinedData = AjaxController::getHeads($whereClause,array('asmt2_loc.header_lvl1 as headers'));
					foreach ($joinedData as $key) {
						if(!in_array($key->headers, $firstLevel)){
							array_push($firstLevel,$key->headers);
						}
					}
					$headers = DB::table('asmt2_loc')->whereIn('asmt2l_id',$firstLevel)->select('asmt2l_id','asmt2l_desc')->get();
					$headers['hasNull'] = false;
					if(in_array(null,$firstLevel,true)){
						$headers['hasNull'] = true;
					}

					$assesed = (empty(DB::table($table)->where($whereClauseDB,$whereAnswer)->select($field)->get()->first()->$field) ? null : json_encode(array_keys(json_decode(DB::table($table)->where($whereClauseDB,$whereAnswer)->select($field)->get()->first()->$field,true))));
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$SELECTED = $data->uid.'_'.$data->hfser_id.'_'.$data->aptid.'_'.$appidReal;

					$urlToRedirect = asset('employee/dashboard/processflow/assessment/each/'.$appidReal.'/'.$apptype.'/');
					return ($this->agent ? response()->json(array('AppData'=>$data, 'appId'=> $appidReal, 'apptype' => $apptype, 'monType'=>$applyType, 'headers'=>$headers, 'address' => $urlToRedirect, 'realMontype' => ($otherApplication !== false ? $otherApplication : ''), 'assesed'=> $assesed)) : view('employee.processflow.pfassessmentchoose', ['AppData'=>$data, 'appId'=> $appidReal, 'apptype' => $apptype, 'monType'=>$applyType, 'headers'=>$headers, 'address' => $urlToRedirect, 'realMontype' => ($otherApplication !== false ? $otherApplication : ''), 'assesed'=> $assesed]));	
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfassessmentchoose');	
				}
			}
		}



		////// ASSESSMENT ONE
		////// ASSESSMENT ONE VIEW
		public function AssessmentOneViewProcessFlow(Request $request, $appid, $apptype, $otherApplication = false)
		{
			$dataToView = $charCompiled = $toDir = $jsonToArray = $noCharCompiled = $selfAssessmentCheck = $jsonToDB = $appform = $table = $selectFromDB = $whereClause = $recordsToCheck = $tableToUpdate = $slug = $fieldsOnUpdate = $allUserDetails = $checkExistMon = $currentAssessment = $storedAssessment = $merged = $checkForStatus = $urlToRedirect = $tableNames = $currentRequest = $sendDB = null;
			$uncompliedList = $exceptedData = array();
			try 
			{
				($request->isMobile === "true" && $this->agent ? self::sessionForMobile($request->uid) : null);
				if($this->agent){
					$apptype = $request->apptype;
					$appid = $request->appid;
					$request = json_decode($request->assessment);
				}
				$allUserDetails = AjaxController::getCurrentUserAllData();
				$exceptData = array('_token','appID','facilityname','monType','org','header');
				if(strtolower($apptype) !== 'mon' && strtolower($apptype) !== 'surv'){//licensing
					$tableNames = 'appform';
					$urlToRedirect = asset('employee/dashboard/processflow/assessment/'.$appid.'/'.$apptype);
					$selectFromDB = array('DOHAssessment');
					if(DB::table('appform')->where('appid',$appid)->count() < 1){
						return redirect('employee/dashboard/processflow/assessment/');
						dd('redirecting you to page');
					}
					if((!empty($request) && $this->agent) || !empty($request->all())){
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
				} else if(strtolower($apptype) == 'mon') {//monitoring
					$urlToRedirect = asset('employee/dashboard/processflow/assessment/'.$appid.'/MON/'.$otherApplication);
					$selectFromDB = array('DOHMonitoring');
					$charCompiled = $appid;
					$table = 'mon_form';
					$tableNames = $table;
					$whereClause = 'monid';
					$fieldsOnUpdate = array(
						'hasViolation'=>1
					);
					$checkExistMon = DB::table('mon_form')->where([['monid',$appid],['type_of_faci',$otherApplication]])->count();
					if($checkExistMon < 1){
						return redirect('employee/dashboard/others/monitoring/inspection');
						dd('wrong monitoring');
					}
				} else if(strtolower($apptype) == 'surv') {//surveillance
					$urlToRedirect = asset('employee/dashboard/others/surveillance');
					$selectFromDB = array('DOHSurveillance');
					$charCompiled = $appid;
					$table = 'surv_form';
					$tableNames = $table;
					$whereClause = 'survid';
					$fieldsOnUpdate = array(
						'hasViolation'=>1
					);
					$checkExistMon = DB::table('surv_form')->where([['survid',$appid],['type_of_faci',$otherApplication]])->count();
					if($checkExistMon < 1){
						return redirect('employee/dashboard/others/surveillance');
						dd('wrong surveillance');
					}
				}



				$dataToView = DB::table($table)->where($whereClause,$charCompiled)->select($selectFromDB)->get()->first();
				$selectFromDB = implode('', $selectFromDB);
				if(empty($dataToView->$selectFromDB) || $dataToView === null){
					if((!empty($request) && $this->agent) || !empty($request->all())){
						$recordsToCheck = (!empty($request) && $this->agent ? $request: $request->all());
						$exceptedData = (!empty($request) && $this->agent ? $recordsToCheck: $request->all());
						foreach ($recordsToCheck as $key => $value) {
							if($value == false && $value != null){
								if(!in_array($key, $uncompliedList)){
									array_push($uncompliedList, $key);
								}
							}
							if(in_array($key, $exceptData)){
								unset($exceptedData->key);
							}
						}
						if(!empty($uncompliedList) && (strtolower($apptype) !== 'mon' || strtolower($apptype) !== 'surv')){	
							DB::table($tableNames)
							->where($whereClause,$appid)							
							->update($fieldsOnUpdate);
						}
						$jsonToDB = json_encode(array($request->header => ($this->agent ? $exceptedData : $request->except($exceptData))));
						if(strtolower($apptype) == 'mon'){
							DB::table($table)->where($whereClause,$appid)->update(['DOHMonitoring' => $jsonToDB]);
						} else if(strtolower($apptype) == 'surv'){
							DB::table($table)->where($whereClause,$appid)->update(['DOHSurveillance' => $jsonToDB]);
						} else {
							if(DB::table($table)->where($whereClause,$charCompiled)->count() <= 0 ){
								DB::table($table)->insert([
							    ['appid' => $charCompiled,'t_date' => AjaxController::getCurrentUserAllData()['date'],'t_time' => AjaxController::getCurrentUserAllData()['time'],'assessedby' => AjaxController::getCurrentUserAllData()['cur_user'],'DOHAssessment' => $jsonToDB]
								]);
							} else {
								DB::table($table)->where($whereClause,$charCompiled)
								->update(['appid' => $charCompiled,'t_date' => AjaxController::getCurrentUserAllData()['date'],'t_time' => AjaxController::getCurrentUserAllData()['time'],'assessedby' => AjaxController::getCurrentUserAllData()['cur_user'],'DOHAssessment' => $jsonToDB]);
							}
						}
						// $dataToView = json_encode($request->except($exceptData));
						$dataToView = json_encode($exceptedData);
						// return 'add';
					} else {
						return redirect('employee/dashboard/processflow/assessment');
						dd('ERROR, PLEASE CONTACT ADMIN IMMEDIATELY');
					}
				} else {
					$checkForStatus = (is_null(DB::table($table)->where($whereClause,$charCompiled)->select('assessmentStatus')->get()->first()) ? null : DB::table($table)->where($whereClause,$charCompiled)->select('assessmentStatus')->get()->first()->assessmentStatus);
					$dataToView = $dataToView->$selectFromDB;
					if($checkForStatus  == 0){
						$exceptedData = (!empty($request) && $this->agent ? $request: $request->all());
						foreach ($exceptedData as $key => $value) {
							if(in_array($key, $exceptData)){
								unset($exceptedData->key);
							}
						}
						if((!empty($request) && $this->agent) || !empty($request->all())){
							$storedAssessment = json_decode($dataToView,true);
							// $currentAssessment = array($request->header => $request->except($exceptData));
							$currentAssessment = array($request->header => ($this->agent ? $exceptedData : $request->except($exceptData)));
							if(!array_key_exists($request->header,$storedAssessment)){
								$merged = json_encode(array_merge($currentAssessment,$storedAssessment));
								// dd(array_merge($currentAssessment,$storedAssessment));
								$sendDB = DB::table($table)
									->where($whereClause,$charCompiled)							
									->update([$selectFromDB => $merged]);
							} else {
								$sendDB = true;
								// return view('employee/assessment/operationSucess');
							}
						} /*else {
							return redirect('employee/dashboard/processflow/assessment/view/'.$appid.'/'.$apptype.'/'.$otherApplication);
							dd('Redirecting you to page');
						}*/
						// return 'update';
					} elseif($checkForStatus === 1) { // FOR POSSIBLE CHANGES ONLY. PLEASE DON'T MAKE HILABOT
						dd('Unknown Error occured. Please try again later.');
					}
				}
				return (($sendDB  ? ($this->agent ? response()->json(array('status' => 'success', 'message' => 'success')) : view('employee/assessment/operationSuccess', ['redirectTo' => $urlToRedirect])) : ($this->agent ? response()->json(array('status' => 'error', 'message' => 'error')) : view('employee/assessment/operationSuccess', ['redirectTo' => $urlToRedirect]))));
				// $dataToView = json_decode($dataToView,true);
				// $toDir = explode(',',$dataToView['filename']);
				// unset($dataToView['filename']);
				// unset($dataToView['header']);
				// $appform = DB::table('appform')->where('appid',$appid)->get()->first();
				// return view('employee.processflow.pfassessmentoneview',['data' => json_encode($dataToView),'file'=>$toDir,'selfCheck'=>$selfAssessmentCheck, 'appform' => $appform]);
			} 
			catch (Exception $e) 
			{
				return $e;
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pfassessmentoneview');
			}
		}

		////// ASSESSMENT
		////// ASSESSMENT ONE
		/////  syrel redo
		// !@#
		public function AssessmentOneProcessFlow(Request $request, $appid, $apptype, $choosen, $otherApplication = false)
		{
			// $headers = null;
			// $headersFromDB = array();
		
			$charCombined = $checkExistMon = $currentUser = null;
			$appidReal = $appid;
			$applyType = 'license';
			$origChoosen = $choosen;
			$choosen = (strtoupper($choosen) !== 'OTHERS'? ['asmt2_loc.header_lvl1',$choosen] : ['asmt2_loc.asmt2l_id','<>',null]);
			$firstLevel = array();
			if ($request->isMethod('get')) 
			{
				if($this->agent){
					self::sessionForMobile($request->uid);
				}
				
				if(strtolower($apptype) !== 'mon' && strtolower($apptype) !== 'surv'){
					$whereClause = [['x08_ft.appid','=',$appid],['serv_asmt.hfser_id', '=',$apptype], $choosen];
					$appformFetch = DB::table('appform')->where('appid',$appid)->select('uid','hfser_id','aptid')->get()->first();
					if(!empty($appformFetch)){
						$charCombined = $appformFetch->uid.'_'.$appformFetch->hfser_id.'_'.$appformFetch->aptid.'_'.$appid;
						if(DB::table('app_assessment')->where('appid',$charCombined)->count() > 0 || $appformFetch->hfser_id != $apptype){
							// return redirect('employee/dashboard/processflow/assessment/view/'.$appid.'/'.$apptype);
							// dd('Redirecting you to page');
						}
					} else {
						return redirect('employee/dashboard/processflow/assessment/');
						dd('Wrong appid');
					}
				} else if(strtolower($apptype) == 'mon') { //monitoring
					$checkExistMon = DB::table('mon_form')->where([['monid',$appid],['type_of_faci',$otherApplication]])->count();
					if($checkExistMon < 1){
						return redirect('employee/dashboard/others/monitoring/inspection');
						dd('wrong monitoring');
					}
					$appformFetch = DB::table('mon_form')->where('monid',$appid)->select('DOHMonitoring')->get()->first();
					if(!empty($appformFetch->DOHMonitoring)){
						// return redirect('employee/dashboard/processflow/assessment/view/'.$appid.'/'.$apptype.'/'.$otherApplication);
						// dd('Redirecting you to page');
					}
					$applyType = 'mon';
					$appid = DB::table('mon_form')->select('appid')->where('monid','=',$appid)->first()->appid;
					if(empty($appid)){
						return redirect('employee/dashboard/processflow/assessment/'.$appid.'/'.$apptype.'/view');
						dd('Redirecting you to page');
					}
					$whereClause = [['x08_ft.appid','=',$appid],['facilitytyp.facid', '=',$otherApplication],$choosen];
				}
				else if(strtolower($apptype) == 'surv') { //surveillance
					$checkExistMon = DB::table('surv_form')->where([['survid',$appid],['type_of_faci',$otherApplication]])->count();
					if($checkExistMon < 1){
						return redirect('employee/dashboard/others/monitoring/inspection');
						dd('wrong monitoring');
					}
					$appformFetch = DB::table('surv_form')->where('survid',$appid)->select('DOHSurveillance')->get()->first();
					if(!empty($appformFetch->DOHSurveillance)){
						// return redirect('employee/dashboard/processflow/assessment/view/'.$appid.'/'.$apptype.'/'.$otherApplication);
						// dd('Redirecting you to page');
					}
					$applyType = 'mon';
					$appid = DB::table('surv_form')->select('appid')->where('survid','=',$appid)->first()->appid;
					if(empty($appid)){
						return redirect('employee/dashboard/processflow/assessment/'.$appid.'/'.$apptype.'/view');
						dd('Redirecting you to page');
					}
					$whereClause = [['x08_ft.appid','=',$appid],['facilitytyp.facid', '=',$otherApplication],$choosen];
				}
				// if(strtolower($origChoosen) === 'others'){
				// 	$headers = (is_null(DB::table('app_assessment')->where('appid',$appid)->select('headers')->get()->first()) ? null : json_decode(DB::table('app_assessment')->where('appid',$appid)->select('headers')->get()->first()->headers));
				// 	if(is_null($headers)){
				// 		return redirect('employee/dashboard/processflow/assessment');
				// 	}
				// 	foreach ($headers as $key => $head) {
				// 		if($key !== 'hasNull'){
				// 			if(!in_array($head->asmt2l_id, $headersFromDB)){
				// 				array_push($headersFromDB, $head->asmt2l_id);
				// 			}
				// 		}
				// 	}
				// }
				try 
				{
					$asmt2_col = $asmt2_loc = $levelFirst = array();
					$joinedData = null;
					$allAccess = $filenames = array();
					$countColoumn = DB::SELECT("SELECT count(*) as 'all' FROM information_schema.columns WHERE table_name = 'asmt2'")[0]->all -1;
					// $currentUser =(!$this->agent ? AjaxController::getCurrentUserAllData()['cur_user'].','.(empty(AjaxController::getCurrentUserAllData()['position']) ? 'NONE' : AjaxController::getCurrentUserAllData()['position']) : "");
					$currentUser = AjaxController::getCurrentUserAllData()['cur_user'].','.(empty(AjaxController::getCurrentUserAllData()['position']) ? 'NONE' : AjaxController::getCurrentUserAllData()['position']) ;
					$joinedData = DB::table('x08_ft')
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
		            ->where($whereClause)
		            ->distinct();
		            // if(!empty($headersFromDB)){
		            if(strtolower($origChoosen) === 'others'){
		            	$joinedData->whereNull('asmt2_loc.header_lvl1');
		            }
		            $joinedData = json_decode($joinedData->get(),true);
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
					return ($this->agent ? response()->json(array('AppData'=>$data, 'appId'=> $appidReal, 'joinedData'=>$joinedData, 'apptype' => $apptype, 'filenames'=>$filenames, 'monType'=>$applyType, 'header'=>$origChoosen,'org'=>$SELECTED, 'assessor' => $currentUser)) : view('employee.processflow.pfassessmentone', ['AppData'=>$data, 'appId'=> $appidReal, 'joinedData'=>$joinedData, 'apptype' => $apptype, 'filenames'=>$filenames, 'monType'=>$applyType, 'header'=>$origChoosen,'org'=>$SELECTED, 'assessor' => $currentUser]));	
				} 
				catch (Exception $e) 
				{
					// dd('catch');
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfassessmentone');	
				}
			}
			// if ($request->isMethod('post')) 
			// {
			// 	try 
			// 	{
			// 		$Cur_useData = AjaxController::getCurrentUserAllData();
			// 		// 	chckOrNot [] , rmks [], num, AsId [], id, SeldID [],
			// 		// $Gas = DB::table('app_assessment')->where('appid', '=', $request->id)->first();
			// 		$X = 0;
			// 		for ($i=0; $i < $request->num; $i++) { 
			// 			$test = DB::table('app_assessment')
			// 					->where('app_assess_id', '=', $request->SeldID[$i])
			// 					->update([
			// 						'isapproved' => $request->chckOrNot[$i],
			// 						'remarks' => $request->rmks[$i],
			// 						't_date' => $Cur_useData['date'],
			// 						't_time' => $Cur_useData['time'],
			// 						'assessedby' => $Cur_useData['cur_user'],
			// 						// 'uid' => $Cur_useData['cur_user']
			// 					]);
			// 			}
			// 			if ($request->hasNotApproved == 0) {$Stat = 'FR';$x = 1;} 
			// 			else { $Stat = 'RI';$x = 0;}
			// 			$update = array(
			// 							'status'=>$Stat,
			// 							'isInspected'=> $x,
			// 							'inspecteddate'=> $Cur_useData['date'],
			// 							'inspectedtime'=> $Cur_useData['time'],
			// 							'inspectedipaddr'=> $Cur_useData['ip'],
			// 							'inspectedby'=> $Cur_useData['cur_user'],
			// 						);
			// 			$test = DB::table('appform')->where('appid', '=', $request->id)->update($update);
			// 			$selected = AjaxController::getUidFrom($request->id);
			// 			AjaxController::notifyClient($selected, 4);
			// 			if ($test) {
			// 				return 'DONE';
			// 			} else {
			// 				$TestError = $this->SystemLogs('No data has been modfied in appform table. (AssessmentOneProcessFlow)');
			// 				return 'ERROR';
			// 			}
			// 			return $request->hasApproved;	
			// 	} 
			// 	catch (Exception $e) 
			// 	{
			// 		AjaxController::SystemLogs($e);
			// 		return 'ERROR';
			// 		// return response()->json($e);
			// 	}
			// }
		}

		public function AssessmentDisplay($appid, $apptype, $otherApplication = false, $forTest = false)
		{
			$charCompiled = $noCharCompiled = $appform = $table = $selectFromDB = $whereClause = $fieldsOnUpdate = $checkExistMon = $checkStatus = $checkForStatus = $compliedToString = $dataFromDB = $mergedData = $unsortedData = $isEmptyAssess = $checkInspected = $exploded = null;
			$assessor = $filenames = array();
			$exceptData = array('_token','appID','facilityname','monType','org','header','assessor');
			$allUserDetails = AjaxController::getCurrentUserAllData();
			$fieldsOnUpdate = array('assessmentStatus' => 1);
				if(strtolower($apptype) !== 'mon' && strtolower($apptype) !== 'surv'){//licensing
					$selectFromDB = array('DOHAssessment');
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
				} else if(strtolower($apptype) == 'mon') {//monitoring
					$selectFromDB = array('DOHMonitoring');
					$charCompiled = $appid;
					$table = 'mon_form';
					$whereClause = 'monid';
					$checkExistMon = DB::table($table)->where([[$whereClause,$appid],['type_of_faci',$otherApplication]])->count();
					if($checkExistMon < 1){
						return redirect('employee/dashboard/others/monitoring/inspection');
						dd('wrong monitoring');
					}
					(DB::table($table)->where($whereClause,$charCompiled)->select('assessmentStatus')->get()->first()->assessmentStatus <= 0 ? DB::table($table)->where($whereClause,$charCompiled)->update(['assessmentStatus' => 1]) : null);
				} else if(strtolower($apptype) == 'surv'){
					$selectFromDB = array('DOHSurveillance');
					$charCompiled = $appid;
					$table = 'surv_form';
					$whereClause = 'survid';
					$checkExistMon = DB::table($table)->where([[$whereClause,$appid],['type_of_faci',$otherApplication]])->count();
					if($checkExistMon < 1){
						return redirect('employee/dashboard/others/surveillance/inspection');
						dd('wrong surveillance');
					}
					(DB::table($table)->where($whereClause,$charCompiled)->select('assessmentStatus')->get()->first()->assessmentStatus <= 0 ? DB::table($table)->where($whereClause,$charCompiled)->update(['assessmentStatus' => 1]) : null);
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
							$exploded = explode(',', $value['filename']);
							foreach ($exploded as $explodedkey => $explodedvalue) {
								if(!in_array(trim($explodedvalue), $filenames)){
									array_push($filenames, trim($explodedvalue));
									unset($dataFromDB[$key]['filename']);
								}
							}
						}
					}
					$unsortedData = call_user_func_array("array_merge", $dataFromDB);
					$testArray = array();
					foreach ($unsortedData as $key => $value) {
						$stringToFind = '!/*headCode';
						if($key !== 'filename'){
							$string = $key;
							$findSeq = strpos($string,$stringToFind);
							$part = null;
							if($findSeq !== false) {
								$findSeq +=strlen($stringToFind);
								while(substr($string,$findSeq,3) !== '!/*'){
									$part = $part.substr($string,$findSeq,1);
									$findSeq +=1;
								}
								$testArray[$part][$key] = $value;
								$findSeq = $part = null;
							}
						}
					}
					$tryLng = AjaxController::arrangeAssessment($testArray);
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
								'inspecteddate'=>$allUserDetails['date'],
								'inspectedtime'=>$allUserDetails['time'],
								'inspectedipaddr'=>$allUserDetails['ip'],
								'inspectedby'=>$allUserDetails['cur_user']
							]);
						}
					}
					$toDir = $filenames;
					if($forTest){
						$arrayFlash = array();
						$toFindOnString = array('!/*isArea','!/*desc');
						foreach ($dataToView as $key => $value) {

							if(strpos($key, 'seq32!/*') === false ){
								foreach ($toFindOnString as $str) {
									$arrayFlash[$key][] = AjaxController::filterAssessmentData($key,$str,'!/*');
									$arrayFlash[$key]['isRemark'] = (strpos($key, '!/*remarks') === false ? false : true);
								}
								$arrayFlash[$key][] = $value;
							}

						}
						return view('employee.processflow.pfassessmentDynamicView',['data' => $arrayFlash]);
					}
					return view('employee.processflow.pfassessmentoneview',['data' => json_encode($dataToView),'file'=>$toDir, 'assessor' => $assessor]);
				} else {
					return redirect('employee/dashboard/processflow/assessment/');
				}
		}

		////// ASSESSMENT ONE VIEW
		////// RECOMMENDATION
		public function RecommendationProcessFlow(Request $request)
		{
			try 
			{
				$data = AjaxController::getAllApplicantsProcessFlow();
				return view('employee.processflow.pfrecommendation', ['BigData'=>$data]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pfrecommendation');
			}
		}
		////// RECOMMENDATION 
		////// RECOMMENDATION ONE
		public function RecommendationOneProcessFlow(Request $request, $appid)
		{
			if(DB::table('appform')->where([['appid', $appid], ['isCashierApprove', 1], ['isPayEval',1], /*['isRecoForApproval', null]*/])->count() <= 0){
				return back()->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'Please finish all requirements first!.']);
			}
			if ($request->isMethod('get')) 
			{
				try 
				{
					// $canView = null;
					$data = AjaxController::getRecommendationData($appid);
					// $data1 = AjaxController::getPreAssessment($data->uid);
					$data2 = AjaxController::getAssignedMembersInTeam4Recommendation($appid);
					$canView = AjaxController::canViewFDAOOP($appid);
					switch ($data->hfser_id) {
						case 'PTC':
							$otherDetails = DB::table('hferc_evaluation')->leftJoin('x08','x08.uid','hferc_evaluation.HFERC_evalBy')->where([['appid',$appid]])->first();
							break;
						case 'COA':
						case 'LTO':
							$otherDetails = DB::table('assessmentrecommendation')->leftJoin('x08','x08.uid','assessmentrecommendation.evaluatedby')->select('assessmentrecommendation.*','x08.fname','x08.mname','x08.lname')->where('appid',$appid)->first();
							break;
						
						default:
							$otherDetails = [];
							break;
					}
					// dd($data);
					// if($data->hfser_id == 'LTO'){
					// 	$canView = AjaxController::canViewFDAOOP($appid);
					// }
					// dd($otherDetails);
					return view('employee.processflow.pfrecommendationone', ['AppData'=>$data,/*'PreAss'=>$data1,*/ 'APPID' => $appid, 'Teams4theApplication' => $data2, 'canView' => $canView, 'otherDetails' => $otherDetails]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfrecommendationone');
				}
			}
			if ($request->isMethod('post')) {
				try {
					// return $request->all();
						$Cur_useData = AjaxController::getCurrentUserAllData();
						$update = array(
							'isRecoForApproval' => $request->isOk,
							'RecoForApprovalby' => $Cur_useData['cur_user'],
							'RecoForApprovalTime' => $Cur_useData['time'],
							'RecoForApprovalDate' =>$Cur_useData['date'],
							'RecoForApprovalIpAdd' => $Cur_useData['ip'],
							'RecoRemark' => $request->desc
						);
						if ($request->isOk == 1) {
							$update['status'] = 'FA';
						} else if ($request->isOk == 0) 
						{
							$update['status'] = 'DND';
						}
						$data = DB::table('appform')->where('appid', '=', $request->id)->update($update);
						$hfserCheck = DB::table('appform')->where('appid', '=', $request->id)->first();
						if(isset($hfserCheck)){
							switch (strtolower($hfserCheck->hfser_id)) {
								case 'ptc':
									$selected = AjaxController::getUidFrom($appid);
									AjaxController::notifyClient($appid,$selected,56);
									break;
								default:
									$selected = AjaxController::getUidFrom($request->id);
									AjaxController::notifyClient($request->id,$selected,($request->isOk == 1 ? 19 : 20));
									break;
							}
						}
						return 'DONE';
				} catch (Exception $e) {
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		////// RECOMMENDATION ONE
		////// APPROVAL
		public function ApprovalProcessFlow(Request $request)
		{
			try 
			{
				$data = AjaxController::getAllApplicantsProcessFlow();
				return view('employee.processflow.pfapproval', ['BigData'=>$data]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pfapproval');
			}
		}
		public function RecommendationProcessFlowFDA(Request $request, $clientRequest = 'machines')
		{
			try 
			{	
				$extra = false;
				$clientRequest = AjaxController::isRequestForFDA($clientRequest);
				$data = AjaxController::getAllApplicantsProcessFlow();
				return view('employee.FDA.pfrecommendation', ['BigData'=>$data, 'request' => $clientRequest, 'extra' => $extra]);
			} 
			catch (Exception $e) 
			{
				dd($e);
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.FDA.pfrecommendation');
			}
		}
		public function ApprovalProcessFlowFDA(Request $request, $clientRequest = 'machines')
		{
			try 
			{	
				$extra = false;
				$clientRequest = AjaxController::isRequestForFDA($clientRequest);
				$data = AjaxController::getAllApplicantsProcessFlow();
				// dd($data);
				return view('employee.FDA.pfapprovalFDA', ['BigData'=>$data, 'request' => $clientRequest, 'extra' => $extra]);
			} 
			catch (Exception $e) 
			{
				dd($e);
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.FDA.pfapprovalFDA');
			}
		}
		////// APPROVAL
		////// APPROVAL ONE
		public function ApprovalOneProcessFlow(Request $request, $appid)
		{
			if(DB::table('appform')->where([['appid', $appid],['isrecommended',1], ['isCashierApprove', 1], ['isPayEval',1],/* ['isRecoForApproval', 1],['isApprove',null]*/])->count() <= 0){
				return redirect('employee/dashboard/processflow/approval')->with('errRet', ['errAlt'=>'warning', 'errMsg'=>'Application does not qualify on this level.']);
			}
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getRecommendationData($appid);
					// $data1 = AjaxController::getPreAssessment($data->uid);
					switch ($data->hfser_id) {
						case 'PTC':
							$otherDetails = DB::table('hferc_evaluation')->leftJoin('x08','x08.uid','hferc_evaluation.HFERC_evalBy')->where([['appid',$appid],['HFERC_eval',1]])->first();
							break;
						case 'COA':
						case 'LTO':
						$otherDetails = DB::table('assessmentrecommendation')->leftJoin('x08','x08.uid','assessmentrecommendation.evaluatedby')->select('assessmentrecommendation.*','x08.fname','x08.mname','x08.lname')->where('appid',$appid)->first();
							break;
						
						default:
							$otherDetails = null;
							break;
					}
					$canView = AjaxController::canViewFDAOOP($appid);
					$data2 = AjaxController::getAssignedMembersInTeam4Recommendation($appid);
					// dd($data);
					return view('employee.processflow.pfapprovalone', ['AppData'=>$data,/*'PreAss'=>$data1, */'APPID' => $appid, 'Teams4theApplication' => $data2, 'otherDetails' => $otherDetails, 'canView' => $canView, 'hfser_id' => $data->hfser_id]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfapprovalone');
				}
			}
			if ($request->isMethod('post')) 
			{
				$code = $license = $faci = $appform = $branchData = $hferID = $dateOnFormat = null;
				try {
						$ChckPassword = AjaxController::checkPassword($request->pass);
						if ($ChckPassword == true) 
						{
							$faci = DB::table('facilitytyp')->join('x08_ft','x08_ft.facid','facilitytyp.facid')->join('serv_type','serv_type.servtype_id','facilitytyp.servtype_id')->select('facilitytyp.facid')->where([['x08_ft.appid',$appid],['serv_type.servtype_id',1]])->get()->first()->facid;
							$appform = DB::table('appform')->where('appid',$appid)->select('hfser_id','rgnid','ocid')->first();
							$branchData = DB::table('branch')->where('regionid',$appform->rgnid)->select($appform->hfser_id)->first();
							$hferID = $appform->hfser_id;
							for ($i=strlen($branchData->$hferID); $i < (5 - strlen($branchData->$hferID)) ; $i++) { 
								$code = $code."0";
							}
							$code = $code.($branchData->$hferID+1);
							if($appform->hfser_id == 'LTO'){
								$dateOnFormat = (substr(Date('Y',strtotime($request->validity)),-2) != substr(Date('Y',strtotime($request->validityDateFrom)),-2) ? substr(Date('Y',strtotime($request->validityDateFrom)),-2).substr(Date('Y',strtotime($request->validity)),-2) : substr(Date('Y',strtotime($request->validity)),-2));
								$license =  $appform->rgnid.'-'.$code.'-'.$dateOnFormat.'-'.$faci.'-'.($appform->ocid == 'G' ? 1 : 2);
							} else if($appform->hfser_id == 'PTC'){
								$license = Date('Y',strtotime('now')).'-'.$code;
							}
							
							
							$Cur_useData = AjaxController::getCurrentUserAllData();
							$status = ($request->isOk == '1') ? 'A' : 'RA';
						 	$data = array(
						 			'isApprove' => $request->isOk,
						 			'approvedBy' => $Cur_useData['cur_user'],
						 			'approvedDate' => $Cur_useData['date'],
						 			'approvedTime' =>  $Cur_useData['time'],
						 			'approvedIpAdd' => $Cur_useData['ip'],
						 			'approvedRemark' => $request->desc,
						 			'status' => $status,
						 			'licenseNo' => $license
	 					 		);
						 	(!empty($request->validity) ? $data['validDate'] = Carbon::parse($request->validity)->toDateString() : "");
						 	(!empty($request->validityDateFrom) ? $data['validDateFrom'] = Carbon::parse($request->validityDateFrom)->toDateString() : "");
							$test = DB::table('appform')->where('appid', '=', $request->id)->update($data);
						 	// return $status
						 	// $test = true;
							if($status == 'A' && $test){
								// if($appform->hfser_id == 'LTO'){
								// 	$fromNHFR = AjaxController::nhfrFunctions($appid);
								// 	if(isset($fromNHFR->response_code)){
								// 		DB::table('appform')->where('appid',$appid)->update(['nhfrfacilitycode' => ($fromNHFR[0]->hfhudcode ?? 'ERROR ON NHFR PAGE')]);
								// 	}
								// }
								$x08 = DB::table('x08_ft')->where('appid',$appid)->get();
								if(!empty($x08)){
									foreach ($x08 as $key => $value) {
										DB::table('licensed')->insert(['appid' => $appid,'x08_ftid' => $value->appid]);
									}
								}	
							}
							if ($test) {
								DB::table('branch')->where('regionid',$appform->rgnid)->update([$hferID => ($branchData->$hferID+1)]);
								$selected = AjaxController::getUidFrom($request->id);
								AjaxController::notifyClient($request->id,$selected,($request->isOk == 1 ? 21 : 22));
								return 'DONE';
							} else {
								return 'ERROR';
							}
						}
						else
						{
							return 'WRONGPASSWORD';
						}
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function RecommendationOneProcessFlowFDA(Request $request, $appid, $reqType = 'machines')
		{
			$reqType = strtolower($reqType);
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getRecommendationData($appid);
					// $data1 = AjaxController::getPreAssessment($data->uid);
					$data2 = AjaxController::getAssignedMembersInTeam4Recommendation($appid);
					$canView = AjaxController::canViewFDAOOP($appid);
					if($reqType == 'machines'){
						$typeOfRequest = 'cdrrhr';
						if(isset($canView[0])){
							$canView[0] = false;
						}

						DB::table('appform')->where('appid', '=', $appid)->update(['FDAStatMach'=>'For Final Dicision']);
					} else{
						$typeOfRequest = 'cdrr';
						if(isset($canView[1])){
							$canView[1] = false;
						}
					}
					$approveForCurrentRequest = ($reqType == 'machines' ? $data->isRecoFDA : 1);
				
					$hasJudge = DB::table('fdacert')->where([['appid',$appid],['department',$typeOfRequest]])->exists();
					return view('employee.FDA.pfreco', ['AppData'=>$data,/*'PreAss'=>$data1, */'APPID' => $appid, 'Teams4theApplication' => $data2, 'canView' => $canView, 'canjudge' => $hasJudge, 'currentRequest' => $approveForCurrentRequest, 'request' => $reqType]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.FDA.pfreco');
				}
			}
			if ($request->isMethod('post')) 
			{	
				$Cur_useData = AjaxController::getCurrentUserAllData();
				try {
					// $ChckPassword = ($reqType == 'machines' ? true : AjaxController::checkPassword($request->pass));
					$ChckPassword = true;
					if ($ChckPassword == true) 
					{
						$pharCOCUP = $xrayCOCUP = null;
						$status = ($request->isOk == '1') ? 'A' : 'RA';
						if($reqType == 'machines'){
							$data = array(
					 			'isRecoFDA' => $request->isOk,
					 			'isRecoDecision' => $request->recommendation,
					 			'RecobyFDA' => $Cur_useData['cur_user'],
					 			'RecodateFDA' => $Cur_useData['date'],
					 			'RecotimeFDA' =>  $Cur_useData['time'],
					 			'RecoippaddrFDA' => $Cur_useData['ip'],
					 			'RecoRemarkFDA' => ($request->desc ?? null),
					 			'FDAstatus' => $status,
 					 		);
						} else {
							$data = array(
					 			'isApproveFDAPharma' => $request->isOk,
					 			'approvedByFDAPharma' => $Cur_useData['cur_user'],
					 			'approvedDateFDAPharma' => $Cur_useData['date'],
					 			'approvedTimeFDAPharma' =>  $Cur_useData['time'],
					 			'approvedIpAddFDAPharma' => $Cur_useData['ip'],
					 			'approvedRemarkFDAPharma' => ($request->desc ?? null),
					 			'FDAstatus' => $status,
 					 		);
						}
					 	
						$test = DB::table('appform')->where('appid', '=', $request->id)->update($data);
						// $selected = AjaxController::getUidFrom($request->id);
						// AjaxController::notifyClient($request->id,$selected,($request->isOk == 1 ? 33 : 32));
						if ($test) {
							return 'DONE';
						} else {
							return 'ERROR';
						}
					}
					else
					{
						return 'WRONGPASSWORD';
					}
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
				
			}
		}


		public function ApprovalOneProcessFlowFDA(Request $request, $appid, $reqType = 'machines')
		{
			$reqType = strtolower($reqType);
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getRecommendationData($appid);
					// $data1 = AjaxController::getPreAssessment($data->uid);
					$data2 = AjaxController::getAssignedMembersInTeam4Recommendation($appid);
					$canView = AjaxController::canViewFDAOOP($appid);
					if($reqType == 'machines'){
						$typeOfRequest = 'cdrrhr';
						if(isset($canView[0])){
							$canView[0] = false;
						}

						DB::table('appform')->where('appid', '=', $appid)->update(['FDAStatMach'=>'For Monitoring']);
					} else{
						$typeOfRequest = 'cdrr';
						if(isset($canView[1])){
							$canView[1] = false;
						}

						DB::table('appform')->where('appid', '=', $appid)->update(['FDAStatPhar'=>'For Monitoring']);
					}
					$approveForCurrentRequest = ($reqType == 'machines' ? $data->isApproveFDA : $data->isApproveFDAPharma);
					$hasJudge = DB::table('fdacert')->where([['appid',$appid],['department',$typeOfRequest]])->exists();
					return view('employee.FDA.pfapprovaloneFDA', ['AppData'=>$data,/*'PreAss'=>$data1, */'APPID' => $appid, 'Teams4theApplication' => $data2, 'canView' => $canView, 'canjudge' => $hasJudge, 'currentRequest' => $approveForCurrentRequest, 'request' => $reqType]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.FDA.pfapprovaloneFDA');
				}
			}
			if ($request->isMethod('post')) 
			{	
				$Cur_useData = AjaxController::getCurrentUserAllData();

				if($request->has('cert')){
					$toQueryValidate = ($reqType == 'machines' ? 'cdrrhr' : 'cdrr');
					if(DB::table('fdacert')->where([['appid',$appid],['department',$toQueryValidate]])->doesntExist()){
						if($reqType == 'pharma'){
							if($request->cert == 'rl'){
								$fieldsToInsert = ['certtype' => 'RL','warehouse' => $request->waAR, 'allied' => $request->rlAR, 'apptype' => $request->rlta, 'estype' => ($reqType == 'machines' ? 'X-Ray' : 'Hospital Pharmacy'), 'otherestype' => $request->rlod, 'department' => $toQueryValidate, 'appid' => $appid, 'issueby' => $Cur_useData['cur_user']];
							} else if($request->cert == 'coc'){
								$fieldsToInsert = ['certtype' => 'COC', 'cocNo' => Date('Y-m-d',strtotime('now')).'-'.$appid, 'warehouse' => $request->cocWA, 'allied' => $request->cocAR, 'estype' => ($reqType == 'machines' ? 'X-Ray' : 'Hospital Pharmacy'), 'otherdet' => $request->cocOD, 'otherestype' => $request->cocod, 'department' => $toQueryValidate, 'appid' => $appid, 'issueby' => $Cur_useData['cur_user']];
							}
						} else {
							$fullName = null;
							$faciHead = DB::table('hfsrbannexa')->where([['appid',$appid],['isMainRadio',1]])->first();
							if(isset($faciHead)){
								$fullName = ucwords($faciHead->prefix . ' ' . $faciHead->firstname) . ' ' . $faciHead->middlename . ' ' .  ucwords($faciHead->surname . ' ' . $faciHead->suffix);
							}
							if($request->cert == 'rl'){
								$fieldsToInsert = ['certtype' => 'RL','rlNo' => $request->pRLNo, 'dtn' => $request->pdtnNo, 'estype' => ($reqType == 'machines' ? 'X-Ray' : 'Hospital Pharmacy'), 'headFaci' => $request->rlMhof, 'chiefRadioTechno' => $fullName, 'radProOff' => $request->rlMrpo, 'department' => $toQueryValidate, 'appid' => $appid, 'issueby' => $Cur_useData['cur_user']];
							} else if($request->cert == 'coc'){
								$fieldsToInsert = ['certtype' => 'COC','authorizationStatus' => $request->cocMas, 'cocNo' => Date('Y-m-d',strtotime('now')).'-'.$appid, 'estype' => ($reqType == 'machines' ? 'X-Ray' : 'Hospital Pharmacy'), 'headFaci' => ($request->cocMhof ?? null), 'chiefRadioTechno' => $fullName, 'radProOff' => ($request->cocMrpo ?? null), 'department' => $toQueryValidate, 'appid' => $appid, 'issueby' => $Cur_useData['cur_user']];
							}
						}
						if(DB::table('fdacert')->insert($fieldsToInsert)){
							$selected = AjaxController::getUidFrom($appid);
							AjaxController::notifyClient($appid,$selected,($reqType == 'pharma' ? 58 : 57));
							return 'true';
						}
					}
					return 'false';
				} else {
					try {
						// $ChckPassword = AjaxController::checkPassword($request->pass);
						$ChckPassword = true;
						if ($ChckPassword == true) 
						{
							
							$pharCOCUP = $xrayCOCUP = null;
							$status = ($request->isOk == '1') ? 'A' : 'RA';
							if($request->hasFile('pharCOCUP')){
								$pharCOCUP = FunctionsClientController::uploadFile($request->pharCOCUP)['fileNameToStore'];
							}
							if($request->hasFile('xrayCOCUP')){
								$xrayCOCUP = FunctionsClientController::uploadFile($request->xrayCOCUP)['fileNameToStore'];
							}
							if($reqType == 'machines'){
								$data = array(
						 			'isApproveFDA' => $request->isOk,
						 			'approvefdaverd' => $request->verd,
						 			'approvedByFDA' => $Cur_useData['cur_user'],
						 			'approvedDateFDA' => $Cur_useData['date'],
						 			'approvedTimeFDA' =>  $Cur_useData['time'],
						 			'approvedIpAddFDA' => $Cur_useData['ip'],
						 			'approvedRemarkFDA' => $request->desc,
						 			'FDAstatus' => $status,
	 					 		);
							} else {
								$data = array(
						 			'isApproveFDAPharma' => $request->isOk,
						 			'approvefdaverdpharma' => $request->verd,
						 			'approvedByFDAPharma' => $Cur_useData['cur_user'],
						 			'approvedDateFDAPharma' => $Cur_useData['date'],
						 			'approvedTimeFDAPharma' =>  $Cur_useData['time'],
						 			'approvedIpAddFDAPharma' => $Cur_useData['ip'],
						 			'approvedRemarkFDAPharma' => $request->desc,
						 			'FDAstatus' => $status,
	 					 		);
							}
						 	// return $data;
						 	if(!FunctionsClientController::existOnDB('fdacert',[['appid',$appid],['department',(AjaxController::isRequestForFDA($reqType) == 'machines' ? 'cdrrhr' : 'cdrr')]]) && !isset($request->verd)){
								return 'Please add RL/COC first!';
							}
							$test = DB::table('appform')->where('appid', '=', $appid)->update($data);
							$selected = AjaxController::getUidFrom($appid);
							AjaxController::notifyClient($appid,$selected,($request->isOk == 1 ? 33 : 32));
							if ($test) {
								return 'DONE';
							} else {
								return 'ERROR';
							}
						}
						else
						{
							return 'WRONGPASSWORD';
						}
					} 
					catch (Exception $e) 
					{
						return $e;
						AjaxController::SystemLogs($e);
						return 'ERROR';
					}
				}
			}
		}

		public function MonitoringList(Request $request,$type = 'machines'){
			try {
				$selected = AjaxController::isRequestForFDA($type);
				if($request->isMethod('get')){
					$arrRet = [
						'entries' => DB::table('fdamonitoring')->leftJoin('trans_status','trans_status.trns_id','fdamonitoring.status')->leftJoin('appform','appform.appid','fdamonitoring.appid')->leftJoin('fdacert','fdacert.appid','appform.appid')->where([['type',$selected]])->select('appform.appid','addedBy','addedTimeAndDate','trns_desc','appform.facilityname','fdamon','certtype')->get(),
						'region' => DB::table('region')->get(),
						'selected' => $selected
					];
					return view('employee.FDA.monitoringEntry',$arrRet);
				} else {
					if($request->has('action')){
						switch ($request->action) {
							case 'getList':
								$list = ($selected == 'machines' ? 'isApproveFDA': 'isApproveFDAPharma');
								return DB::table('appform')->select('appid','facilityname')->distinct()->where([['isApprove',1],[$list,1],['cmid',$request->cmid]])->get();
								break;
							case 'submitMonitor':
								if(DB::table('fdamonitoring')->insert(['appid' => $request->faci, 'type' => $selected, 'status' => 'FM', 'addedBy' => AjaxController::getCurrentUserAllData()['cur_user']])){
									return back()->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Added Successfully.']);
								}
								break;
							
							default:
								# code...
								break;
						}
					}
				}
			}
			catch (Exception $e) {
				return $e;
			}
		}


		public function MonitoringProcess(Request $request, $type = 'machines', $fdamonid){
			$selected = AjaxController::isRequestForFDA($type);
			$monFDA = DB::table('fdamonitoring')->where('fdamon',$fdamonid)->first();
			$filtered = [];
			if(isset($monFDA)){
				if($request->isMethod('get')){
					$forFilter = DB::table('fdamonitoringfiles')->leftJoin('fdamonitoring','fdamonitoring.fdamon','fdamonitoringfiles.fdaMonId')->where([['fdaMonId',$fdamonid],['fdamonitoring.type',$selected]])->orderBy('addedTimeDate','DESC')->get();
					foreach ($forFilter as $key => $value) {
						if($value->isReply == null){
							$filtered['fromPO'] = $value;
						} else {
							$filtered['fromClient'][] = $value;
						}
					}
					$arrRet = [
						'choosen' => $selected,
						'AppData' => AjaxController::getAllDataEvaluateOne($monFDA->appid),
						'eval' => ($filtered['fromPO'] ?? null),
						'fromClient' => ($filtered['fromClient'] ?? null),
						'monitoringDetails' => $monFDA
					];
					return view('employee.FDA.monitoringEntryUpload',$arrRet);
				}
				if($request->isMethod('post')){
					if($request->hasFile('fileUp')){
						$uploadName = FunctionsClientController::uploadFile($request->fileUp)['fileNameToStore'];
						$toInsertFieldsAndValue = array([
							'fileName' => $uploadName,
							'otherfilename' => (FunctionsClientController::uploadFile($request->otherUpload)['fileNameToStore'] ?? null),
							'remark' => $request->remarks,
							'addedBy' => session()->get('employee_login')->uid,
							'fdaMonId' => $fdamonid
						]);
						$isRedirect = DB::table('fdamonitoringfiles')->insert($toInsertFieldsAndValue);

						if($isRedirect && DB::table('fdamonitoring')->where('fdamon',$fdamonid)->update(['decision' => $request->recommendation])){
							$uid = AjaxController::getUidFrom($monFDA->appid);
							AjaxController::notifyClient($fdamonid,$uid,60,'fdamonitoring');
							return back()->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Sent and notified client.']);
						}
						return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Network error occured. Please try again.']);

					}
					if($request->has('changeTo')){
						if(DB::table('fdamonitoring')->where('fdamon',$fdamonid)->update(['decision' => 'C', 'status' => 'A'])){
							$uid = AjaxController::getUidFrom($monFDA->appid);
							AjaxController::notifyClient($fdamonid,$uid,60,'fdamonitoring');
							return back()->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Status has been update to Compliant']);
						}
					}
					if($request->has('remarksFC')){
						if(DB::table('fdamonitoringfiles')->where([['fdaMonId',$fdamonid],['isReply',null]])->update(['remark' => $request->remarksFC])){
							DB::table('fdamonitoring')->where('fdamon',$fdamonid)->update(['hasReplyFlag' => null, 'status' => 'A']);
							$uid = AjaxController::getUidFrom($monFDA->appid);
							AjaxController::notifyClient($fdamonid,$uid,63,'fdamonitoring');
							return back()->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Resent to Client']);
						}
					}
				}
			}
			return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Monitoring Records not found.']);
		}

		////// APPROVAL ONE
		////// FAILED
		public static function FailedProcessFlow(Request $request)
		{
			try 
			{
				$data = AjaxController::getFailedApplications();
				// return dd($data);
				return view('employee.processflow.pffailed', ['BigData'=>$data]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pffailed');
			}
		}
		////// FAILED
		////// FAILED ONE
		public static function FailedOneProcessFlow(Request $request, $appid)
		{
			try 
			{
					$data = AjaxController::getRecommendationData($appid);
					// $data1 = AjaxController::getPreAssessment($data->uid);
					$data2 = AjaxController::getAssignedMembersInTeam4Recommendation($appid);
					// return dd($data);
					return view('employee.processflow.pffailedone', ['AppData'=> $data,/*'PreAss'=> $data1,*/ 'APPID' => $appid, 'Teams4theApplication' => $data2]);
			} 
			catch (Exception $e) 
			{
				AjaxController::SystemLogs($e);
				session()->flash('system_error','ERROR');
				return view('employee.processflow.pffailedone');
			}
		}
		////// FAILED ONE
		///////////////////////////////////////////////// PROCESS FLOW
		///////////////////////////////////////////////// OTHERS
		////// MONITORING
		public function MonitoringOthers(Request $request)
		{
			if(in_array(true, AjaxController::isSessionExist(['employee_login']))){
				if ($request->isMethod('get')) 
				{
					try 
					{
						$allData = AjaxController::getAllMonitoringForm();
						// $test = AjaxController::getFacTypeByFacid("BB");
						// dd(AjaxController::getFacTypeByFacid("BB")[0]->facname);
						$allRec = AjaxController::getAllSurveillanceRecommendation(); 
						$typNameSql = "SELECT * FROM facilitytyp WHERE servtype_id = 1";
						$typName = DB::select($typNameSql);
						$region = DB::table('region')->get();
						// dd($faciName);

						//CDO
						foreach($allData as $key => $value) {
							if($value->novid != "") {
								$currentNov = AjaxController::getAllNovIssuedByMonid($value->monid);

								if($currentNov != null) {
									$novdate = $currentNov[0]->novdate;
									$novdire = $currentNov[0]->novdire;

									if(date_create(date('Y-m-d')) > date_create(date('Y-m-d', strtotime($novdate.'+ 3 days'))) && $novdire == 1) {	
										DB::table('mon_form')
											->where('monid', '=', $value->monid)
											->update(['isCDO'=>1]);
									} else if($currentNov[0]->novdire == 2) {
										DB::table('mon_form')
											->where('monid', '=', $value->monid)
											->update(['isCDO'=>1]);
									} else {
										DB::table('mon_form')
											->where('monid', '=', $value->monid)
											->update(['isCDO'=>null]);
									}
								}
							}
						}

						// dd($allData);

						return view('employee.others.Monitoring', ['TypName'=>$typName, 'AllData'=>$allData, 'AllRec'=>$allRec, 'region' => $region]);
					} 
					catch (Exception $e) 
					{
						AjaxController::SystemLogs($e);
						session()->flash('system_error', 'ERROR');
						return view('employee.others.Monitoring')	;
					}
				}
			}
		}

		public function MonitoringTeamsOthers(Request $request)
		{
			if(in_array(true, AjaxController::isSessionExist(['employee_login']))){

				if ($request->isMethod('get')) 
				{
					try 
					{
						$allDataSql = "SELECT * FROM mon_form join appform on appform.appid = mon_form.appid WHERE team IS NULL";
						$allData = DB::select($allDataSql);
						$allRec = AjaxController::getAllSurveillanceRecommendation();
						$allTeam = AjaxController::getAllTeams();
						$allNewTeam = AjaxController::getAllMonTeam();
						$empWOTeam = AjaxController::getEmployeeWithoutTeamOthers();
						$empWTeam = AjaxController::getEmployeeWithTeamOthers();
						$allMonTeam = AjaxController::getAllMonTeam();
						$empData = AjaxController::getCurrentUserAllData();
						// dd($allData);
						//CDO

						// foreach($allData as $key => $value) {
						// 	if($value->novid != "") {
						// 		$currentNov = AjaxController::getAllNovIssuedByMonid($value->monid);

						// 		if($currentNov != null) {
						// 			$novdate = $currentNov[0]->novdate;
						// 			$novdire = $currentNov[0]->novdire;

						// 			if(date_create(date('Y-m-d')) > date_create(date('Y-m-d', strtotime($novdate.'+ 3 days'))) && $novdire == 1) {	
						// 				DB::table('mon_form')
						// 					->where('monid', '=', $value->monid)
						// 					->update(['isCDO'=>1]);
						// 			} else if($currentNov[0]->novdire == 2) {
						// 				DB::table('mon_form')
						// 					->where('monid', '=', $value->monid)
						// 					->update(['isCDO'=>1]);
						// 			} else {
						// 				DB::table('mon_form')
						// 					->where('monid', '=', $value->monid)
						// 					->update(['isCDO'=>null]);
						// 			}
						// 		}
						// 	}
						// }

						// dd($empWTeam);
						return view('employee.others.MonitoringTeams', ['AllData'=>$allData, 'AllRec'=>$allRec, 'AllTeam'=>$allTeam, 'WT'=>$empWTeam, 'WOT'=>$empWOTeam, 'AllMonTeam'=>$allMonTeam, 'AllNewTeam'=>$allNewTeam, 'emp' => $empData]);
					} 
					catch (Exception $e) 
					{
						dd($e);
						AjaxController::SystemLogs($e);
						session()->flash('system_error', 'ERROR');
						return view('employee.others.Monitoring')	;
					}
				}
			}
			return redirect('employee');
		}

		public function MonitoringInspectionOthers(Request $request)
		{
			if(in_array(true, AjaxController::isSessionExist(['employee_login']))){
				if ($request->isMethod('get')) 
				{
					try  
					{
						$allData = AjaxController::listForMonitoringAssessment($request);
						//CDO
						foreach($allData as $key => $value) {
							if($value->novid != "") {
								$currentNov = AjaxController::getAllNovIssuedByMonid($value->monid);

								if($currentNov != null) {
									$novdate = $currentNov[0]->novdate;
									$novdire = $currentNov[0]->novdire;

									// if(date_diff(date_create(date('Y-m-d')), date_create(date('Y-m-d', strtotime($novdate.'+ 3 days'))))->d <= 0 && $novdire == 1) {
									// 	DB::table('mon_form')
									// 		->where('monid', '=', $value->monid)
									// 		->update(['isCDO'=>1]);
									// }
									if(date_create(date('Y-m-d')) > date_create(date('Y-m-d', strtotime($novdate.'+ 3 days'))) && $novdire == 1) {	
										DB::table('mon_form')
											->where('monid', '=', $value->monid)
											->update(['isCDO'=>1]);
									} else if($currentNov[0]->novdire == 2) {
										DB::table('mon_form')
											->where('monid', '=', $value->monid)
											->update(['isCDO'=>1]);
									} else {
										DB::table('mon_form')
											->where('monid', '=', $value->monid)
											->update(['isCDO'=>null]);
									}
								}
							}
						}
						// dd($allData);
						return ($this->agent ? response()->json(array($allData)) : view('employee.others.MonitoringInspection', ['AllData'=>$allData]));
					} 
					catch (Exception $e) 
					{
						dd($e);
						AjaxController::SystemLogs($e);
						session()->flash('system_error', 'ERROR');
						return view('employee.others.Monitoring');
					}
				}
			} else {
				return redirect('employee/')->with('errRet', ['errAlt'=>'error', 'errMsg'=>'Please Log in first.']);
			}
		}

		public function MonitoringInspectionOthersMobile(Request $request)
		{
			if($request->isMethod('post')){
				try {
					AjaxController::createMobileSessionIfMobile($request);
					return AjaxController::sendTo(false,$this->agent,$request->all(),'employee.others.MonitoringInspection',['data' => AjaxController::listForMonitoringAssessment($request)]);
				} catch (Exception $e) {
					return response()->json($e);
				}
			}
			return abort(404);
		}

		public function MonitoringTechnicalOthers(Request $request,$optid = null)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$allDataSql = "SELECT * FROM mon_form /*WHERE assessmentStatus <> 0*/";
					$allData = DB::select($allDataSql);
					$allNovDir = AjaxController::getAllNovDirections();
					$violationKey = 0;
					$allViolation = array();

					// $flag = DB::select("SELECT EXISTS (SELECT * FROM assessmentcombinedduplicate WHERE monid = '1') AS value");
					// dd($allData);
					foreach($allData as $k => $v) {
						$flag = DB::select("SELECT EXISTS (SELECT * FROM assessmentcombinedduplicate WHERE monid = ".$v->monid.") AS value")[0];
						// dd($flag->value == true);
						if($flag->value) {
							$allViolation[$v->monid] = DB::select("SELECT *FROM `assessmentcombinedduplicate` WHERE monid = ".$v->monid." AND evaluation = 0");
							DB::table('mon_form')
											->where('monid', '=', $v->monid)
											->update(['hasViolation'=>1]);
						} else {
							// DB::table('mon_form')
							// 				->where('monid', '=', $v->monid)
							// 				->update(['isApproved'=>1]);
						}
					}

					// dd($allViolation);

					// Getting the violations
					// foreach($allData as $key => $value) {
					// 	if($value->DOHMonitoring != "") {
					// 		$arr = json_decode($value->DOHMonitoring, true);
					// 		$mergedarr = call_user_func_array("array_merge", $arr);

					// 		$violationCount = 0;							
					// 		foreach($mergedarr as $k => $v) {
					// 			// violation count
					// 			$start = explode('/', $k)[0];
					// 			$length = strlen('comp');

					// 			if(substr($k, strlen($start)+1, $length) === 'comp') {
					// 				if($v != 'true') {
					// 					$violationCount++;
					// 				}
					// 			}
					// 		}

					// 		if($violationCount > 0) {
					// 			// violation table update
					// 			DB::table('mon_form')
					// 							->where('monid', '=', $value->monid)
					// 							->update(['hasViolation'=>1]);

					// 			DB::table('mon_form')
					// 							->where('monid', '=', $value->monid)
					// 							->update(['violation'=>AjaxController::getAllViolationByMonId($value->monid)]);
					// 		} /*else if($currentNov[0]->novdire == 2) {
					// 			DB::table('mon_form')
					// 				->where('monid', '=', $value->monid)
					// 				->update(['isCDO'=>1]);
					// 		}*/ else {
					// 			DB::table('mon_form')
					// 				->where('monid', '=', $value->monid)
					// 				->update(['isApproved'=>1]);
					// 		}
					// 	}
 				// 	}

 					$allDataSql = "SELECT * FROM mon_form WHERE hasViolation IS NOT NULL";
 					$allData = DB::select($allDataSql);


 					//CDO
					foreach($allData as $key => $value) {
						if($value->novid != "") {
							$currentNov = AjaxController::getAllNovIssuedByMonid($value->monid);

							if($currentNov != null) {
								$novdate = $currentNov[0]->novdate;
								$novdire = $currentNov[0]->novdire;

								if(date_create(date('Y-m-d')) > date_create(date('Y-m-d', strtotime($novdate.'+ 3 days'))) && $novdire == 1) {	
									DB::table('mon_form')
										->where('monid', '=', $value->monid)
										->update(['isCDO'=>1]);
								} else if($currentNov[0]->novdire == 2) {
									DB::table('mon_form')
										->where('monid', '=', $value->monid)
										->update(['isCDO'=>1]);
								} else {
									DB::table('mon_form')
										->where('monid', '=', $value->monid)
										->update(['isCDO'=>null]);
								}
							}
						}
					}

 					// dd($allData);
					return view('employee.others.MonitoringTechnical', ['AllData'=>$allData, 'AllNov'=>$allNovDir, 'AllViolation'=>$allViolation, 'optid' => $optid]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.Monitoring')	;
				}
			}	
			if($request->isMethod('post')){
				if($request->has('setToRevise') && $request->has('monid')){
					if(DB::table('mon_form')->where('monid',$request->monid)->update(['hasLOE' => null, 'monitorRemark' => $request->remark, 'forResubmit' => 1])){
						$monDet = DB::table('mon_form')->where('monid',$request->monid)->first();
						if(DB::table('technicalfindingshist')->insert(['LOE' => $monDet->LOE, 'attached_filesUser'=> $monDet->attached_filesUser, 'id' => $monDet->monid, 'fromWhere' => 'mon'])){
							$uid = AjaxController::getUidFrom(DB::table('mon_form')->where('monid',$request->monid)->select('appid')->first()->appid);
							AjaxController::notifyClient($request->monid,$uid,64);
							return back()->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Operation Successful.']);
						}
					}
				}
			}
		}

		public function MonitoringRecommendationOthers(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$allDataSql = "SELECT * FROM mon_form join appform on appform.appid = mon_form.appid WHERE hasLOE IS NOT NULL";
					$allData = DB::select($allDataSql);
					$allRec = AjaxController::getAllSurveillanceRecommendation();
					$allVer = AjaxController::getAllVerdict();
					// dd(AjaxController::getAttachMon(54)->attached_files);
					// $test = AjaxController::getViolationDescById('AST001');
					// dd($allRec[0]->rec_extra);
					// dd($test);

 					$allData = DB::select($allDataSql);

 					//CDO
					foreach($allData as $key => $value) {
						if($value->novid != "") {
							$currentNov = AjaxController::getAllNovIssuedByMonid($value->monid);

							if($currentNov != null) {
								$novdate = $currentNov[0]->novdate;
								$novdire = $currentNov[0]->novdire;

								if(date_create(date('Y-m-d')) > date_create(date('Y-m-d', strtotime($novdate.'+ 3 days'))) && $novdire == 1) {	
									DB::table('mon_form')
										->where('monid', '=', $value->monid)
										->update(['isCDO'=>1]);
								} else if($currentNov[0]->novdire == 2) {
									DB::table('mon_form')
										->where('monid', '=', $value->monid)
										->update(['isCDO'=>1]);
								} else {
									DB::table('mon_form')
										->where('monid', '=', $value->monid)
										->update(['isCDO'=>null]);
								}
							}
						}
					}

					// dd($allData);

					return view('employee.others.MonitoringRecommendation', ['AllData'=>$allData, 'AllRec'=>$allRec, 'AllVer'=>$allVer]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.Monitoring')	;
				}
			}	
		}

		public function MonitoringEvaluationOthers(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$allDataSql = "SELECT * FROM mon_form WHERE recommendation IS NOT NULL";
					$allData = DB::select($allDataSql);

					//CDO
					foreach($allData as $key => $value) {
						if($value->novid != "") {
							$currentNov = AjaxController::getAllNovIssuedByMonid($value->monid);

							if($currentNov != null) {
								$novdate = $currentNov[0]->novdate;
								$novdire = $currentNov[0]->novdire;

								if(date_diff(date_create(date('Y-m-d')), date_create(date('Y-m-d', strtotime($novdate.'+ 3 days'))))->d <= 0 && $novdire == 1) {
									DB::table('mon_form')
										->where('monid', '=', $value->monid)
										->update(['isCDO'=>1]);
								} 
							}
						}
					}
					
					return view('employee.others.MonitoringEvaluation', ['AllData'=>$allData]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.Monitoring')	;
				}
			}	
		}

		public function MonitoringUpdateStatusOthers(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$allDataSql = "SELECT * FROM mon_form join appform on appform.appid = mon_form.appid WHERE hasLOE IS NOT NULL";
					$allData = DB::select($allDataSql);
					$allRec = AjaxController::getAllSurveillanceRecommendation();
					$allVer = AjaxController::getAllVerdict();

 					$allData = DB::select($allDataSql);

 					//CDO
					foreach($allData as $key => $value) {
						if($value->novid != "") {
							$currentNov = AjaxController::getAllNovIssuedByMonid($value->monid);

							if($currentNov != null) {
								$novdate = $currentNov[0]->novdate;
								$novdire = $currentNov[0]->novdire;

								if(date_create(date('Y-m-d')) > date_create(date('Y-m-d', strtotime($novdate.'+ 3 days'))) && $novdire == 1) {	
									DB::table('mon_form')
										->where('monid', '=', $value->monid)
										->update(['isCDO'=>1]);
								} else if($currentNov[0]->novdire == 2) {
									DB::table('mon_form')
										->where('monid', '=', $value->monid)
										->update(['isCDO'=>1]);
								} else {
									DB::table('mon_form')
										->where('monid', '=', $value->monid)
										->update(['isCDO'=>null]);
								}
							}
						}
					}

					return view('employee.others.MonitoringUpdateStatus', ['AllData'=>$allData, 'AllRec'=>$allRec, 'AllVer'=>$allVer]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.Monitoring')	;
				}
			}	
		}

		////// NOV
		public function __novm(Request $request, $novid) {
			if($request->isMethod('get')) {
				try {
					// $AllData = AjaxController::getAllMonitoringForm();
					// $Nov = AjaxController::getAllNovIssuedByMonid($monid); // collection of array of objects
					$NovAll = AjaxController::getAllNovDirections();
					$Nov = AjaxController::getNovIssuedByNov($novid);
					$arrNov = (isset($Nov) ? explode(',', $Nov->novdire) : null);
					$AllTeam = AjaxController::getAllMonTeamMembers($Nov->novteam); // collection of array
					return view('employee.others.NoticeOfViolation', ['Nov'=>$Nov, 'AllTeam'=>$AllTeam, 'NovAll' => $NovAll, 'arrNov' => $arrNov]);

					return response(view('employee.others.NoticeOfViolation', ['Nov'=>$Nov, 'AllTeam'=>$AllTeam, 'NovAll' => $NovAll, 'arrNov' => $arrNov]), 200, [
						'Content-Type'=> 'text/html',
						'Content-Disposition' => 'attachment; filename="Notice Of Violation.html"',
					]);
				} catch(Exception $e) {

				}
			}
		}

		public function __novs(Request $request, $survid) {
			if($request->isMethod('get')) {
				try {
					// $AllData = AjaxController::getAllMonitoringForm();
					$Nov = AjaxController::getAllNovIssuedBySurvid($survid); // collection of array of objects
					$AllTeam = AjaxController::getMembersByTeamIdNotJson($Nov[0]->novteam); // collection of array
					return view('employee.others.NoticeOfViolation', ['Nov'=>$Nov, 'AllTeam'=>$AllTeam]);
				} catch(Exception $e) {

				}
			}
		}

		public function __nov_submit(Request $request) {
			if($request->isMethod('get')) {
				try {
					$AllData = AjaxController::getAllMonitoringForm();
					return view('employee.others.NoticeOfViolation', ['AllData'=>$AllData]);
				} catch(Exception $e) {

				}
			}
		}

		////// RAOIN
		public function __raoins(Request $request, $survid) {
			if($request->isMethod('get')) {
				try {
					// $AllData = AjaxController::getAllMonitoringForm();
					$Nov = AjaxController::getAllNovIssuedBySurvid($survid); // collection of array of objects
					$AllTeam = AjaxController::getMembersByTeamIdNotJson($Nov[0]->novteam); // collection of array
					$AllData = AjaxController::getSurveillanceFormByMonid($survid);

					return view('employee.others.RAOIN', ['Nov'=>$Nov, 'AllTeam'=>$AllTeam, 'AllData'=>$AllData]);
				} catch(Exception $e) {

				}
			}
		}
		public function __raoin(Request $request, $monid) {
			if($request->isMethod('get')) {
				try {
					// $AllData = AjaxController::getAllMonitoringForm();
					$Nov = AjaxController::getAllNovIssuedByMonid($monid); // collection of array of objects
					$AllTeam = AjaxController::getAllMonTeamMembers($Nov[0]->novteam); // collection of array
					$AllData = AjaxController::getMonitoringFormByMonid($monid); // collection of array

					// return view('employee.others.RAOIN', ['Nov'=>$Nov, 'AllTeam'=>$AllTeam, 'AllData'=>$AllData]);
					return response(view('employee.others.RAOIN', ['AllTeam'=>$AllTeam, 'AllData'=>$AllData]), 200, [
						'Content-Type'=> 'text/html',
						'Content-Disposition' => 'attachment; filename="Recommendatory Action on Issued Notice of Violation.html"',
					]);
					// return view('employee.others.RAOIN', ['Nov'=>$Nov, 'AllTeam'=>$AllTeam, 'AllData'=>$AllData]);
				} catch(Exception $e) {

				}
			}
		}

		////// SURVEILLANCE
		public function SurveillanceOthers(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$allData = AjaxController::getAllSurveillanceForm();
					$test = AjaxController::getFacTypeByFacid("BB");
					$arrayToCheck = array();
					// dd($allData);
					// dd(AjaxController::getFacTypeByFacid("BB")[0]->facname);
					$allRec = AjaxController::getAllSurveillanceRecommendation();
					$typNameSql = "SELECT * FROM facilitytyp";
					$typName = DB::select($typNameSql);
					$compFromSurvform = DB::table('surv_form')->select('compid')->whereNotNull('compid')->get();
					if(count($compFromSurvform) > 0){
						foreach($compFromSurvform as $com){
							if(!in_array($com->compid, $arrayToCheck)){
								array_push($arrayToCheck, $com->compid);
							}
						}
						$arrayToCheck = implode(',', $arrayToCheck);
					}
					// dd($allData);
					$fromComplaints = DB::table('complaints_form')->where('type','Complaints')->whereNotIn('ref_no',(is_array($arrayToCheck) ? $arrayToCheck : [$arrayToCheck]) )->get();
					return view('employee.others.Surveillance', ['TypName'=>$typName, 'AllData'=>$allData, 'AllRec'=>$allRec, 'comp' => $fromComplaints]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.Surveillance')	;
				}
			}	
			else if($request->isMethod('post')) {
				if($request->action == 'getUnregisteredData'){
					return json_encode(DB::table('surv_form')->where('survid',$request->survid)->first());
				}
				if($request->has('forEdit')){
					if(DB::table('surv_form')->where('survid',$request->forEdit)->update(
					['name_of_faci'=>$request->u_nameoffaci, 'address_of_faci'=>(isset($request->address) ? $request->address : AjaxController::getAddressByLocation($request->u_reg,$request->u_prov,$request->u_cm,$request->u_brgy)), 'type_of_faci'=>$request->u_typeoffaci, 'faciEmail' => (isset($email) ? $email->email : null), 'compid' => $request->compid, 'fromWhere' => $request->fromWhere, 'compAddress' => (strtolower($request->fromWhere) == 'unregistered facility' ? json_encode(['reg' => $request->u_reg,'prov' => $request->u_prov, 'cm' => $request->u_cm, 'brgy' => $request->u_brgy]) : '')]
					)){
						return back()->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Edited Successfully.']);
					}
					return back()->with('errRet', ['errAlt'=>'danger', 'errMsg'=>'Error Occured. Please try again later.']);
				}
			}
		}

		public function SurveillanceActivity(Request $request)
		{
			$images = null;
			if ($request->isMethod('get')) 
			{
				try 
				{
					$allData = AjaxController::getAllSurveillanceForm();
					$test = AjaxController::getFacTypeByFacid("BB");
					$allRec = AjaxController::getAllSurveillanceRecommendation();
					$typNameSql = "SELECT * FROM facilitytyp";
					$typName = DB::select($typNameSql);
					return view('employee.others.SurveillanceActivity', ['TypName'=>$typName, 'AllData'=>$allData, 'AllRec'=>$allRec]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.SurveillanceActivity')	;
				}
			} else if($request->isMethod('post')){
				$cUser = AjaxController::getCurrentUserAllData();
				if($request->has('images')){
					$images = array();
					foreach ($request->file('images') as $key) {
						$imageRec = FunctionsClientController::uploadFile($key);
						array_push($images,$imageRec['fileNameToStore']);
					}
				}
				$toUpdate = ['survAct' => $request->action, 'comments' => $request->comments, 'LOAttachments' => (is_array($images) ? implode(',',$images): null ), 'issuedBy' => $cUser['cur_user'], 'issuedDate' => $cUser['date'], 'issuedTime' => $cUser['time'], 'hfsrbno' => $request->novNo, 'faciEmail' => $request->emailFaci, 'dpo' => $request->dpo];
				if(trim($request->violation) != ''){
					$toUpdate['violation'] = $request->violation;
					$toUpdate['hasViolation'] = 1;
				}

				$test = DB::table('surv_form')->where('survid',$request->survid)->update($toUpdate);

				if($test){
					$toNotify = DB::table('surv_form')->where('survid',$request->survid)->first();
					if(isset($toNotify->appid)){
						$uid = AjaxController::getUidFrom($toNotify->appid);
						if(isset($uid)){
							AjaxController::notifyClient($toNotify->survid,$uid,44,'surv');
						}
					} else {
						$dataToBeSend = array('name' => $toNotify->name_of_faci, 'action' => 'Surveillance', 'url' => asset('client1/action/sendActionTaken/surv/'.$request->survid));
						Mail::send('employee.others.mailForActivityLog', $dataToBeSend, function($message) use ($toNotify,$request) {
			               $message->to($request->emailFaci);
			               $message->subject('DOHOLRS Violation on Facility');
			               $message->from('doholrs@gmail.com','DOH Support');
			            });
					}
				}

				return redirect('employee/dashboard/others/surveillance/survact');
			}
		}

		public function clientActionTaken(Request $request,$optid = null)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$allData = DB::table('surv_form')->leftjoin('facilitytyp','facilitytyp.facid','surv_form.type_of_faci')->where('hasLOE',1)->get();
					return view('employee.others.clientActionTaken', ['AllData'=>$allData, 'optid' => $optid]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.clientActionTaken');
				}
			}	
			if($request->isMethod('post')){
				try {
					if($request->has('setToRevise') && $request->has('survid')){
						if(DB::table('surv_form')->where('survid',$request->survid)->update(['hasLOE' => null, 'surveillanceRemark' => $request->remark, 'forResubmit' => 1])){
							$monDet = DB::table('surv_form')->where('survid',$request->survid)->first();
							if(DB::table('technicalfindingshist')->insert(['LOE' => $monDet->LOE, 'attached_filesUser'=> $monDet->attachments, 'id' => $monDet->survid, 'fromWhere' => 'surv'])){
								$appid = DB::table('surv_form')->where('survid',$request->survid)->select('appid','faciEmail','compid','name_of_faci')->first();
								if(isset($appid->appid)){
									$uid = AjaxController::getUidFrom($appid->appid);
									AjaxController::notifyClient($request->survid,$uid,65);
									
								}

								if(isset($appid->faciEmail)){
									$dataToBeSend = array('name' => $appid->name_of_faci, 'action' => 'Surveillance', 'url' => asset('client1/action/sendActionTaken/surv/'.$request->survid));
									Mail::send('employee.others.mailForActivityLogResent', $dataToBeSend, function($message) use ($request,$appid) {
						               $message->to($appid->faciEmail);
						               $message->subject('DOHOLRS Violation on Facility');
						               $message->from('doholrs@gmail.com','DOH Support');
						            });

								}


								return back()->with('errRet', ['errAlt'=>'success', 'errMsg'=>'Operation Successful.']);
							}
						}
					}
				} 
				
				catch (Exception $e) {
					dd($e);
				}

			}
		}
		
		public function SurveillanceTeamsOthers(Request $request)
		{
			if(in_array(true, AjaxController::isSessionExist(['employee_login']))){
				if ($request->isMethod('get')) 
				{
					try 
					{
						$allDataSql = "SELECT * FROM surv_form LEFT JOIN facilitytyp on facilitytyp.facid = surv_form.type_of_faci  WHERE team IS NULL ";
						$allData = DB::select($allDataSql);
						$allRec = AjaxController::getAllSurveillanceRecommendation();
						$allTeam = AjaxController::getAllTeams();
						$empWOTeam = AjaxController::getEmployeeWithoutTeamOthers();
						$empWTeam = AjaxController::getEmployeeWithTeamOthers();
						$empData = AjaxController::getCurrentUserAllData();
						$allMonTeam = AjaxController::getAllSurvTeam();
						// dd($empWTeam);
						return view('employee.others.SurveillanceTeams', ['AllData'=>$allData, 'AllRec'=>$allRec, 'AllTeam'=>$allTeam, 'WT'=>$empWTeam, 'WOT'=>$empWOTeam, 'emp' => $empData, 'AllMonTeam' => $allMonTeam]);
					} 
					catch (Exception $e) 
					{
						dd($e);
						AjaxController::SystemLogs($e);
						session()->flash('system_error', 'ERROR');
						return view('employee.others.Surveillance')	;
					}
				}
			}
			return redirect('employee');
		}

		public function SurveillanceInspectionOthers(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$allDataSql = "SELECT * FROM surv_form WHERE team IS NOT NULL";
					$allData = DB::select($allDataSql);
					return ($this->agent ? response()->json(array($allData)) : view('employee.others.SurveillanceInspection', ['AllData'=>$allData]));
					// return view('employee.others.SurveillanceInspection', ['AllData'=>$allData]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.Surveillance')	;
				}
			}	
		}

		public function SurveillanceRecommendationOthers(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$allDataSql = "SELECT * FROM surv_form LEFT JOIN facilitytyp on facilitytyp.facid = surv_form.type_of_faci LEFT JOIN verdict ON surv_form.verdict = verdict.vid";
					$allData = DB::select($allDataSql);
					$allRec = AjaxController::getAllSurveillanceRecommendation();
					$allNovDir = AjaxController::getAllNovDirections();
					$allVer = AjaxController::getAllVerdict();
					// $test = AjaxController::getViolationDescById('AST001');
					// dd($allRec[0]->rec_extra);
					// dd($test);

					// Getting the violations
					foreach($allData as $key => $value) {
						if($value->DOHSurveillance != "") {
							$arr = json_decode($value->DOHSurveillance, true);
							$mergedarr = call_user_func_array("array_merge", $arr);

							$violationCount = 0;							
							foreach($mergedarr as $k => $v) {
								// violation count
								$start = explode('/', $k)[0];
								$length = strlen('comp');

								if(substr($k, strlen($start)+1, $length) === 'comp') {
									if($v != 'true') {
										$violationCount++;
									}
								}
							}

							// if($violationCount > 0) {
							// 	// violation table update
							// 	DB::table('surv_form')
							// 					->where('survid', '=', $value->survid)
							// 					->update(['hasViolation'=>1]);

							// 	DB::table('surv_form')
							// 					->where('survid', '=', $value->survid)
							// 					->update(['violation'=>AjaxController::getAllViolationBySurvId($value->survid)]);
							// } else {
							// 	DB::table('surv_form')
							// 					->where('survid', '=', $value->survid)
							// 					->update(['isApproved'=>1]);
							// }
						}
 					}

 					$allData = DB::select($allDataSql);

					return view('employee.others.SurveillanceRecommendation', ['AllData'=>$allData, 'AllRec'=>$allRec, 'AllNov'=>$allNovDir, 'AllVer'=>$allVer]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.Surveillance')	;
				}
			}	
		}	

		////// REQUEST OF ASSISTANCE
		public function RequestAssistanceOthers(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$allDataSql = "(SELECT * from complaints_form where `type` = 'Complaints') UNION (SELECT * from req_ast_form where `type` = 'Assistance')";
					$allData = DB::select($allDataSql);
					// dd($allData);
					$data = AjaxController::getAllRequestForAssistance();
					$data3 = AjaxController::getAllComplaints();
					$data2 = DB::table('complaints_form')->orderBy('ref_no', 'desc')->get();
					$data1 = DB::table('req_ast_form')->orderBy('ref_no', 'desc')->get();

					$faciNameSql = "SELECT DISTINCT appform.appid, appform.uid, appform.facilityname FROM appform WHERE isApprove IS NOT NULL AND hfser_id IN ('LTO','ATO') ORDER BY appform.appid ASC";

					$faciName = DB::select($faciNameSql);

					if(count($data1) < 1) {
						$data1 = array("ref_no"=>"0", "name_of_comp"=>"0", "age"=>"0", "civ_stat"=>"0", "address"=>"0", "gender"=>"0", "req_date"=>"0", "contact_no"=>"0", "name_of_faci"=>"0", "type_of_faci"=>"0", "address_of_faci"=>"0", "name_of_conf_pat"=>"0", "date_of_conf_pat"=>"0", "reqs"=>"0", "comps"=>"0", "signature"=>"0");
						$data1 = (object) $data1;
					}					

					if(count($data2) < 1) {
						$data2 = array("ref_no"=>"0", "name_of_comp"=>"0", "age"=>"0", "civ_stat"=>"0", "address"=>"0", "gender"=>"0", "req_date"=>"0", "contact_no"=>"0", "name_of_faci"=>"0", "type_of_faci"=>"0", "address_of_faci"=>"0", "name_of_conf_pat"=>"0", "date_of_conf_pat"=>"0", "reqs"=>"0", "comps"=>"0", "signature"=>"0");
						$data2 = (object) $data2;
					} 

					for($i=0; $i<count($allData); $i++) {
						$rtemp = explode(', ', $allData[$i]->reqs);
						$ctemp = explode(', ', $allData[$i]->comps);

						$allData[$i]->x_reqs = rtrim($allData[$i]->reqs, ', ');
						$allData[$i]->x_comps = rtrim($allData[$i]->comps, ', ');

						if($allData[$i]->appid != $allData[$i]->name_of_faci)
							$allData[$i]->select_type = (isset($allData[$i]->appid) ? AjaxController::getFacTypeByAppId($request, $allData[$i]->appid) : []);

						for($j=0; $j<count($rtemp); $j++) {
							for($k=0; $k<count($data); $k++) {
								if($rtemp[$j] == $data[$k]->rq_id) {
									$rtemp[$j] = $data[$k]->rq_desc;
								}
							}
						}

						for($j=0; $j<count($ctemp); $j++) {
							for($k=0; $k<count($data3); $k++) {
								if($ctemp[$j] == $data3[$k]->cmp_id) {
									$ctemp[$j] = $data3[$k]->cmp_desc;
								}
							}
						}
						$rtemp = implode(', ', $rtemp);
						$ctemp = implode(', ', $ctemp);
						$allData[$i]->reqs = $rtemp;
						$allData[$i]->comps = $ctemp;
					}
					return view('employee.others.RequestAssistance', ['ROAData'=>$data, 'CompData'=>$data3, 'FormData'=>$data1, 'FacName'=>$faciName, 'AllData'=>$allData]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.RequestAssistance');
				}
			}
		}
		////// COMPLAINTS
		public function ComplaintsOthers(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					return view('employee.others.Complaints');
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error', 'ERROR');
					return view('employee.others.Complaints')	;
				}
			}
		}
		///////////////////////////////////////////////// OTHERS
		///////////////////////////////////////////////// MANAGE
		////// GROUP RIGHTS
		public function GroupRightsManage(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllGroups();
					$data1 = AjaxController::getAllModules();
					$data2 = AjaxController::getAllGroupRights();
					// return dd($data2);
					// 'rights'=>$data2,
					return view('employee.manage.mgrouprights', [ 'groups'=>$data, 'modules'=>$data1]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.manage.mgrouprights');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('x07')->insert(['grp_id' => $request->id, 'grp_desc' => $request->name]);
					$test = DB::insert('INSERT INTO x06 (`grp_id`, `mod_id`, `allow`, `ad_d`, `upd`, `cancel`, `print`, `view`) 
								SELECT COALESCE(?), mod_id, COALESCE(1), COALESCE(1), COALESCE(1), COALESCE(1), COALESCE(1), COALESCE(1)
								FROM x05', [$request->id]);
					if ($test) {
							return "DONE";	
						} else {
							AjaxController::SystemLogs($e);
							return 'ERROR';
						}
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';	
				}
			}
		}
		////// GROUP RIGHTS
		////// GROUP
		public static function GroupManage(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllGroups();
					return view('employee.manage.mgroups', ['GR' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.manage.mgroups');
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('x07')->insert(['grp_id' => $request->id, 'grp_desc' => $request->name]);
					$test = DB::insert('INSERT INTO x06 (`grp_id`, `mod_id`, `allow`, `ad_d`, `upd`, `cancel`, `print`, `view`) 
								SELECT COALESCE(?), mod_id, COALESCE(1), COALESCE(1), COALESCE(1), COALESCE(1), COALESCE(1), COALESCE(1)
								FROM x05', [$request->id]);
					if ($test) {
							return "DONE";	
						} else {
							AjaxController::SystemLogs($e);
							return 'ERROR';
						}
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';	
				}
			}
		}
		////// GROUP
		////// MODULE
		public static function ModuleManage(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllModules();
					return view('employee.manage.mmodules', ['Mods'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.manage.mmodules');					
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					DB::table('x05')->insert(['mod_id' => $request->id, 'mod_desc' => $request->name, 'mod_lvl' => $request->lvl, 'mod_l1' => $request->lvl1, 'mod_l2' => $request->lvl2]);
					$data = DB::table('x07')->get();
					if ($data) {
							for ($i=0; $i < count($data) ; $i++) { 
								DB::table('x06')->insert(['grp_id' => $data[$i]->grp_id, 'mod_id' => $request->id, 'allow' => 1, 'ad_d' => 1, 'upd' => 1, 'cancel' => 1, 'print' => 1, 'view' => 1]);
							}
						}
					return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}
		public function verifyData($data) {
			$return = $data;
			if(!isset($data) && empty($data)) {
				$return  = '';
			}
			return $return;
		}
		////// MODULE
		////// SYSTEM USERS
		public function SystemUsersManage(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getFilteredUsers();
					$data1 = AjaxController::getFilteredTypes();
					$data2 = AjaxController::getAllRegion();
					$data3 = AjaxController::getAllFacilityGroup();
					$data4 = AjaxController::getAllTeams();
					// return dd($data);
					return view('employee.manage.msystemusers', ['users'=>$data, 'types'=>$data1, 'region'=>$data2, 'facilitys' => $data3, 'team' => $data4]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.manage.msystemusers');
					return $e;
				}
			}
			if ($request->isMethod('post')) 
			{
				try 
				{
					$dt = Carbon::now();
		          	$dateNow = $dt->toDateString();
		          	$timeNow = $dt->toTimeString();
		          	$data['pre'] = ($request->pre);
					$data['fname'] 	= ($request->fname);
					$data['mname'] 	= ($request->mname);
					$data['lname'] 	= ($request->lname);
					$data['suf'] 	= ($request->suf);
					$data['rgnid'] 	= ($request->rgn);
					$data['email'] 	= ($request->email);
					$data['cntno'] 	= ($request->cntno);
					$data['posti'] 	= ($request->posti);
					$data['type'] 	= ($request->typ);
					$data['uname'] 	= strtoupper(($request->uname));
					$data['pass'] 	= Hash::make(($request->pass));
					$data['ip'] 	= request()->ip();
					$data['token'] 	= ''; //Str::random(40);
					$data['isActive'] = '1';
					$checkUser = DB::table('x08')
	                    ->where([ ['uid', '=', $data['uname']], ['pwd', '=', $data['pass']] ])
	                    ->select('*')
	                    ->first();
	                $checkEmail = DB::table('x08')->where('email', '=', $data['email'])->first();
	                $checkUID = DB::table('x08')->where('uid',$data['uname'])->exists();
	                if($checkUID){
	                	return 'uidExist';
	                }
	                if ($checkEmail) 
	                {
	                	return 'SAME_EMAIL';
	                } 
	                else 
	                {
	                	if ($checkUser) { // Check if Account Exist
	                    	return 'SAME';
	                    } 
	                    else // Check if Account Exist
	                    { 
	                    	$addedby = session()->get('employee_login');
	                    	$x = $request->mname;
	                    	if ($x != "") {
		                    	$mid = strtoupper($x[0]);
		                    	$mid = $mid.'. ';
		                    } else {
		                    	$mid = ' ';
		                    }
							$name = $request->fname.' '.$mid.''.$request->lname;
	                    	$dataToBeSend = array('name'=>$name, 'token'=>$data['token']);

							// Mail::send('mail4SystemUsers', $dataToBeSend, function($message) use ($request) {
								
							// 	try {
							// 		$message->to($request->email, $request->facility_name)->subject
							// 			('Verify Email Account');
							// 		$message->from('doholrs@gmail.com','DOH Support');
							// 	}
							// 	catch (Exception $error) {
							// 		AjaxController::SystemLogs($error);
							// 		return $error;
							// 	}
							// });

							DB::table('x08')->insert(
				                [
				                    'uid' => $data['uname'],
				                    'pwd' => $data['pass'],
				                    'rgnid' => $data['rgnid'],
				                    'contact' => $data['cntno'],
				                    'position' => $data['posti'],
				                    'email' => $data['email'],
				                    'pre' => $data['pre'],
				                    'fname' => $data['fname'],
				                    'mname' => $data['mname'],
				                    'lname' => $data['lname'],
				                    'suf' => $data['suf'],
				                    'ipaddress' => $data['ip'],
				                    't_date' => $dateNow,
				                    't_time' =>$timeNow,
				                    'grpid' => $data['type'],
				                    'def_faci' => $request->defaci,
				                    'team' => $request->team,
				                    'isActive' => 1,
				                    'isAddedBy' => $addedby->uid,
				                    // 'token' => $data['token'],
				                ]
				            );
							return 'DONE';
						}
	                }
				}
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return $e;
				}
			}
		}
		////// SYSTEM USERS
		////// APPLICANT ACCOUNTS
		public static function ApplicantAccountsManage(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllApplicantAccounts();
					return view('employee.manage.mapplicantaccounts', ['users' => $data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.manage.mapplicantaccounts');
				}
			}
		}
		////// APPLICANT ACCOUNTS
		////// SYSTEM LOGS
		public function SystemLogsManage(Request $request)
		{
			if ($request->isMethod('get')) 
			{
				try 
				{
					$data = AjaxController::getAllSystemLogs();
					// return dd($data);
					return view('employee.manage.msystemlogs', ['results'=>$data]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.manage.msystemlogs');
				}
			}
		}
		////// SYSTEM LOGS
		///////////////////////////////////////////////// MANAGE


		public function printOR($appid){
			$check = DB::table('appform')->where('appid',$appid)->select('isCashierApprove')->first()->isCashierApprove;
			if($check > 0){
				$applicationData = DB::table('appform')
				->leftJoin('city_muni','appform.cmid','city_muni.cmid')
				->leftJoin('province','appform.provid','province.provid')
				->where('appid',$appid)
				->select('appform.street_number','appform.street_name','city_muni.cmname','province.provname')
				->first();
				$sql = "SELECT fname, mname, lname, authorizedsignature FROM x08 WHERE uid = (SELECT uid FROM appform WHERE appid = '$appid')";
				$payor = DB::select($sql);
				$payor = (!empty(array($payor[0]->fname.$payor[0]->mname.$payor[0]->lname)[0]) ? $payor[0]->fname . " " . $payor[0]->mname . " " . $payor[0]->lname : $payor[0]->authorizedsignature);
				$applicationData = $applicationData->street_number. " " . $applicationData->street_name. " " . $applicationData->cmname . " " . $applicationData->provname;
				$currentUser = $cur_user = AjaxController::getCurrentUserAllData();
				$payments = AjaxController::getAllDataOrderOfPaymentUploads($appid ,5);
				// dd($payments);
				foreach ($payments as $key => $value) {
					if(!empty($value->ORRef)){
						$or = $value->ORRef;
					}
				}
				$data2 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,4);
				// dd(['payments'=>$payments, 'cUser' => $currentUser, 'address' => ucwords($applicationData), 'payor' => ucwords($payor), 'or' => $or]);
				// dd($payments);
				// dd($data2);
				return view('employee.processflow.or',['payments'=>$payments, 'cUser' => $currentUser, 'address' => ucwords($applicationData), 'payor' => ucwords($payor), 'or' => (!empty($or) ? $or : 'Not Set')]);	
			}
			return 'Forbidden';
			
		}

		public function printORFDA($appid){
			$check = DB::table('appform')->where('appid',$appid)->select('isCashierApprove')->first()->isCashierApprove;
			if($check > 0){
				$applicationData = DB::table('appform')
									->leftJoin('city_muni','appform.cmid','city_muni.cmid')
									->leftJoin('province','appform.provid','province.provid')
									->where('appid',$appid)
									->select('appform.street_number','appform.street_name','city_muni.cmname','province.provname')
									->first();
				$sql = "SELECT fname, mname, lname, authorizedsignature FROM x08 WHERE uid = (SELECT uid FROM appform WHERE appid = '$appid')";
				$payor = DB::select($sql);
				$payor = (!empty(array($payor[0]->fname.$payor[0]->mname.$payor[0]->lname)[0]) ? $payor[0]->fname . " " . $payor[0]->mname . " " . $payor[0]->lname : $payor[0]->authorizedsignature);
				$applicationData = $applicationData->street_number. " " . $applicationData->street_name. " " . $applicationData->cmname . " " . $applicationData->provname;
				$currentUser = $cur_user = AjaxController::getCurrentUserAllData();
				$payments = AjaxController::getAllDataOrderOfPaymentUploads($appid ,5);
				// dd($payments);
				foreach ($payments as $key => $value) {
					if(!empty($value->ORRef)){
						$or = $value->ORRef;
					}
				}
				$data2 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,4);
				// dd(['payments'=>$payments, 'cUser' => $currentUser, 'address' => ucwords($applicationData), 'payor' => ucwords($payor), 'or' => $or]);
				return view('employee.processflow.or',['payments'=>$payments, 'cUser' => $currentUser, 'address' => ucwords($applicationData), 'payor' => ucwords($payor), 'or' => (!empty($or) ? $or : 'Not Set')]);	
			}
			return 'Forbidden';
			
		}
		//////CASHIER
		/////////////////////////////////////////////////PROCESS FLOW
		public function cashierActions(Request $request, $appid, $facid = false)
		{
			// if(DB::table('appform')->where([['appid', $appid],['isrecommended',1], /*['isCashierApprove',null],*/['isPayEval',1]])->count() <= 0){
			// 	return redirect('employee/dashboard/processflow/cashier');
			// 	// return redirect()->back();
			// }//6-2-2021
			$cur_user = AjaxController::getCurrentUserAllData();
			if ($request->isMethod('get')) 
			{
				try 
				{
					$userChoosenPaymentMet = DB::table('chgfil')->join('charges','charges.chg_code','chgfil.paymentMode')->where([['appform_id',$appid],['userChoosen',1]])->first();
					// dd($userChoosenPaymentMet);
					$paymentsRec = 0;
					$canAdd = DB::table('chgfil')->where('appform_id',$appid)->whereNotNull('recievedBy')->doesntExist();
					$paymentMethod = DB::table('charges')->where([['cat_id','PMT'],['forWhom','HFSRB']])->get();
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$data1 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,5);
					$data2 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,4);
					$data3 = AjaxController::getAllDataOrderOfPaymentUploads($appid ,2);
					$data4 = AjaxController::getAllOrderOfPayment();
					$data5 = AjaxController::getAllDataOrderOfPaymentUploads($data->aptid ,3);
					$uacs = AjaxController::getAllUACS();
					$code = $data->hfser_id.'R'.$data->rgnid.'-'.$data->appid;
					foreach($data1 as $payments){
						if($payments->cat_id === 'PMT'){
							$paymentsRec +=1;
						}
					}
					// dd($data1);
					return view('employee.processflow.pfcashieractions',['AppData'=>$data, 'Payments' => $data1, 'Sum' => $data2, 'OOPs' =>$data4, 'Chrges' =>$data5, 'APPID' => $appid, 'loggedIn'=>$cur_user, 'appform_id'=> $appid, 'paymentMethod'=>$paymentMethod, 'aptid'=>$facid, 'code' => $code, 'canAdd' => $canAdd, 'paymentRec' => $paymentsRec, 'uacs' => $uacs, 'uChoosen' => $userChoosenPaymentMet]);
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.processflow.pfcashieractions');
				}
			}

			
			if ($request->isMethod('post')) 
			{
				try 
				{
					if(empty($request->action)){
						$Cur_useData = AjaxController::getCurrentUserAllData();
				  		$getData = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->select('chg_num')->first();
				  		$test = DB::table('chgfil')->insert([
				  						'chgapp_id' => $request->id,
				  						'chg_num' => $getData->chg_num,
				  						'appform_id' => $request->appid,
				  						'reference' => $request->desc,
				  						'amount' => $request->amount,
				  						't_date' => $Cur_useData['date'],
				  						't_time' => $Cur_useData['time'],
				  						't_ipaddress' => $Cur_useData['ip'],
				  						'uid' => $Cur_useData['cur_user'],
				  						'sysdate' => $Cur_useData['date'],
				  						'systime' => $Cur_useData['time']
				  					]);
				  		$upd = array('chg_num'=>(intval($getData->chg_num) + 1));
				  		$test2 = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->update($upd);
			  		} elseif($request->action == 'evalute') {
			  			DB::table('chgfil')->where([['appform_id',$appid],['chg_num','<>',null],['isPaid',null]])->update(['isPaid'=>1]);
			  			$update = DB::table('appform')->where('appid',$request->appid)->update(['CashierApproveBy'=>$cur_user['cur_user'],'CashierApproveDate' => Date('Y-m-d',strtotime('now')), 'CashierApproveTime' => Date('H:i:s',strtotime('now')), 'CashierApproveIp' => $request->ip(), 'isCashierApprove' => 1, 'proofpaystat' => 'posted']);
			  			if($update){
			  				$uid = AjaxController::getUidFrom($request->appid);
			  				AjaxController::notifyClient($request->appid,$uid,31);
			  				return 'DONE';
			  			} else {
			  				return 'ERROR';
			  			}
			  		} else if($request->action == 'edit_uacs') {
			  			$test2 = DB::table('chgfil')->where('id',$request->id)->update(['m04ID_FK' => $request->uacs]);
			  		}
			  		return 'DONE';
				} 
				catch (Exception $e) 
				{
					return $e;
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		///////FDA CASHIER
		public function cashierActionsFDA(Request $request, $appid, $facid = false)
		{
			$cur_user = AjaxController::getCurrentUserAllData();
			if ($request->isMethod('get')) 
			{
				try 
				{
					$paymentMethod = DB::table('charges')->where('cat_id','PMT')->get();
					$payables = DB::table('fda_chgfil')->where([['amount','<', 0],['appid',$appid],['paymentFor', 'cdrrhr']])->get();
					$flag = DB::table('fda_chgfil')->where([['appid',$appid],['paymentFor', 'cdrrhr']])->exists();
					$balance = DB::table('fda_chgfil')
								->where([['appid',$appid],['amount', '>', 0]])
								->whereNotNull('MAvalue')
								->Orwhere([['appid',$appid],['amount', '>', 0],['uid','SYSTEM'],['lrfFor','cdrrhr']])
								->sum('amount');
					$paymentsGiven = (DB::table('fda_chgfil')->where([['appid',$appid],['paymentFor','cdrrhr']])->sum('amount') ?? 0);
					$balance = (isset($paymentsGiven) ? $balance+$paymentsGiven : $balance);
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$code = $data->hfser_id.'R'.$data->rgnid.'-'.$data->appid;
					$canView = AjaxController::canViewFDAOOP($appid);
					return view('employee.FDA.pfcashieractions',['AppData'=>$data, 'loggedIn'=>$cur_user, 'appid'=> $appid, 'paymentMethod'=>$paymentMethod, 'aptid'=>$facid, 'code' => $code, 'Sum' => $balance, 'payables' => $payables, 'canView' => $canView, 'flag' => $flag]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.FDA.pfcashieractions');
				}
			}

			
			if ($request->isMethod('post')) 
			{
				try 
				{
					if(empty($request->action)){
						$Cur_useData = AjaxController::getCurrentUserAllData();
				  		$getData = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->select('chg_num')->first();
				  		$test = DB::table('chgfil')->insert(
			  			[
	  						'chgapp_id' => $request->id,
	  						'chg_num' => $getData->chg_num,
	  						'appform_id' => $request->appid,
	  						'reference' => $request->desc,
	  						'amount' => $request->amount,
	  						't_date' => $Cur_useData['date'],
	  						't_time' => $Cur_useData['time'],
	  						't_ipaddress' => $Cur_useData['ip'],
	  						'uid' => $Cur_useData['cur_user'],
	  						'sysdate' => $Cur_useData['date'],
	  						'systime' => $Cur_useData['time'],
			  			]
			  			);
				  		$upd = array('chg_num'=>(intval($getData->chg_num) + 1));
				  		$test2 = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->update($upd);
			  		} elseif($request->action == 'evalute') {

						


			  			$update = DB::table('appform')->where('appid',$request->appid)->update(['CashierApproveByFDA'=>$cur_user['cur_user'],'CashierApproveDateFDA' => $cur_user['date'], 'CashierApproveTimeFDA' => $cur_user['time'], 'CashierApproveIpFDA' => $cur_user['ip'], 'isCashierApproveFDA' => 1, 'FDAstatus' => 'FI', 'FDAStatMach' => 'For Evaluation', 'proofpaystatMach' => 'posted']);
			  			// $update = DB::table('appform')->where('appid',$request->appid)->update(['CashierApproveByFDA'=>$cur_user['cur_user'],'CashierApproveDateFDA' => $cur_user['date'], 'CashierApproveTimeFDA' => $cur_user['time'], 'CashierApproveIpFDA' => $cur_user['ip'], 'isCashierApproveFDA' => 1, 'FDAstatus' => 'FA', 'FDAStatMach' => 'For Evaluation', 'proofpaystatMach' => 'posted']);
			  			if($update){
			  				return 'DONE';
			  			} else {
			  				return 'ERROR';
			  			}
			  		}
			  		return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function cashierActionsPharmaFDA(Request $request, $appid, $facid = false)
		{
			// if(DB::table('appform')->where([['appid', $appid],['isrecommended',1], /*['isCashierApprove',null],*/['isPayEval',1]])->count() <= 0){
				// return redirect('employee/dashboard/processflow/cashier');
				// return redirect()->back();
			// }
			$cur_user = AjaxController::getCurrentUserAllData();
			if ($request->isMethod('get')) 
			{
				try 
				{
					$paymentMethod = DB::table('charges')->where('cat_id','PMT')->get();
					$payables = DB::table('fda_chgfil')->where([['amount','<', 0],['appid',$appid],['paymentFor', 'cdrr']])->get();
					$flag = DB::table('fda_chgfil')->where([['appid',$appid],['paymentFor', 'cdrr']])->exists();
					$balance = DB::table('fda_chgfil')
								->where([['appid',$appid],['uid', '<>', 'SYSTEM'],['amount','>', 0]])
								->whereNull('MAvalue')
								->Orwhere([['appid',$appid],['amount', '>', 0],['uid','SYSTEM'],['lrfFor','cdrr']])
								->sum('amount');

					$paymentsGiven = (DB::table('fda_chgfil')->where([['appid',$appid],['paymentFor','cdrr']])->sum('amount') ?? 0);
					$balance = (isset($paymentsGiven) ? $balance+$paymentsGiven : $balance);
					$data = AjaxController::getAllDataEvaluateOne($appid);
					$code = $data->hfser_id.'R'.$data->rgnid.'-'.$data->appid;
					$canView = AjaxController::canViewFDAOOP($appid);
					return view('employee.FDA.pfcashieractionspharma',['AppData'=>$data, 'loggedIn'=>$cur_user, 'appid'=> $appid, 'paymentMethod'=>$paymentMethod, 'aptid'=>$facid, 'code' => $code, 'Sum' => $balance, 'payables' => $payables, 'canView' => $canView, 'flag' => $flag]);
				} 
				catch (Exception $e) 
				{
					dd($e);
					AjaxController::SystemLogs($e);
					session()->flash('system_error','ERROR');
					return view('employee.FDA.pfcashieractions');
				}
			}

			
			if ($request->isMethod('post')) 
			{
				try 
				{
					if(empty($request->action)){
						$Cur_useData = AjaxController::getCurrentUserAllData();
				  		$getData = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->select('chg_num')->first();
				  		$test = DB::table('chgfil')->insert([
				  						'chgapp_id' => $request->id,
				  						'chg_num' => $getData->chg_num,
				  						'appform_id' => $request->appid,
				  						'reference' => $request->desc,
				  						'amount' => $request->amount,
				  						't_date' => $Cur_useData['date'],
				  						't_time' => $Cur_useData['time'],
				  						't_ipaddress' => $Cur_useData['ip'],
				  						'uid' => $Cur_useData['cur_user'],
				  						'sysdate' => $Cur_useData['date'],
				  						'systime' => $Cur_useData['time'],
				  					]);
				  		$upd = array('chg_num'=>(intval($getData->chg_num) + 1));
				  		$test2 = DB::table('chg_app')->where('chgapp_id', '=', $request->id)->update($upd);
			  		} elseif($request->action == 'evalute') {
			  			$update = DB::table('appform')->where('appid',$request->appid)->update(['CashierApproveByPharma'=>$cur_user['cur_user'],'CashierApproveDatePharma' => $cur_user['date'], 'CashierApproveTimePharma' => $cur_user['time'], 'CashierApproveIpPharma' => $cur_user['ip'], 'isCashierApprovePharma' => 1, 'FDAstatus' => 'FI', 'FDAStatPhar' => 'For Evaluation', 'proofpaystatPhar' => 'posted']);
			  			if($update){
			  				return 'DONE';
			  			} else {
			  				return 'ERROR';
			  			}
			  		}
			  		return 'DONE';
				} 
				catch (Exception $e) 
				{
					AjaxController::SystemLogs($e);
					return 'ERROR';
				}
			}
		}

		public function chAction(Request $request){
			if($request->action == 'delete'){
				if(DB::table('chgfil')->where('id',$request->id)->delete()){
					return 'SUCCESS';
				} else {
					return "no";
				}
			} else {
				if($request->action == 'edit'){
					$update = array("ORRef"=>$request->or, "depositNum"=>$request->slip, "otherRef"=>$request->ref,"amount"=>$request->amt);
					if(DB::table('chgfil')->where('id',$request->id)->update($update)){
						return "SUCCESS";
					} else {
						return "ERROR";
					}
				}
			}
		}

		public function chActionFDA(Request $request){
			if($request->action == 'delete'){
				if(DB::table('fda_chgfil')->where('fda_chgfilid',$request->id)->delete()){
					return 'SUCCESS';
				} else {
					return "no";
				}
			} else {
				if($request->action == 'edit'){
					$update = array("ORRef"=>$request->or, "otherRef"=>$request->ref,"amount"=>'-'.$request->amt);
					if(DB::table('fda_chgfil')->where('fda_chgfilid',$request->id)->update($update)){
						return "SUCCESS";
					} else {
						return "ERROR";
					}
				}
			}
		}

		public function savePayment(Request $request)
		{
			// dd($request->all());
			if(session()->has('employee_login')){
				if(DB::table('chgfil')->where('appform_id',$request->appform_idSubmit)->whereNotNull('recievedBy')->doesntExist()){
					$user = AjaxController::getCurrentUserAllData();
					$filename = null;
					if($request->hasFile('attFile')){
						$filename = FunctionsClientController::uploadFile($request->attFile)['fileNameToStore'];
					}
					$filename = (isset($request->otherFile) ? $request->otherFile : $filename);
					DB::table('chg_app')->insert([
						'chg_num' => 1,
						'chgopp_seq' => 1,
						'chg_code' => $request->mPay,
						'amt' => 0,
						'aptid' =>(!empty($request->aptid) ? $request->aptid : null),
						'remarks' => 'Payment'
					]);
					$id = DB::getPdo()->lastInsertId();
					if($id){
						if(DB::table('chgfil')->where('uid',$request->appform_idSubmit)->insert(['amount' => $request->aPaid, 'otherRef'=>$request->otherRef, /*'depositNum'=>$request->slipNum,*/ 'ORRef'=>$request->orRef, 'paymentMode'=> $request->mPay, 'attachedFile'=>$filename, 'recievedBy'=>$user['cur_user'], 'paymentDate'=>date("Y-m-d",strtotime('now')), 'appform_id'=>$request->appform_idSubmit,'reference'=>'Payment','chgapp_id'=>$id, 'm04ID_FK' =>$request->uacs, 'draweeBank' => $request->drawee, 'number' => $request->number])){

							$clienthfser_id = DB::table('appform')->where('appid',$request->appform_idSubmit)->select('hfser_id')->first()->hfser_id;
							$stat = null;
							switch ($clienthfser_id) {
								case 'PTC':
									$stat = 'FPPE';
									break;
								
								default:
									$stat = 'FI';
									break;
							}
							DB::table('appform')->where('appid',$request->appform_idSubmit)->update(['status' => $stat]);
							return redirect('employee/dashboard/processflow/actions/'.$request->appform_idSubmit.'/'.$request->aptid);

						}
					}

				} else {
					session()->flash('system_error','ERROR');
					return redirect('employee/dashboard/processflow/actions/'.$request->appform_idSubmit.'/'.$request->aptid);
				}
			}
			else {
				return 'FORBIDDEN';
			}
		}

		public function savePaymentFDA(Request $request)
		{
			if(session()->has('employee_login')){
				try {
					// if(DB::table('fda_chgfil')->where([['appid',$request->appform_idSubmit],['amount','<',0]])->doesntExist()){
						$user = AjaxController::getCurrentUserAllData();
						$filename = null;
						if($request->hasFile('attFile')){
							$filename = strtotime('now').$request->attFile->getClientOriginalName();
							$request->attFile->storeAs('public/uploaded/', $filename);
						}
						if(DB::table('fda_chgfil')->where('uid',$request->appid)->insert(['amount' => '-'.$request->aPaid, 'otherRef'=>$request->otherRef, 't_date' => $user['date'], 't_time' => $user['time'], 'ORRef'=>$request->orRef, 'paymentMode'=> $request->mPay, 'uid'=>$user['cur_user'], 'paymentDate'=>date("Y-m-d",strtotime('now')), 'appid'=>$request->appform_idSubmit, 'ipaddress' => $user['ip'], 'paymentFor' => $request->for])){
							if(in_array($request->mPay, ['MOP-011'])){
								DB::table('appform')->where('appid',$request->appform_idSubmit)->update(['FDAstatus' => 'FDAFP']);
							}
							return back();
							// return redirect('employee/dashboard/processflow/FDA/actions/'.$request->appform_idSubmit.'/'.$request->aptid);
						}
					// } else {
						
					// }
				} catch (Exception $e) {
					dd($e);
					session()->flash('system_error','ERROR');
					return redirect('employee/dashboard/processflow/FDA/actions/'.$request->appform_idSubmit.'/'.$request->aptid);
				}
				
			}
			else {
				return 'FORBIDDEN';
			}
		}

		// public function samplefix(){
		// 	$toCheck = [95];
		// 	$count = 443.8;

		// 	foreach ($toCheck as $key) {
		// 		$items = DB::table('assessmentcombined')->where('asmtH3ID_FK',$key)->orderBy('asmtComb','ASC')->get();
		// 		foreach($items as $item => $value){
		// 			$count +=.1;
		// 			DB::table('assessmentcombined')->where('asmtComb',$value->asmtComb)->update(['assessmentSeq' => $count]);
		// 		}
		// 	}
		// 	echo $count;
		// }


		public function SaveInspectionMobile(Request $request){
			if ($request->isMethod('get')) {
				return false;
			}

			try {
				if(isset($request->apptype) && isset($request->data)){
					$var = json_decode($request->data,true);
					$forDuplicate = $toRecommendation = $toDeleteID = $toDeleteMon = $toAddID = [];
					AjaxController::customLog(implode('&', array_keys($var)).'-',$request->data);
					
						foreach ($var as $appidkey => $value) {
							$recommendation = ($value['recommendation'][0] ?? null);
							switch ($request->apptype) {

								case 'LTO':
								case 'COA':
								case 'MON':

								if(isset($recommendation)){
										$newStatus = 'FR';
										$recommendationTable = 'assessmentrecommendation';
										$toInspectionSaveTable = 'assessmentcombinedduplicate';
										array_push($toRecommendation, ['choice' => $recommendation['choice'], 'details' => $recommendation['details'], 'valfrom' => Date('Y-m-d',strtotime($recommendation['valfrom'])), 'valto' => Date('Y-m-d',strtotime($recommendation['valto'])), 'days' => (int)$recommendation['days'], 'appid' => $recommendation['appid'], 'selfAssess' => null , 'monid' => (!empty($recommendation['monid']) && $recommendation['monid'] !== 'null' ? $recommendation['monid'] : null), 'noofbed' => (!empty($recommendation['noofbed']) && $recommendation['noofbed'] !== 'null' ? $recommendation['noofbed'] : null), 'noofdialysis' => (!empty($recommendation['noofdialysis']) && $recommendation['noofdialysis'] !== 'null' ? $recommendation['noofdialysis'] : null), 'conforme' => $recommendation['conforme'], 'conformeDesignation' => $recommendation['conformeDesignation'], 'evaluatedby' => $recommendation['evaluatedby']]);
										array_push($toDeleteID, $recommendation['appid']);
										array_push($toDeleteMon, (!empty($recommendation['monid']) && $recommendation['monid'] !== 'null' ? $recommendation['monid'] : null));
										array_push($toAddID, ['appid' => $recommendation['appid'], 'monid' => (!empty($recommendation['monid']) && $recommendation['monid'] !== 'null' ? $recommendation['monid'] : null), 'jsondata' => $request->data ]);

										if(isset($value['data'])){
											foreach ($value['data'] as $dupkey => $dupvalue) {
												array_push($forDuplicate, ['asmtComb_FK' => $dupvalue['asmtComb_FK'], 'assessmentName' => $dupvalue['assessmentName'], 'asmtH3ID_FK' => (!empty($dupvalue['asmtH3ID_FK']) && is_integer($dupvalue['asmtH3ID_FK']) ? $dupvalue['asmtH3ID_FK'] : 0), 'h3name' => ($dupvalue['h3name'] ?? 'NOT SPECIFIED, FROM MOBILE'), 'asmtH2ID_FK' => (!empty($dupvalue['asmtH2ID_FK']) ? $dupvalue['asmtH2ID_FK'] : 0), 'h2name' => (!empty($dupvalue['h2name']) ? $dupvalue['h2name'] : 'NOT SPECIFIED, FROM MOBILE'), 'asmtH1ID_FK' => $dupvalue['asmtH1ID_FK'], 'partID' => ($dupvalue['partID'] ?? null), 'h1name' => $dupvalue['h1name'], 'evaluation' => $dupvalue['evaluation'], 'remarks' => $dupvalue['remarks'], 'assessmentSeq' => $dupvalue['assessmentSeq'], 'evaluatedBy' => $dupvalue['evaluatedBy'], 'assessmentHead' => $dupvalue['assessmentHead'], 'selfassess' => null, 'appid' => $dupvalue['appid'], 'monid' => (!empty($dupvalue['monid']) && $dupvalue['monid'] !== 'null' ? $dupvalue['monid'] : null), 'fromMobile' => 1]);
											}
										}
									}
								break;
								case 'PTC':
									// if(isset($recommendation) && !FunctionsClientController::existOnDB('assessmentrecommendation',array(['appid',$recommendation['appid']],['selfAssess',null],['monid',$recommendation['monid']]))){
										if(isset($value['recommendation'])){
											$newStatus = 'FR';
											foreach ($value['recommendation'] as $key => $recommendation) {
												$recommendationTable = 'assessmentrecommendation';
												$toInspectionSaveTable = 'assessmentcombinedduplicateptc';
												array_push($toRecommendation, ['choice' => $recommendation['choice'], 'details' => $recommendation['details'], 'days' => (int)$recommendation['days'], 'appid' => $recommendation['appid'], 'selfAssess' => null , 'monid' => (empty($recommendation['monid']) ? null : $recommendation['monid']), 'conforme' => $recommendation['conforme'], 'conformeDesignation' => $recommendation['conformeDesignation'], 'evaluatedby' => $recommendation['evaluatedby'], 'revision' => $recommendation['revision']]);
												array_push($toDeleteID, $recommendation['appid']);
												array_push($toAddID, ['appid' => $recommendation['appid'], 'jsondata' => $request->data ]);
											}
										}

										if(isset($value['data'])){
											foreach ($value['data'] as $dupkey => $dupvalue) {
												array_push($forDuplicate, ['asmtComb_FK' => $dupvalue['asmtComb_FK'], 'assessmentName' => $dupvalue['assessmentName'], 'assessmentSeq' =>  $dupvalue['assessmentSeq'], 'assessmentHead' => ($dupvalue['assessmentHead'] ?? 'NOT SPECIFIED, FROM MOBILE'), 'asmtH3ID_FK' => (!empty($dupvalue['asmtH3ID_FK']) ? $dupvalue['asmtH3ID_FK'] : 0), 'h3name' => (!empty($dupvalue['h3name']) ? $dupvalue['h3name'] : 'NOT SPECIFIED, FROM MOBILE'), 'asmtH2ID_FK' => (!empty($dupvalue['asmtH2ID_FK']) ? $dupvalue['asmtH2ID_FK'] : 0), 'h2name' => (!empty($dupvalue['h2name']) ? $dupvalue['h2name'] : 'NOT SPECIFIED, FROM MOBILE'),'asmtH1ID_FK' => (!empty($dupvalue['asmtH1ID_FK']) && is_int($dupvalue['asmtH1ID_FK']) ? $dupvalue['asmtH1ID_FK'] : 0), 'h1name' => (!empty($dupvalue['h1name']) ? $dupvalue['h1name'] : 'NOT SPECIFIED, FROM MOBILE'), 'partID' => (!empty($dupvalue['partID']) ? $dupvalue['partID'] : 'NOT SPECIFIED, FROM MOBILE'), 'evaluation' => (!empty($dupvalue['evaluation']) ? $dupvalue['evaluation'] : 0), 'remarks' => (!empty($dupvalue['remarks']) ? $dupvalue['remarks'] : null), 'evaluatedBy' => (!empty($dupvalue['evaluatedBy']) ? $dupvalue['evaluatedBy'] : 0), 'appid' => (!empty($dupvalue['appid']) ? $dupvalue['appid'] : 0), 'sub' => (!empty($dupvalue['sub']) && $dupvalue['sub'] !== 'null' ? $dupvalue['sub'] : null), 'isdisplay' => (!empty($dupvalue['isdisplay']) ? $dupvalue['isdisplay'] : 0), 'revision' => (!empty($dupvalue['revision']) ? $dupvalue['revision'] : 0), 'parttitle' => $dupvalue['parttitle'], 'fromMobile' => 1]);
											}
										}
									// }
									break;
								
								default:
									return false;
									break;

							}
						}	
						if(isset($newStatus) && $request->apptype != 'MON'){
							DB::table('appform')->whereIn('appid',$toDeleteID)->update(['status' => $newStatus]);
						}
						if($request->apptype !== 'PTC'){
							DB::table($recommendationTable)->whereIn('monid',$toDeleteMon)->whereIn('appid',$toDeleteID)->delete();
							DB::table($toInspectionSaveTable)->whereIn('monid',$toDeleteMon)->whereIn('appid',$toDeleteID)->delete();

						} else {
							DB::table($recommendationTable)->whereIn('appid',$toDeleteID)->delete();
							DB::table($toInspectionSaveTable)->whereIn('appid',$toDeleteID)->delete();
						}
						DB::table('frommobileinspection')->whereIn('monid',$toDeleteMon)->whereIn('appid',$toDeleteID)->delete();

						return response()->json(DB::table($recommendationTable)->insert($toRecommendation) && DB::table($toInspectionSaveTable)->insert($forDuplicate) && DB::table('frommobileinspection')->insert($toAddID));
				}
			} 
			
			catch (Exception $e) {
				AjaxController::SystemLogs($e);
				return response()->json($e);
			}

		}


	}


 ?>