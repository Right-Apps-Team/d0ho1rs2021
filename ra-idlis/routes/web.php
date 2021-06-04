<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* ---- 
Proper Routing

---- */

// New





Route::prefix('client')->group(__DIR__ . '/clients/dashboard.php');
// OLD CLIENT-SIDE
Route::match(['get', 'post'], '/qrcode/{appid}', 'NewClientController@generateForCertificate');
Route::match(['get', 'post'], '/sampleprint', 'AjaxController@tryprint');
Route::match(['get', 'post'], '/', 'NewClientController@__index')->name('client.login');
Route::match(['get', 'post'], '/login_user', 'ClientController@__login');
Route::match(['get', 'post'], '/logout_user', 'ClientController@__logout');
Route::match(['get', 'post'], '/register/verify/{token}', 'ClientController@__rToken');
Route::match(['get', 'post'], '/resend_mail/{uid}', 'ClientController@__rMail');
Route::prefix('client')->group(function() {
	Route::match(['get', 'post'], 'home', 'ClientController@__home')->name('client.home');
	Route::match(['get', 'post'], 'apply', 'ClientController@__applyForm')->name('client.apply');
	Route::prefix('payment')->group(function() {
		Route::match(['get', 'post'], '/', 'ClientController@__gPayment')->name('client.payment');
		Route::match(['get', 'post'], '/app', 'ClientController@__cPayment')->name('client.cpayment');
		Route::match(['get', 'post'], '/{token}/{pmt}', 'ClientController@__pPayment')->name('client.paymentPay');
	});
	Route::match(['get', 'post'], 'evaluation', 'ClientController@__evaluate')->name('client.evaluate');
	Route::match(['get', 'post'], 'inspection', 'ClientController@__inspection')->name('client.inspection');
	Route::match(['get', 'post'], 'issuance', 'ClientController@__issuance')->name('client.inspection');
	Route::match(['get', 'post'], 'listing', 'ClientController@__listing')->name('client.listing');
	Route::prefix('request')->group(function() {
		Route::match(['get', 'post'], '{tbl}', 'ClientController@__rTbl')->name('client.request');
		Route::match(['get', 'post'], 'customQuery/{custom}', 'ClientController@__customQuery')->name('client.custom');
	});
	Route::prefix('certificates')->group(function() {
		Route::match(['get', 'post'], '{hfser}/{appid}', 'ClientController@__byHfser')->name('client.certificates');
	});
});
// NEW CLIENT-SIDE

Route::get('/user/verify/{token}', 'Auth\RegisterController@verifyUser'); // MY CHANGES

// ------------------------------------------------------------------------
Route::get('samplereport',function(){
	return view('client1.FDA.cdrrhrCOC');
});

// Checking 
Route::prefix('check')->group(function() {
	Route::match(['get', 'post'], 'ltoannexs/{appid}', 'CheckingController@checkLtoAnnexs');
});
// Checking

// Route::get('/', 'NewClientController@__index')->name('client1.login');

Route::get('samplefix','DOHController@samplefix');
Route::prefix('client1')->group(function() {
	Route::match(['get', 'post'], '/FAQ', 'NewClientController@faq');
	// Route::match(['get', 'post'], '/sendproofpay', 'NewGeneralController@uploadProofofPay');//6-5-2021
	// Route::match(['get', 'post'], '/', 'NewClientController@__index')->name('client1.login');
	Route::match(['get', 'post'], '/register/verify/{token}', 'NewClientController@__rToken')->name('client1.rtoken');
	Route::match(['get', 'post'], '/forgot/{uid}/a/{token}', 'NewClientController@__forgot');
	Route::match(['get', 'post'], '/reset/{uid}', 'NewClientController@__reset');
	Route::match(['get', 'post'], '/changePass/', 'NewClientController@__changePass');
	Route::match(['get', 'post'], '/home', 'NewClientController@__home')->name('client1.home');
	Route::match(['get', 'post'], '/resend_mail/{uid}', 'NewClientController@__rMail')->name('client1.rmail');
	Route::match(['get', 'post'], '/payment/{token}/{appid}', 'NewClientController@__dPayment')->name('client1.dpayment');
	Route::match(['get', 'post'], '/printPayment/{token}/{appid}', 'NewClientController@__pgPayment')->name('client1.pgpayment');
	Route::match(['get', 'post'], '/printPaymentFDA/{token}/{appid}', 'NewClientController@__fdaPayment');
	Route::match(['get', 'post'], '/fdacertificate/{appid}/{request?}', 'NewClientController@fdacert');
	Route::match(['get', 'post'], '/printPaymentFDACDRR/{token}/{appid}', 'NewClientController@__fdaPaymentCDRR');
	Route::prefix('apply')->group(function() {
		// qweqwe
		Route::match(['get', 'post'], '/', 'NewClientController@__apply')->name('client1.apply');
		Route::match(['get', 'post'], '/change_request/{appid}', 'NewClientController@__change_request');
		Route::match(['get', 'post'], '/new', 'NewClientController@__applyNew')->name('client1.applynew');
		Route::match(['get', 'post'], '/assessmentSend/{appid}/{apptype}', 'NewClientController@AssessmentSend');

		Route::match(['get', 'post'], '/assessmentReady/{appid}', 'NewClientController@assessmentReady');
		Route::match(['get', 'post'], '/HeaderOne/{appid}/{part}/{montype?}', 'NewClientController@assessmentHeaderOne'); // View each

		Route::match(['get', 'post'], '/HeaderTwo/{appid}/{headerOne}/{montype?}', 'NewClientController@AssessmentShowH2'); // View each
		Route::match(['get', 'post'], '/HeaderThree/{appid}/{headerTwo}/{montype?}', 'NewClientController@AssessmentShowH3'); // View each
		Route::match(['get', 'post'], '/ShowAssessments/{appid}/{headerThree}/{montype?}', 'NewClientController@ShowAssessments'); // View each

		Route::match(['get', 'post'], '/SaveAssessments/', 'NewClientController@SaveAssessments'); // View each
		Route::match(['get', 'post'], '/registerAssess', 'NewClientController@assessmentRegister'); // View each
		Route::match(['get', 'post'], 'GenerateReportAssessments/{appid}', 'NewClientController@GenerateReportAssessment'); // View each


		Route::match(['get', 'post'], '/edit/{appid}', 'NewClientController@__applyEdit')->name('client1.applyedit');
		Route::match(['get', 'post'], '/assessmentDisplay/{appid}/{apptype}', 'NewClientController@AssessmentDisplay');
		Route::match(['get', 'post'], '/assessmentView/{appid}/{apptype}/{select}', 'NewClientController@assessmentView');

		Route::get('Redirect-to-application/{appid}','NewClientController@redirectToApplication');

// find me
		Route::match(['get', 'post'], '/attachment/{hfser}/{appid}/{office?}', 'NewClientController@__applyAttach')->name('client1.applyattach');	
		Route::prefix('app')->group(function() {
			Route::match(['get', 'post'], 'showResult/{request}/{appid}', 'NewClientController@viewReport');
			Route::match(['get', 'post'], '/updApp/{appid}', 'NewClientController@__updApp')->name('client1.updapp');
			Route::prefix('{hfser}')->group(function() {
				Route::prefix('{appid}')->group(function() {
					Route::match(['get', 'post'], '/', 'NewClientController@__applyApp')->name('client1.applyapp');
					Route::match(['get', 'post'], '/fda', 'NewClientController@__applyfda')->name('client1.applyfda');
					Route::match(['get', 'post'], '/hfsrb', 'NewClientController@__applyhfsrb')->name('client1.applyhfsrb');
					Route::match(['get', 'post'], '/{aptid}', 'NewClientController@__applyApp_n')->name('client1.applyapp_n');
				});
			});
		});
		Route::prefix('fda')->group(function() {
			Route::prefix('/CDRR/')->group(function() {
				Route::match(['get', 'post'], '/personnel/{appid}', 'NewClientController@cdrrpersonnel');
				Route::match(['get', 'post'], '/receipt/{appid}', 'NewClientController@cdrrreceipt');
				Route::match(['get', 'post'], '/attachments/{appid}', 'NewClientController@cdrrattachments');
			});
			Route::prefix('/CDRRHR/')->group(function() {
				Route::match(['get', 'post'], '/personnel/{appid}', 'NewClientController@cdrrhrpersonnel');
				Route::match(['get', 'post'], '/attachments/{appid}', 'NewClientController@cdrrhrattachments');
				Route::match(['get', 'post'], '/receipt/{appid}', 'NewClientController@cdrrhrreceipt');
				Route::match(['get', 'post'], '/xraymachines/{appid}', 'NewClientController@cdrrhrxraymachine');
				Route::match(['get', 'post'], '/xrayservcat/{appid}', 'NewClientController@cdrrhrxrayservcat');
			});
		});
		Route::prefix('hfsrb')->group(function() {
			Route::match(['get', 'post'], '/annexa/{appid}', 'NewClientController@annexa');
			Route::match(['get', 'post'], '/annexb/{appid}', 'NewClientController@annexb');
			Route::match(['get', 'post'], '/annexc/{appid}', 'NewClientController@annexc');
			Route::match(['get', 'post'], '/annexd/{appid}', 'NewClientController@annexd');
			Route::match(['get', 'post'], '/annexf/{appid}', 'NewClientController@annexf');
			Route::match(['get', 'post'], '/annexh/{appid}', 'NewClientController@annexh');
			Route::match(['get', 'post'], '/annexi/{appid}', 'NewClientController@annexi');
		});
		Route::prefix('hfsrb')->group(function() {
			Route::match(['get', 'post'], 'view/annexa/{appid}', 'NewClientController@viewannexa');
			Route::match(['get', 'post'], 'view/annexb/{appid}', 'NewClientController@viewannexb');
			Route::match(['get', 'post'], 'view/annexc/{appid}', 'NewClientController@viewannexc');
			Route::match(['get', 'post'], 'view/annexd/{appid}', 'NewClientController@viewannexd');
			Route::match(['get', 'post'], 'view/annexf/{appid}', 'NewClientController@viewannexf');
			Route::match(['get', 'post'], 'view/annexh/{appid}', 'NewClientController@viewannexh');
			Route::match(['get', 'post'], 'view/annexi/{appid}', 'NewClientController@viewannexi');
		});
		Route::prefix('fda')->group(function() {
			Route::prefix('/CDRR/')->group(function() {
				Route::match(['get', 'post'], 'view/personnel/{appid}/{tag?}', 'NewClientController@viewcdrrpersonnel');
				// Route::match(['get', 'post'], 'view/receipt/{appid}', 'NewClientController@viewcdrrreceipt');
				// Route::match(['get', 'post'], 'view/attachments/{appid}', 'NewClientController@viewcdrrattachments');
				Route::match(['get', 'post'], 'view/otherattachment/{appid}', 'NewClientController@viewcdrrattachments');
			});
			Route::prefix('/CDRRHR/')->group(function() {
				Route::match(['get', 'post'], 'view/personnel/{appid}', 'NewClientController@viewcdrrhrpersonnel');
				Route::match(['get', 'post'], 'view/otherattachments/{appid}', 'NewClientController@viewcdrrhrattachments');
				Route::match(['get', 'post'], 'view/receipt/{appid}', 'NewClientController@viewcdrrhrreceipt');
				Route::match(['get', 'post'], 'view/xraymachines/{appid}', 'NewClientController@viewcdrrhrxraymachine');
				Route::match(['get', 'post'], 'view/xrayservcat/{appid}', 'NewClientController@viewcdrrhrxrayservcat');
			});
		});
		Route::prefix('employeeOverride')->group(function() {
			Route::prefix('app')->group(function() {
				Route::prefix('{hfser}')->group(function() {
					Route::match(['get', 'post'], '/{appid}', 'NewClientController@__editApp')->name('client1.editapplyapp');
					Route::match(['get', 'post'], '/{appid}/hfsrb', 'NewClientController@__editAppHfsrb')->name('client1.editapplyhfsrb');
					Route::match(['get', 'post'], '/{appid}/fda', 'NewClientController@__editAppFda')->name('client1.editapplyfda');
				});
			});
		});
	});
	Route::prefix('request')->group(function() {
		Route::match(['get', 'post'], '{tbl}', 'NewClientController@__rTbl')->name('client.request');
		Route::match(['get', 'post'], 'customQuery/{custom}', 'NewClientController@__customQuery')->name('client1.custom');
		Route::match(['get', 'post'], 'payment/{token}/{chgapp_id}/{appid}', 'NewClientController@__payment')->name('client1.payment');
	});
	Route::prefix('certificates')->group(function() {
		Route::match(['get', 'post'], 'view/{appid}', 'NewClientController@viewCert');
		Route::match(['get', 'post'], 'view/external/{appid}', 'NewClientController@viewCertExt');
		Route::match(['get', 'post'], '{hfser}/{appid}', 'NewClientController@__byHfser')->name('client1.certificates');
	});
	Route::prefix('action')->group(function() {
		Route::match(['get', 'post'], 'sendActionTaken/{from}/{actionid}', 'NewClientController@sendActionTaken');
		Route::match(['get', 'post'], 'viewSchedule/{appid}', 'NewClientController@viewInsSched');
	});
	Route::prefix('nov')->group(function() {
		Route::match(['get', 'post'], '{appid}', 'NewClientController@__novm')->name('client1.novm');
	});
});
// MHEL BAMBOK
///////////////////// EMPLOYEE
/////// DOH CONTROLLER
///// GENERAL -------------------
Route::match(['get', 'post'], '/view/notification', 'AjaxController@getNotification');
Route::match(['get', 'post'], '/update/notification', 'AjaxController@updateNotification');

Route::match(['get', 'post'], '/employee/forMobile/{forMobile?}', 'DOHController@login')->name('employee');
// Login
Route::match(['get', 'post'], '/employee/', 'DOHController@login')->name('employee');
// Forgot Password
Route::match(['get', 'post'], '/employee/forgot', 'DOHController@forgotPassword')->name('ForgotPassword');
//
Route::match(['get', 'post'], '/employee/forgot/{token}', 'DOHController@forgotChangePassword');
Route::match(['get', 'post'], '/employee/reset/password/{uid}', 'DOHController@resetPassword');
// Logout
Route::match(['get', 'post'], '/employee/logout','DOHController@logout');
// Dashboard
Route::get('/employee/dashboard', 'DOHController@dashboard')->name('eDashboard');
// Resend Verification Code
Route::get('employee/resend/{id}','DOHController@resend_ver');
// Verify Account
Route::get('employee/verify/{id}', 'DOHController@verify_account');
// Download File
Route::get('/file/download/{id}','AjaxController@DownloadFile')->name('DownloadFile');
// Open File
Route::get('/file/open/{id}','AjaxController@OpenFile')->name('OpenFile');
// Mail
Route::view('/mailTest2', 'mail4SystemUsers');
// ChangePassword
Route::post('employee/change_pass', 'AjaxController@ChangePassword');
// Notifications 
Route::post('employee/notification/toggle', 'AjaxController@toggleNotification');
Route::get('employee/notification/get_notification', 'AjaxController@getNotification'); 
Route::get('employee/notification', 'DOHController@Notification');
// GroupRights
Route::get('employee/getRights', 'DOHController@getGroupRights');
///// MASTER FILE ---------------------------------------------------------------------------
// Team
Route::match(['get', 'post'], 'employee/dashboard/mf/team', 'DOHController@MfTeam'); // Main, Add
Route::post('employee/mf/save_team', 'AjaxController@saveTeam'); // Update
Route::post('employee/mf/del_test', 'AjaxController@delTeam'); // Delete
// Manage Team
Route::post('employee/mf/delMemberInTeam', 'AjaxController@delMemberInTeam'); // Delete Member
Route::post('employee/mf/getEmployeeWithoutTeam', 'AjaxController@getEmployeeWithoutTeam'); // Get Employees without team
Route::post('employee/mf/getMembersInTeam', 'AjaxController@getMembersInTeam'); // Get members using x08
Route::match(['get', 'post'], 'employee/dashboard/mf/manage/teams', 'DOHController@MfManageTeam'); // Main, Add
// Application Type
Route::match(['get', 'post'], 'employee/dashboard/mf/apptype', 'DOHController@AppType'); // Main, Add
Route::match(['get', 'post'], 'employee/dashboard/mf/licenseValidity', 'DOHController@licenseValidity'); // application license validity
Route::post('employee/mf/save_apptype', 'AjaxController@saveAppType'); // Update
Route::post('employee/mf/del_apptype', 'AjaxController@delAppType'); // Delete
// other ancillary
Route::match(['get', 'post'], 'employee/dashboard/mf/servicetype', 'DOHController@ancilliary'); // Main, Add
Route::match(['get', 'post'], 'employee/dashboard/mf/applylocation', 'DOHController@apploc'); // apply location
Route::match(['get', 'post'], 'employee/dashboard/mf/chargelocation', 'DOHController@chgloc'); // apply location
// Application Status
Route::match(['get', 'post'], 'employee/dashboard/mf/appstatus', 'DOHController@AppStatus'); // Main, Add
Route::post('employee/mf/save_appstatus', 'AjaxController@saveAppStatus'); // Update
Route::post('employee/mf/del_appstatus', 'AjaxController@delAppStatus'); // Delete
// Class
Route::match(['get', 'post'], 'employee/dashboard/mf/class', 'DOHController@Class'); // Main, Add
Route::post('employee/mf/save_class', 'AjaxController@saveClass'); // Update
Route::post('employee/mf/del_class', 'AjaxController@delClass'); // Delete
// Holidays
Route::match(['get', 'post'], 'employee/dashboard/mf/holidays', 'DOHController@Holidays'); // Main, Add
Route::post('employee/mf/getCalendarEvents/{selected}', 'AjaxController@getHolidaysEvent'); // Get Holidays Events for Calendar 
Route::post('employee/mf/save_holiday', 'AjaxController@saveHoliday'); // Update
Route::post('employee/mf/del_holiday', 'AjaxController@delHoliday'); // Delete
// Ownership
Route::match(['get', 'post'], 'employee/dashboard/mf/ownership', 'DOHController@Ownership'); // Main, Add
Route::post('employee/mf/save_ownership', 'AjaxController@saveOwnership'); // Update
Route::post('employee/mf/del_ownership', 'AjaxController@delOwnership'); // Delete
// Functions
Route::post('employee/mf/save_functions', 'AjaxController@saveFunctions'); // Update
Route::post('employee/mf/del_functions', 'AjaxController@delFunctions'); // Update
Route::match(['get', 'post'], 'employee/dashboard/mf/functions', 'DOHController@Functions'); // Main, Add
// Institutional Character
Route::get('employee/mf/del_institutionalcharacter', 'AjaxController@delInstitutionalCharacter'); // Delete
Route::get('employee/mf/save_institutionalcharacter', 'AjaxController@saveInstitutionalCharacter'); // Update
Route::match(['get', 'post'], 'employee/dashboard/mf/institutionalcharacter', 'DOHController@InstitutionalCharacter'); //Main, Add
// Facility
Route::match(['get', 'post'], 'employee/dashboard/mf/facility', 'DOHController@Facility'); // Main, Add
Route::match(['get', 'post'], 'employee/dashboard/mf/For-Ambulance', 'DOHController@forAmbulance'); // Main, Add
Route::post('employee/mf/save_facility', 'AjaxController@saveFacility'); // Update
Route::post('employee/mf/del_facility', 'AjaxController@delFacility'); // Delete
// Services 
Route::post('employee/mf/del_service', 'AjaxController@delService'); // Update 
Route::post('employee/mf/save_service', 'AjaxController@saveService'); // Update 
Route::match(['get', 'post'], 'employee/dashboard/mf/services', 'DOHController@Services');

Route::match(['get', 'post'], 'employee/dashboard/mf/servicesuploads', 'DOHController@ServicesUpload');

// Manage Facility
Route::match(['get', 'post'], 'employee/dashboard/mf/manage/facilities', 'DOHController@ManageFacility'); // Main, Add
Route::match(['get', 'post'], 'employee/dashboard/mf/group/facilities', 'DOHController@GroupFacility'); // Main, Add
// payment Location
Route::match(['get', 'post'], 'employee/dashboard/mf/payment/location', 'DOHController@paymentLocation'); // Main, Add
Route::post('employee/mf/get_facilities', 'AjaxController@getAllFacilities'); // Get Facilities under selected 
Route::post('employee/mf/get_facilitiesWithExtras', 'AjaxController@getAllPaymentLocation'); // Get Facilities under selected with location and facility to apply 
Route::post('employee/mf/del_facilities', 'AjaxController@delFacilities'); // Delete
Route::post('employee/mf/del_facilitiesWithOthers', 'AjaxController@delFacilitiesWithOthers'); // Delete
Route::post('employee/mf/get_managefacility', 'AjaxController@getAllManageFacility'); // Get Facilities under selected Application Type
Route::post('employee/mf/del_managefacility', 'AjaxController@delManageFacility'); // Delete
// Manage Services
// Route::match(['get', 'post'], 'employee/dashboard/mf/manage/services', 'DOHController@ManageServices'); // Main, Add
// Manage Requirements
Route::match(['get', 'post'], 'employee/dashboard/mf/manage/requirements', 'DOHController@ManageRequirements'); // Main, Add
Route::post('employee/mf/facility/getRequirements', 'AjaxController@getRequirementsFiltered'); // Get Requirements (Filtered)
Route::post('employee/mf/del_requirements', 'AjaxController@delRequirements'); // Delete
// Transaction Status
Route::match(['get', 'post'], 'employee/dashboard/mf/transactionstatus', 'DOHController@TransactionStatus'); // Main, Add
Route::post('employee/mf/save_transactionstatus', 'AjaxController@saveTransactionStatus'); // Update
Route::post('employee/mf/del_transactionstatus', 'AjaxController@delTransactionStatus'); // Delete
// Uploads
Route::match(['get', 'post'], 'employee/dashboard/mf/uploads', 'DOHController@Uploads'); // Main, Add
Route::post('employee/mf/save_upload', 'AjaxController@saveUpload'); // Update
Route::post('employee/mf/del_upload', 'AjaxController@delUpload'); // Delete
// Department
Route::match(['get', 'post'], 'employee/dashboard/mf/department', 'DOHController@Department'); // Main, Add
Route::post('employee/mf/save_department', 'AjaxController@saveDepartment'); // Update
Route::post('employee/mf/del_department', 'AjaxController@delDepartment'); // Delete
// Section
Route::match(['get', 'post'], 'employee/dashboard/mf/section', 'DOHController@Section'); // Main, Add
Route::post('employee/mf/save_section', 'AjaxController@saveSection'); // Update
Route::post('employee/mf/del_section', 'AjaxController@delSection'); // Delete
// Work/Position
Route::match(['get', 'post'], 'employee/dashboard/mf/position', 'DOHController@Work'); // Main, Add
// Route::post('employee/mf/save_work', 'AjaxController@saveWork'); // Update
// Route::post('employee/mf/del_work', 'AjaxController@delWork'); // Delete
Route::post('employee/mf/save_position', 'AjaxController@savePosition'); // Update
Route::post('employee/mf/del_position', 'AjaxController@delPosition'); // Delete
//Work Status
Route::match(['get', 'post'], 'employee/dashboard/mf/workstatus', 'DOHController@WorkStatus'); // Main, Add
Route::post('employee/mf/save_workstatus', 'AjaxController@saveWorkStatus'); // Update
Route::post('employee/mf/del_workstatus', 'AjaxController@delWorkStatus');
// License Type
Route::match(['get', 'post'], 'employee/dashboard/mf/licensetype', 'DOHController@LicenseType'); // Main, Add
Route::post('employee/mf/save_licensetype', 'AjaxController@saveLicenseType'); // Update
Route::post('employee/mf/del_licensetype', 'AjaxController@delLicenseType'); // Delete
// Training
Route::match(['get', 'post'], 'employee/dashboard/mf/training', 'DOHController@Training'); // Main, Add
Route::post('employee/mf/save_trainings', 'AjaxController@saveTraining'); // Update
Route::post('employee/mf/del_training', 'AjaxController@delTraining');
// Region
Route::match(['get', 'post'], 'employee/dashboard/ph/regions', 'DOHController@Regions'); // Main, Add
//branch
Route::match(['get', 'post'], 'employee/dashboard/ph/branch', 'DOHController@branch'); // Main, Add
Route::match(['get', 'post'], 'employee/dashboard/ph/notificationMessages', 'DOHController@notificationMessages'); // Main, Add

Route::post('employee/mf/save_region', 'AjaxController@saveRegion'); // Update
Route::post('employee/mf/del_region', 'AjaxController@delRegion'); // Delete
// Province
Route::match(['get', 'post'], 'employee/dashboard/ph/provinces', 'DOHController@Provinces'); // Main, Add
Route::post('employee/mf/save_province', 'AjaxController@saveProvince'); // Update
Route::post('employee/mf/del_province', 'AjaxController@delProvince'); // Delete
// City/Municipalites
Route::match(['get', 'post'], 'employee/dashboard/ph/citymunicipality', 'DOHController@CityMunicipalities'); // Main, Add
Route::post('employee/mf/save_citymunicipality', 'AjaxController@saveCityMunicipality'); // Update
Route::post('employee/mf/del_citymunicipality', 'AjaxController@delcitymunicipality'); // Delete
// Barangay
Route::match(['get', 'post'], 'employee/dashboard/ph/barangay', 'DOHController@Barangay'); // Main, Add
Route::post('employee/mf/barangay/getBarangayFiltered', 'AjaxController@getBarangayFiltered'); // Get Barangays under City/Municipality
Route::get('employee/mf/barangay/getBarangayFiltered{cmid}', 'AjaxController@getBarangayFiltered1'); // Get Barangays under City/Municipality
Route::post('employee/mf/save_barangay', 'AjaxController@saveBarangay'); // Update
Route::post('employee/mf/del_barangay', 'AjaxController@delBarangay'); // Delete
// Order of Payment
Route::match(['get', 'post'], 'employee/dashboard/mf/orderofpayment', 'DOHController@OrderOfPayment'); // Main, Add 
Route::post('employee/mf/save_orderofpayment', 'AjaxController@saveOrderOfPayment'); // Update
Route::post('employee/mf/del_orderofpayment', 'AjaxController@delOrderOfPayment'); // Delete
// Payment Category
Route::match(['get', 'post'], 'employee/dashboard/mf/category', 'DOHController@Category'); // Main, Add
Route::post('employee/mf/save_category', 'AjaxController@saveCategory'); // Update
Route::post('employee/mf/del_category', 'AjaxController@delCategory'); // Delete
// Charges
Route::match(['get', 'post'], 'employee/dashboard/mf/charges', 'DOHController@Charges'); // Main, Add
Route::post('employee/mf/save_charge', 'AjaxController@saveCharge'); // Update Charge
Route::post('employee/mf/del_charge', 'AjaxController@delCharge'); // Delete Charge
// Manage Charges
Route::match(['get', 'post'], 'employee/dashboard/mf/manage/charges', 'DOHController@ManageCharges'); // Main, Add
Route::post('employee/mf/get_manageChargesOOPFiltered', 'AjaxController@ManageChargesOOPFiltered'); // Get All Manages Charges
Route::post('employee/mf/manage/charges/save_amount', 'AjaxController@saveManageChargesAmount'); // Update Charges' Amount
Route::post('employee/mf/del_manageCharges', 'AjaxController@delManagCharges'); // Delete Manage Charge
Route::post('employee/mf/manage/charges/rearrange', 'AjaxController@saveManageChargesRearrange'); // Rearrange a Charge
// Mode of Payment
Route::match(['get', 'post'], 'employee/dashboard/mf/modeofpayment', 'DOHController@ModeofPayment'); // Main, Add
Route::match(['get', 'post'], 'employee/dashboard/mf/uacs', 'DOHController@uacs'); // Main, Add
Route::post('employee/mf/save_modeofpayment', 'AjaxController@saveModeOfPayment'); // Update Mode of Payment
// Default Payment
Route::get('employee/mf/defaultpayment/del', 'AjaxController@delDefaultPayment'); // Delete
Route::get('employee/dashboard/mf/defaultpayment/get', 'AjaxController@getAllDefaultPayment'); // Get All Default Payment
Route::match(['get', 'post'], 'employee/dashboard/mf/defaultpayment', 'DOHController@DefaultPayment'); // Main, Add
// Service Charges
Route::match(['get', 'post'], 'employee/dashboard/mf/service_charges', 'DOHController@ServiceCharges'); // Main, Add
Route::get('employee/mf/assessment/get_ServiceCharges', 'AjaxController@getServiceCharges'); // Get
Route::post('employee/mf/assessment/del_ServiceCharges', 'AjaxController@delServiceCharges'); // Del
// Assessment Category
Route::match(['get', 'post'], 'employee/dashboard/mf/cat_assessment', 'DOHController@AssessmentCategory'); // Main, Add
Route::post('employee/mf/assessment/save_category', 'AjaxController@saveAssessmentCategory'); // Update 
Route::post('employee/mf/assessment/del_category', 'AjaxController@delAssessmentCategory'); // Delete
// Assessment Sub-Category A
Route::match(['get', 'post'], 'employee/dashboard/mf/csuba_assessment', 'DOHController@SubCategoryA'); // Main, Add
// Assessment Sub-Category B
Route::match(['get', 'post'], 'employee/dashboard/mf/csubb_assessment', 'DOHController@SubCategoryB'); // Main, Add
// Assessment Part
Route::match(['get', 'post'], 'employee/dashboard/mf/part', 'DOHController@AssessmentPart');  // Main, Add
Route::post('employee/mf/assessment/save_part', 'AjaxController@saveAssessmentPart'); // Update
Route::post('employee/mf/assessment/del_part', 'AjaxController@delAssessmentPart'); // Delete
// Assessment
Route::match(['get', 'post'], 'employee/dashboard/mf/assessment', 'DOHController@AssessmentMf');  // Main, Add
Route::get('employee/mf/assessment/getAllAssessment', 'AjaxController@getAllAssessment2');
Route::get('employee/mf/assessment/getfiltered_assessment', 'AjaxController@getFilteredAssessment'); // Filtered
Route::post('employee/mf/assessment/save_assessment', 'AjaxController@saveAssessment'); // Update
Route::post('employee/mf/assessment/del_assessment', 'AjaxController@delAssessment'); // Delete
// Assessment 2
Route::post('employee/mf/assessment/save_assessment2', 'AjaxController@saveAssessment2'); // Update
Route::post('employee/mf/assessment/del_assessment2', 'AjaxController@delAssessment2'); // Delete
Route::get('employee/mf/assessment/getSingleAssessment2', 'AjaxController@getSingleAssessment2'); // Get 
Route::post('employee/mf/manage/assessment/rearrange', 'AjaxController@rearrangeManageAssessment');
Route::match(['get', 'post'], 'employee/dashboard/mf/assessment2', 'DOHController@Assessment2MF');  // Main, Add
// Assessment 2 Sub-Description
Route::post('employee/mf/assessment/del_asmtSubDesc2', 'AjaxController@delAsmtSubDesc2'); // Del
Route::post('employee/mf/assessment/save_asmtSubDesc2', 'AjaxController@saveAsmtSubDesc2'); // Update
Route::match(['get', 'post'], 'employee/dashboard/mf/assessment/sub_description', 'DOHController@AssessmentSubDesc2MF'); // Main, Add
// Assessment 2 Column
Route::post('employee/mf/assessment/del_asmtcol', 'AjaxController@delAssessmentColumn'); // Del
Route::post('employee/mf/assessment/save_asmtcol', 'AjaxController@saveAssessmentColumn'); // Update
Route::match(['get', 'post'], 'employee/mf/assessment/column', 'DOHController@AssessmentColumn'); // Main, Add
// Manage Assessment
Route::post('employee/mf/manage/del_manageassessment', 'AjaxController@delManageAssessment'); // Delete
Route::match(['get', 'post'], 'employee/dashboard/mf/manage/assessment', 'DOHController@ManageAssessment'); // Main, Add
// Manage Assessment 2
Route::get('employee/mf/get_FaciOneType' , 'AjaxController@getFacilitiesOneAppType'); // Get All
Route::get('employee/mf/get_ServOneFaci' , 'AjaxController@getServicesOneFacility'); // Get All
Route::get('employee/mf/get_ServOneFaci2' , 'AjaxController@getServicesOneFacility2');
Route::get('employee/mf/get_SingleAsmt' , 'AjaxController@getSingleAsmt2'); // Get All
Route::get('employee/mf/get_allAsmt', 'AjaxController@getAllAsmt2');// Get All Assessment
Route::get('employee/mf/get_AsmtOneFaci', 'AjaxController@getAssessmentOneFacility'); // Get Assessments 
Route::post('employee/mf/del_ManageAssesment2', 'AjaxController@delMngAsmt2'); // Delete 
Route::match(['get', 'post'], 'employee/dashboard/mf/manage/preview_unmanaged_assessment', 'DOHController@previewAssessmentUnmanage'); // Main, Add
Route::match(['get', 'post'], 'employee/dashboard/mf/manage/assessment2', 'DOHController@ManageAssessment2'); // Main, Add
Route::match(['get', 'post'], 'employee/dashboard/mf/preview_unmanaged_assessment', 'DOHController@previewAssessmentUnmanage'); // Main, Add

Route::match(['get', 'post'], 'employee/dashboard/mf/manage/preview_assessment', 'DOHController@previewAssessment'); // Main, Add
Route::match(['get', 'post'], 'employee/dashboard/mf/manage/preview_assessment/{titlecode}', 'DOHController@previewAssessmentwithTitle'); // Main, Add
// Complaints
Route::get('employee/mf/del_complaint', 'AjaxController@delComplaint'); // Delete
Route::get('employee/mf/save_complaint', 'AjaxController@saveComplaint'); // Update
Route::match(['get', 'post'], 'employee/dashboard/mf/complaints', 'DOHController@Complaints'); // Main, Add
// Request for Assistance
Route::get('employee/mf/del_requestforassistance', 'AjaxController@delRequestForAssistance'); // Delete 
Route::get('employee/mf/save_requestforassistance', 'AjaxController@saveRequestForAssistance'); // Update
Route::match(['get', 'post'], 'employee/dashboard/mf/requestforassistance', 'DOHController@RequestForAssistance'); // Main, Add
// System Settings
Route::match(['get', 'post'], 'employee/dashboard/mf/settings', 'DOHController@SystemSetting'); // Main, Add
///// PROCESS FLOW ---------------------------------------------------------------------------
// View All Application
Route::match(['get', 'post'], 'employee/dashboard/processflow/view/{filter?}', 'DOHController@ViewProcessFlow'); // View All
Route::match(['get', 'post'], 'employee/hhrdb/applist', 'DOHController@listofpersonnel'); // View All
// Evaluate
Route::match(['get', 'post'],  'employee/dashboard/mf/FDA/pharma_charges', 'DOHController@fdapharma'); // Main, Add
Route::match(['get', 'post'],  'employee/dashboard/processflow/evaluate', 'DOHController@EvaluateProcessFlow'); // View All

Route::match(['get', 'post'],  'employee/dashboard/processflow/pre-assessment/FDA/{request?}', 'DOHController@pre_assessmentFDA'); // View All // FDA


Route::match(['get', 'post'],  'employee/dashboard/processflow/evaluate/FDA/{request?}', 'DOHController@EvaluateProcessFlowFDA'); // View All // FDA
Route::match(['get', 'post'], 'employee/dashboard/processflow/evaluate/{appid}/{requestforfda?}', 'DOHController@EvaluateOneProcessFlow'); // View One
Route::match(['get', 'post'], 'employee/dashboard/processflow/LTO/evaluate/', 'DOHController@evaluateLTOReq'); // View One
Route::match(['get', 'post'], 'employee/dashboard/processflow/evaluate/{appid}/edit', 'DOHController@EditEvaluationOneProcessFlow'); // Edit One
Route::match(['get', 'post'], 'employee/dashboard/processflow/evaluate/FDA/{appid}/{request}', 'DOHController@EvaluateOneProcessFlowFDA'); // Edit One
Route::post('employee/dashboard/processflow/getSingleDownloadDetails', 'AjaxController@getSingleDownloadDetails'); // Get Evaluate Details
Route::post('employee/dashboard/processflow/judgeApplication', 'AjaxController@JudgeApplication'); // Accept or Reject Application
Route::post('employee/dashboard/processflow/judgeApplicationFDA', 'AjaxController@JudgeApplicationFDA'); // Accept or Reject Application (FDA)
// Order of Payment
Route::match(['get', 'post'], 'employee/dashboard/processflow/orderofpayment', 'DOHController@OrderOfPaymentProcessFlow'); // View All
Route::post('employee/dashboard/processflow/orderofpayment/deleteCharge', 'AjaxController@DeleteChargeOrderofPayment'); // Delete Selected Charge
Route::post('employee/dashboard/processflow/orderofpayment/accept_orderofpayment', 'AjaxController@AcceptOrderOfPaymentProcessFlow'); // Accept Order of Payment
Route::post('employee/dashboard/processflow/orderofpayment/FDA/accept_orderofpayment/Machines', 'AjaxController@AcceptOrderOfPaymentProcessFlowFDAMachines'); // Accept Order of Payment
Route::post('employee/dashboard/processflow/orderofpayment/FDA/accept_orderofpayment/Pharma', 'AjaxController@AcceptOrderOfPaymentProcessFlowFDAPharma'); // Accept Order of Payment
Route::match(['get', 'post'], 'employee/dashboard/processflow/orderofpayment/{appid}', 'DOHController@OrderOfPaymentOneProcessFlow'); // view, Add
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/{request}/orderofpayment', 'DOHController@OrderOfPaymentProcessFlowFDA'); // fda
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/machines/orderofpayment/{appid}', 'DOHController@OrderOfPaymentOneProcessFlowMachinesFDA'); // view, Add
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/pharma/orderofpayment/{appid}', 'DOHController@OrderOfPaymentOneProcessFlowPharmaFDA'); // view, Add
Route::post('employee/dashboard/processflow/orderofpayment/deleteCharge/FDA', 'AjaxController@DeleteChargeOrderofPaymentFDA'); // Delete Selected Charge
// Cashier
Route::match(['get', 'post'], 'employee/dashboard/processflow/cashier', 'DOHController@CashierProcessFlow'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/cashier/{appid}/{facid}', 'DOHController@CashierOneProcessFlow'); // view, Add
// CashierFDA
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/cashier', 'DOHController@CashierProcessFlowFDA'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/cashier/{appid}/{facid}', 'DOHController@CashierOneProcessFlowFDA'); // view, AddFDA

Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/pharma/cashier', 'DOHController@CashierProcessFlowPharmaFDA'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/pharma/cashier/{appid}/{facid}', 'DOHController@CashierOneProcessFlowFDApharma'); // view, AddFDA


// inspection schedule
Route::match(['get', 'post'], 'employee/dashboard/processflow/inspection/{appid?}', 'DOHController@inspection'); //inspection schedule
// Assignment of Team
Route::match(['get', 'post'], 'employee/dashboard/processflow/assignmentofteam', 'DOHController@AssignmentofTeamProcessFlow'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/assignmentofhferc', 'DOHController@hfercTeam'); // hferc assignment
Route::match(['get', 'post'], 'employee/dashboard/processflow/assignmentofhferc/{appid}/{revision?}', 'DOHController@hfercTeamAssignment'); // hferc assignment
Route::match(['get', 'post'], 'employee/dashboard/processflow/assignmentofcommittee', 'DOHController@committeTeam'); //committee
Route::match(['get', 'post'], 'employee/dashboard/processflow/assignmentofcommittee/{appid}', 'DOHController@committeTeamAssignment');
// committee assignment
Route::post('employee/dashboard/processflow/get_teams', 'AjaxController@getTeamInRegion'); // Get Team
Route::post('employee/dashboard/processflow/get_members', 'AjaxController@getMembersInRegion'); // Get Members
Route::post('employee/dashboard/processflow/get_memWithoutTeam', 'AjaxController@getMembersInRegionWithoutTeam'); // Get Employee Without Team
Route::post('employee/dashboard/processflow/get_assignedmembers', 'AjaxController@getAssignedMembersInTeam'); // Get Assigned Members
Route::post('employee/dashboard/processflow/del_assignedmember', 'AjaxController@delAssignedMemberInTeam'); // Delete Assigned Members
Route::post('employee/dashboard/processflow/save_assignedmembers', 'AjaxController@saveAssignedMembersInTeam'); // Save Assigned Members
// Assessment 
Route::match(['get', 'post'], 'employee/dashboard/processflow/evaluation/{isMobile?}', 'DOHController@EvaluationProcessFlow'); // evaluation tool
Route::match(['get', 'post'], 'employee/dashboard/processflow/conevaluation/{session?}', 'DOHController@EvaluationProcessFlowCON'); // evaluation tool
Route::match(['get', 'post'], 'employee/dashboard/processflow/evalution/{user}/{appid}/{apptype}/', 'DOHController@EvaluationShowEach'); // View each
Route::match(['get', 'post'], 'employee/dashboard/processflow/evalution/each/{user}/{appid}/{apptype}/{choosen}', 'DOHController@EvaluationOneProcessFlow'); //each evaluation
Route::match(['get', 'post'], 'employee/dashboard/processflow/evaluation/view/{user}/{appid}/{apptype}', 'DOHController@EvaluationOneViewProcessFlow'); // save evaluation
Route::match(['get', 'post'], 'employee/dashboard/processflow/evaluation/compiled/{user}/{appid}/{apptype}', 'DOHController@EvaluationDisplay'); // view evaluation
Route::match(['get', 'post'], 'employee/dashboard/processflow/conevalution/{appid}', 'DOHController@coneval'); 
Route::match(['get', 'post'], 'employee/dashboard/processflow/view/conevalution/{appid}', 'DOHController@conEvalView'); // evaluation tool



/*
new evaluation tool
*/
Route::match(['get', 'post'], 'employee/dashboard/processflow/floorPlan/revisionLastCount/{appid}', 'AjaxController@mobileMaxRevision');
Route::match(['get', 'post'], 'employee/dashboard/processflow/floorPlan/parts/{appid}/{revision}/{session?}', 'EvaluationController@FPShowPart'); // evaluation tool
Route::match(['get', 'post'], 'employee/dashboard/processflow/floorPlan/HeaderOne/{appid}/{part}/{otherUID?}', 'EvaluationController@FPShowH1'); // level 1
Route::match(['get', 'post'], 'employee/dashboard/processflow/floorPlan/HeaderTwo/{appid}/{headerOne}/{otherUID?}', 'EvaluationController@FPShowH2'); // level 2
Route::match(['get', 'post'], 'employee/dashboard/processflow/floorPlan/HeaderThree/{appid}/{headerTwo}/{otherUID?}', 'EvaluationController@FPShowH3'); // level 3
Route::match(['get', 'post'], 'employee/dashboard/processflow/floorPlan/ShowAssessments/{appid}/{revision}/{headerThree}/{uid?}', 'EvaluationController@FPShowAssessments'); // View assessment
Route::post('employee/dashboard/processflow/floorPlan/ShowAssessmentsMobile/{appid}/{revision}/{headerThree}/{uid?}', 'EvaluationController@FPShowAssessmentsMobile'); // View assessment
Route::match(['get', 'post'], 'employee/dashboard/processflow/floorPlan/SaveAssessments/{revision}/{otherUID?}', 'EvaluationController@FPSaveAssessments'); // save evaluation
Route::match(['get', 'post'], 'employee/dashboard/processflow/floorPlan/GenerateReportAssessments/{appid}/{revision}/{uid}', 'EvaluationController@FPGenerateReportAssessment'); // generate evaluation
/*
end of new evaluation tool
*/


////// new assessment
Route::match(['get', 'post'], 'employee/dashboard/processflow/forAssessements', 'DOHController@AssessmentParts'); // assessment
Route::match(['get', 'post'], 'employee/dashboard/processflow/parts/{appid}/{montype?}', 'DOHController@AssessmentShowPart'); // View each
Route::match(['get', 'post'], 'employee/dashboard/processflow/HeaderOne/{appid}/{part}/{montype?}', 'DOHController@AssessmentShowH1'); // View each
Route::match(['get', 'post'], 'employee/dashboard/processflow/HeaderTwo/{appid}/{headerOne}/{montype?}', 'DOHController@AssessmentShowH2'); // View each
Route::match(['get', 'post'], 'employee/dashboard/processflow/HeaderThree/{appid}/{headerTwo}/{montype?}', 'DOHController@AssessmentShowH3'); // View each
Route::match(['get', 'post'], 'employee/dashboard/processflow/ShowAssessments/{appid}/{headerThree}/{montype?}', 'DOHController@ShowAssessments'); // View each

//accept assessment
Route::match(['get', 'post'], 'employee/dashboard/processflow/SaveAssessments/', 'DOHController@SaveAssessments'); 
Route::post('employee/dashboard/processflow/SaveInspection/Mobile', 'DOHController@SaveInspectionMobile');// View each
Route::match(['get', 'post'], 'employee/dashboard/processflow/SaveAssessmentsMobile/{hash?}', 'DOHController@SaveAssessmentsMobile'); // View each

//generate report on assessment
Route::match(['get', 'post'], 'employee/dashboard/processflow/GenerateReportAssessments/{appid}/{monid?}', 'DOHController@GenerateReportAssessment'); // View each

Route::match(['get', 'post'], 'employee/dashboard/processflow/fixAssessmentOrder', 'AssessmentController@assessmentOrder'); // View each
Route::match(['get', 'post'], 'employee/dashboard/processflow/registerAssess', 'AssessmentController@assessmentRegister'); // View each

///// new assessment end



Route::match(['get', 'post'], 'employee/dashboard/processflow/assessment/{session?}', 'DOHController@AssessmentProcessFlow'); // assessment
//////////////////syrel remade
Route::match(['get', 'post'], 'employee/dashboard/processflow/assessment/view/{appid}/{apptype}/{montype?}', 'DOHController@AssessmentOneViewProcessFlow'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/assessment/compiled/{appid}/{apptype}/{montype?}/{isTest?}', 'DOHController@AssessmentDisplay'); // assessment unpassed
Route::match(['get', 'post'], 'employee/dashboard/processflow/assessment/{appid}/{apptype}/{montype?}', 'DOHController@AssessmentShowEach'); // View each
Route::match(['get', 'post'], 'employee/dashboard/processflow/assessment/each/{appid}/{apptype}/{choosen}/{montype?}', 'DOHController@AssessmentOneProcessFlow'); // assessment unpassed
Route::match(['get', 'post'], 'employee/mf/assessment/dupAssessment2', 'AjaxController@dupAssessment2'); // duplicate data
// Recommendation
Route::match(['get', 'post'], 'employee/dashboard/processflow/recommendation', 'DOHController@RecommendationProcessFlow'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/recommendation/{appid}', 'DOHController@RecommendationOneProcessFlow'); // View One, Add
// Approval
Route::match(['get', 'post'], 'employee/dashboard/processflow/approval', 'DOHController@ApprovalProcessFlow'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/approval/{appid}', 'DOHController@ApprovalOneProcessFlow'); // View All, Add



Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/approval/{request?}', 'DOHController@ApprovalProcessFlowFDA'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/approval/{appid}/{type?}', 'DOHController@ApprovalOneProcessFlowFDA'); // View All, Add

Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/recommendation/{request?}', 'DOHController@RecommendationProcessFlowFDA'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/recommendation/{appid}/{type?}', 'DOHController@RecommendationOneProcessFlowFDA'); // View All, Add

// FDA Monitoring
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/monitoring/{request?}', 'DOHController@MonitoringList'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/monitoring/{type?}/{appid}/', 'DOHController@MonitoringProcess'); // View All, Add



// Failed 
Route::match(['get', 'post'], 'employee/dashboard/processflow/failed', 'DOHController@FailedProcessFlow'); // View All
Route::match(['get', 'post'], 'employee/dashboard/processflow/failed/{appid}', 'DOHController@FailedOneProcessFlow'); // View All, Add
///// OTHERS ---------------------------------------------------------------------------
//// Monitoring
Route::post('employee/dashboard/others/monitoring/mobile/inspection/{true}', 'DOHController@MonitoringInspectionOthersMobile'); // View All, Add
// Monitoring Identification
Route::match(['get', 'post'], 'employee/dashboard/others/monitoring', 'DOHController@MonitoringOthers'); // View All, Add
// Monitoring Assignment of Teams
Route::match(['get', 'post'], 'employee/dashboard/others/monitoring/teams', 'DOHController@MonitoringTeamsOthers'); // View All, Add
// Monitoring Inspection
Route::match(['get', 'post'], 'employee/dashboard/others/monitoring/inspection', 'DOHController@MonitoringInspectionOthers'); // View All, Add
// Monitoring Inspection
Route::match(['get', 'post'], 'employee/dashboard/others/monitoring/technical/{id?}', 'DOHController@MonitoringTechnicalOthers'); // View All, Add
// Monitoring Recommendation
Route::match(['get', 'post'], 'employee/dashboard/others/monitoring/recommendation', 'DOHController@MonitoringRecommendationOthers'); // View All, Add
// Monitoring Evaluation
Route::match(['get', 'post'], 'employee/dashboard/others/monitoring/evaluation', 'DOHController@MonitoringEvaluationOthers'); // View All, Add
// Monitoring Evaluation
Route::match(['get', 'post'], 'employee/dashboard/others/monitoring/updatestatus', 'DOHController@MonitoringUpdateStatusOthers'); // View All, Add
// Surveillance Identification
Route::post('employee/dashboard/others/action/{action}', 'AjaxController@actionsForROA');
Route::match(['get', 'post'], 'employee/dashboard/others/surveillance', 'DOHController@SurveillanceOthers'); // View All, Add
Route::match(['get', 'post'], 'employee/dashboard/others/surveillance/getSurvAct', 'AjaxController@getSurvAct'); // View All, Add
Route::match(['get', 'post'], 'employee/dashboard/others/surveillance/getMonAct', 'AjaxController@getMonAct'); // View All, Add
Route::match(['get', 'post'], 'employee/dashboard/others/surveillance/getComplaint', 'AjaxController@getComplaint'); // View All, Add
Route::match(['get', 'post'], 'employee/dashboard/others/surveillance/survact', 'DOHController@SurveillanceActivity'); // View All, Add
Route::match(['get', 'post'], 'employee/dashboard/others/surveillance/clientActionTaken/{idopt?}', 'DOHController@clientActionTaken'); // View All, Add
// Surveillance Assignment of Teams
Route::match(['get', 'post'], 'employee/dashboard/others/surveillance/teams', 'DOHController@SurveillanceTeamsOthers'); // View All, Add
// Surveillance Inspection
Route::match(['get', 'post'], 'employee/dashboard/others/surveillance/inspection', 'DOHController@SurveillanceInspectionOthers'); // View All, Add
// Surveillance Recommendation
Route::match(['get', 'post'], 'employee/dashboard/others/surveillance/recommendation', 'DOHController@SurveillanceRecommendationOthers'); // View All, Add
// Request for Assistance
Route::match(['get', 'post'], 'employee/dashboard/others/roacomplaints', 'DOHController@RequestAssistanceOthers')->name('others.roacomp'); // View All, Add
//ajax
Route::post('employee/dashboard/others/restoreROAC', 'OthersController@restoreROAC');
Route::get('employee/dashboard/others/getHistoryLogs', 'AjaxController@getHistoryLogs');
Route::get('employee/dashboard/others/getAppTeamByAppId', 'AjaxController@getAppTeamByAppId'); // Get Teams
Route::get('employee/dashboard/others/getFacNameByFacid{facid}/{brgyid?}', 'AjaxController@getFacNameByFacid'); // Get FacName
Route::get('employee/dashboard/others/getFacNameNotApprovedByFacid{facid}', 'AjaxController@getFacNameNotApprovedByFacid'); // Get FacName
Route::get('employee/dashboard/others/getFacTypeByAppId{appid}', 'AjaxController@getFacTypeByAppId'); // Get FacType
Route::get('employee/dashboard/others/getAllFacAddr', 'AjaxController@getAllFacAddr'); // Get FacAddr
Route::get('employee/dashboard/monitor/getRemarksByAstSurveillance{ast}/{survid}', 'AjaxController@getRemarksByAstSurveillance'); // Get Remarks
Route::get('employee/dashboard/monitor/getRemarks{ast}/{monid}', 'AjaxController@getRemarksByAst'); // Get Remarks
Route::get('employee/dashboard/monitor/getRemarksDesc{ast}', 'AjaxController@getSubDescByAst'); // Get Remarks
Route::get('employee/dashboard/monitor/getRecommendation{id}', 'AjaxController@getRecommendation'); // Get Recommendation
Route::get('employee/dashboard/monitor/getMembersByTeamId{id}', 'AjaxController@getMembersByTeamId'); // Get Team Members
Route::get('employee/dashboard/monitor/getTeamMembers{id}', 'AjaxController@getTeamMembers'); // Show Team Members
Route::get('employee/dashboard/monitor/getEmployeeFullNameByUid{uid}', 'AjaxController@getEmployeeFullNameByUid'); // Get Employee Full Name
Route::get('employee/dashboard/monitor/getAllNovDirections', 'AjaxController@getAllNovDirections'); // Get Nov Directories
Route::get('employee/dashboard/monitor/getNovDirection{id}', 'AjaxController@getNovDirection'); // Get Nov Directories By Id
Route::get('employee/dashboard/monitor/getVerdict{vid}', 'AjaxController@getVerdict'); // Get Verdict
Route::get('employee/dashboard/monitor/team/getMonTeamByRegionJSON{rgnid}', 'AjaxController@getMonTeamByRegionJSON'); // Get MonTeam
Route::get('employee/dashboard/monitor/team/getSurvTeamByRegionJSON{rgnid}', 'AjaxController@getSurvTeamByRegionJSON'); // Get SurvTeam
Route::get('employee/dashboard/monitor/team/getMembersByNewTeamId{montid}', 'AjaxController@getMembersByNewTeamId'); // Get TeamMembers
Route::get('employee/dashboard/monitor/team/surveillanceTeamFetch/{montid}', 'AjaxController@getMembersByNewTeamIdSurv'); // Get TeamMembers
Route::get('employee/dashboard/monitor/team/remarks{montmemberid}', 'AjaxController@getTeamMemberRemarks'); // Get TeamMemberRemarks

Route::get('employee/dashboard/surviellance/team/remarks{montmemberid}', 'AjaxController@getTeamMemberRemarksSurv'); // Get TeamMemberRemarks

Route::get('employee/dashboard/others/surveillance/unreg/{region}', 'AjaxController@getProvincesByRegionUnreg'); // Get Verdict
Route::get('employee/dashboard/others/surveillance/unprov/{province}', 'AjaxController@getProvincesByProvinceUnreg'); // Get Verdict
Route::get('employee/dashboard/others/surveillance/uncm/{province}', 'AjaxController@getProvincesByCMUnreg'); // Get Verdict
Route::get('employee/dashboard/others/entry', 'OthersController@__entry'); // Get Verdict

Route::post('employee/dashboard/monitor/violations/get', 'AjaxController@getViolationsNew'); // Get TeamMemberRemarks
///// MANAGE ---------------------------------------------------------------------------
// Group Rights
Route::post('employee/dashboard/manage/saverightsonsinglegroup', 'AjaxController@saveRightsOnSingleGroup'); // Save Rights
Route::post('employee/dashboard/manage/getrightsonsinglegroup', 'AjaxController@getRightsOnSingleGroup'); // Get Single Rights
Route::match(['get', 'post'], 'employee/dashboard/manage/grouprights', 'DOHController@GroupRightsManage'); // View All, Add
// Groups
Route::post('employee/dashboard/manage/savegroup', 'AjaxController@saveGroupMange'); // Update
Route::post('employee/dashboard/manage/delgroup', 'AjaxController@delGroupMange'); // Delete
Route::match(['get', 'post'], 'employee/dashboard/manage/groups', 'DOHController@GroupManage'); // View All, Add
// Modules
Route::get('employee/dashboard/manage/getlvl3module', 'AjaxController@getModuleLevel3'); // Get Level 3 Module
Route::get('employee/dashboard/manage/getlvl2module', 'AjaxController@getModuleLevel2'); // Get Level 2 Module
Route::get('employee/dashboard/manage/getlvl1module', 'AjaxController@getModuleLevel1'); // Get Level 1 Module
Route::post('employee/dashboard/manage/savemodule', 'AjaxController@saveModuleMange'); // Update
Route::post('employee/dashboard/manage/delmodule', 'AjaxController@delModuleMange'); // Delete
Route::match(['get', 'post'], 'employee/dashboard/manage/modules', 'DOHController@ModuleManage'); // View All, Add
// System Users
Route::post('employee/dashboard/manage/saveUser', 'AjaxController@SaveUserManage'); // Update All information
Route::post('employee/dashboard/manage/saveIfActive', 'AjaxController@SaveIFActive'); // Update Change State
Route::match(['get', 'post'], 'employee/dashboard/manage/system_users', 'DOHController@SystemUsersManage'); // View All
// Applicant Accounts
Route::match(['get', 'post'], 'employee/dashboard/manage/applicants', 'DOHController@ApplicantAccountsManage');
// System Logs
Route::match(['get', 'post'], 'employee/dashboard/manage/system_logs', 'DOHController@SystemLogsManage');
///////////////////// EMPLOYEE 
////////////////// LLOYD /////////////////////////
////////////// ASSESSMENT TOOL ////////////////////
// __index
// main
// Route::match(['get', 'post'], 'employee/dashboard/assessment/', 'AssessmentController@__index'); // removed Nov 26, 2018
// hospital level 1
Route::match(['get', 'post'], 'employee/dashboard/assessment/hospitallevel1', 'AssessmentController@hospital1');
// nursing service
Route::match(['get', 'post'], 'employee/dashboard/assessment/nursingservice', 'AssessmentController@nursingservice');
// physical plant
Route::match(['get', 'post'], 'employee/dashboard/assessment/physicalplant', 'AssessmentController@physicalplant');
// dialysis clinic
Route::match(['get', 'post'], 'employee/dashboard/assessment/dialysisclinic', 'AssessmentController@dialysisclinic');
// ambulatory surgical clinic
Route::match(['get', 'post'], 'employee/dashboard/assessment/ambsurclinic', 'AssessmentController@ambsurclinic');
////////// OTHERS - REQ FOR ASSISTANCE //////////////
//// Request for assistance
// Add
Route::match(['get', 'post'], 'employee/dashboard/others/req_submit/{id}', 'OthersController@req_submit');
Route::match(['get', 'post'], 'employee/dashboard/others/comp_submit/{id}', 'OthersController@comp_submit');
// Manage
Route::match(['get', 'post'], 'employee/dashboard/others/roacomplaints/manage', 'OthersController@roacomplaints_manage');
//// Surveillance
// Add
Route::match(['get', 'post'], 'employee/dashboard/others/surv_submit', 'OthersController@surv_submit');
// Add Unreg
Route::match(['get', 'post'], 'employee/dashboard/others/surv_submit_u', 'OthersController@surv_submit_u');
//Team
Route::match(['get', 'post'], 'employee/dashboard/others/surv_team', 'OthersController@surv_team');
// Edit
Route::match(['get', 'post'], 'employee/dashboard/others/surv_edit', 'OthersController@surv_edit');
// Delete
Route::match(['get', 'post'], 'employee/dashboard/others/surv_delete', 'OthersController@surv_delete');
// Issue NOV
Route::match(['get', 'post'], 'employee/dashboard/others/surv_nov', 'OthersController@surv_nov');
// Recommendation
Route::match(['get', 'post'], 'employee/dashboard/others/surv_recommendation', 'OthersController@surv_recommendation');
//// Monitoring
//Add
Route::match(['get', 'post'], 'employee/dashboard/others/mon_submit', 'OthersController@mon_submit');
//Team
Route::match(['get', 'post'], 'employee/dashboard/others/mon_team', 'OthersController@mon_team');
// Edit
Route::match(['get', 'post'], 'employee/dashboard/others/mon_edit', 'OthersController@mon_edit');
// Delete
Route::match(['get', 'post'], 'employee/dashboard/others/mon_delete', 'OthersController@mon_delete');
// Update
Route::match(['get', 'post'], 'employee/dashboard/others/monitoring/updatestatus/mon_update', 'OthersController@mon_update');
// Issue NOV
Route::match(['get', 'post'], 'employee/dashboard/others/mon_nov', 'OthersController@mon_nov');
// Update NOV
Route::match(['get', 'post'], 'employee/dashboard/others/mon_nov_u', 'OthersController@mon_nov_u');
// Recommendation
Route::match(['get', 'post'], 'employee/dashboard/others/mon_recommendation', 'OthersController@mon_recommendation');
// Update Recommendation
Route::match(['get', 'post'], 'employee/dashboard/others/mon_recommendation_u', 'OthersController@mon_recommendation_u');
// Add Attachment
Route::match(['get', 'post'], 'employee/dashboard/others/mon_attachment', 'OthersController@mon_attachment');
// Add Team
Route::match(['get', 'post'], 'employee/dashboard/others/mon_addmonteam', 'OthersController@mon_addmonteam');
Route::match(['get', 'post'], 'employee/dashboard/others/surv_addmonteam', 'OthersController@surv_addmonteam');
// Remove Team
Route::match(['get', 'post'], 'employee/dashboard/others/mon_removemonteam', 'OthersController@mon_removemonteam');
Route::match(['get', 'post'], 'employee/dashboard/others/surv_removemonteam', 'OthersController@surv_removemonteam');
// Add Member
Route::match(['get', 'post'], 'employee/dashboard/others/mon_addmonteammember', 'OthersController@mon_addmonteammember');
Route::match(['get', 'post'], 'employee/dashboard/others/surv_addmonteammember', 'OthersController@surv_addmonteammember');
// Remove Member
Route::match(['get', 'post'], 'employee/dashboard/others/mon_remmonteammember', 'OthersController@mon_remmonteammember');

Route::match(['get', 'post'], 'employee/dashboard/others/surv_remmonteammember', 'OthersController@surv_remmonteammember');
//// NOV
Route::match(['get', 'post'], 'employee/dashboard/others/novs/{survid}', 'DOHController@__novs');
Route::match(['get', 'post'], 'employee/dashboard/others/novm/{monid}', 'DOHController@__novm');
Route::match(['get', 'post'], 'employee/dashboard/others/nov_submit', 'DOHController@__nov_submit'); // submit
//// RAOIN
Route::match(['get', 'post'], 'employee/dashboard/others/raoins/{survid}', 'DOHController@__raoins');
Route::match(['get', 'post'], 'employee/dashboard/others/raoin/{monid}', 'DOHController@__raoin');
////////////////// SyrelXxXxX /////////////////////////
Route::match(['get', 'post'], '/employee/dashboard/assessment/hospitallevel2', 'AssessmentController@hospital2');
Route::match(['get', 'post'], '/employee/dashboard/assessment/hospitallevel3', 'AssessmentController@hospital3');
Route::match(['get', 'post'], '/employee/dashboard/assessment/practiceMat', 'DOHController@activityLogs');
///////////////////////////////////////////////////////cashier
Route::match(['get', 'post'], 'employee/dashboard/processflow/actions/{appid}/{aptid?}', 'DOHController@cashierActions');
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/actions/{appid}/{aptid?}', 'DOHController@cashierActionsFDA');
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/pharma/actions/{appid}/{aptid?}', 'DOHController@cashierActionsPharmaFDA');
Route::match(['get', 'post'], 'employee/dashboard/processflow/printor/{appid}', 'DOHController@printOR');
Route::match(['get', 'post'], 'employee/dashboard/processflow/view/hfercevaluation/{appid}/{revisionCount}', 'DOHController@viewhfercresult');
Route::match(['get', 'post'], 'employee/dashboard/processflow/FDA/printor/{appid}', 'DOHController@printORFDA');
Route::match(['get', 'post'], 'employee/cashier/actions', 'DOHController@chAction');
Route::match(['get', 'post'], 'employee/cashier/FDA/actions', 'DOHController@chActionFDA');
Route::match(['get', 'post'], 'employee/dashboard/cashier', 'DOHController@savePayment');
Route::match(['get', 'post'], 'employee/dashboard/FDA/cashier', 'DOHController@savePaymentFDA');
// try
Route::match(['get', 'post'], '/employee/upload', 'AssessmentController@preview');
Route::match(['get', 'post'], '/employee/assessment/', 'AssessmentController@samples');
// FDA range
Route::match(['get', 'post'], '/employee/mf/FDA/ranges', 'DOHController@fdaRanges');
// fda xray machine
Route::match(['get', 'post'], '/employee/mf/FDA/xraytype', 'DOHController@xraymachine');
// fda xray location
Route::match(['get', 'post'], '/employee/mf/FDA/xrayloc', 'DOHController@xraylocation');
// fda xray category
Route::match(['get', 'post'], '/employee/mf/FDA/xraycat', 'DOHController@xraycat');
// fda xray services
Route::match(['get', 'post'], '/employee/mf/FDA/machines/requirements', 'DOHController@xrayReq');
// fda machine other requirements
Route::match(['get', 'post'], '/employee/mf/FDA/xrayserv', 'DOHController@xrayserv');
// Assessment title
Route::match(['get', 'post'], '/employee/mf/assessmentpart', 'AssessmentController@title');
// Assessment Headers
Route::match(['get', 'post'], 'employee/mf/headerOne', 'AssessmentController@headerOne');
// Assessment Headers
Route::match(['get', 'post'], 'employee/mf/headerTwo', 'AssessmentController@headerTwo');
// Assessment Headers
Route::match(['get', 'post'], 'employee/mf/headerThree', 'AssessmentController@headerThree');
// Assessment Headers
Route::match(['get', 'post'], 'employee/mf/AssessmentCombine/{limit?}', 'AssessmentController@assessmentCombine');
// Assessment Headers
Route::match(['get', 'post'], '/employee/mf/assessmentheader', 'AssessmentController@getHeaders');
// level 1 header
Route::match(['get', 'post'], '/employee/dashboard/manage/getlvlHeader', 'AssessmentController@getHeadersLevel');
// load Header Filterf
Route::match(['get', 'post'], '/employee/dashboard/manage/getlvlFilter', 'AssessmentController@getlvlFilter');
Route::match(['get', 'post'], '/employee/dashboard/manage/getlvlFilterFromLevel', 'AssessmentController@getlvlFilterFromLevel');
Route::match(['get', 'post'], '/employee/dashboard/manage/getlvlResults', 'AssessmentController@getlvlResults');
Route::match(['get', 'post'], '/employee/dashboard/manage/saveHeader', 'AssessmentController@saveHeader');//edit
Route::match(['get', 'post'], '/employee/dashboard/assessment/dentallaboratory', 'AssessmentController@dentallaboratory');
Route::match(['get', 'post'],'/employee/dashboard/assessment/document','AjaxController@print'); // print
Route::match(['get', 'post'],'/employee/dashboard/activityLogs','AjaxController@activityLog'); // print
//////////////////// BERKZ /////////////////////////
//drugtestinglab
Route::match(['get', 'post'], '/employee/dashboard/assessment/drugTestingLab', 'AssessmentController@drugTestingLab');
//birthinghome
Route::match(['get', 'post'], '/employee/dashboard/assessment/birthingHome', 'AssessmentController@birthingHome');
//acuteChronicCare
Route::match(['get', 'post'], '/employee/dashboard/assessment/acuteChronicCare', 'AssessmentController@acuteChronicCare');
//ambulatorysurgicalclinic oral and maxillo-facial surgery
Route::match(['get', 'post'], '/employee/dashboard/assessment/ambSurgOmfs', 'AssessmentController@ambSurgOmfs');
//general clinic laboratory
Route::match(['get', 'post'], '/employee/dashboard/assessment/gencliniclab', 'AssessmentController@genClinicLab');
//custodial psychiatric care facility
Route::match(['get', 'post'], '/employee/dashboard/assessment/custodialPsychiatric', 'AssessmentController@custodialPsychiatric');
//land ambulance
Route::match(['get', 'post'], '/employee/dashboard/assessment/landAmbulance', 'AssessmentController@landAmbulance');
//laboratory for drinking water analysis
Route::match(['get', 'post'], '/employee/dashboard/assessment/drinkingWater', 'AssessmentController@drinkingWater');

//////////////////// BERKZ /////////////////////////
/////////////////// Lloyk client //////////////////
Route::get('client1/apply/app/LTO/48/hfsrb/getAllProfessions', 'AjaxController@getAllProfessions'); // Get 
Route::get('client1/apply/app/LTO/48/hfsrb/getAllEmploymentStatus', 'AjaxController@getAllEmploymentStatus'); // Get
Route::get('client1/apply/app/LTO/48/hfsrb/getAllAnnexA', 'AjaxController@getAllAnnexA'); // Get 
Route::get('client1/apply/app/LTO/48/hfsrb/getAnnexAById{id}', 'AjaxController@getAnnexAById'); // Get 
Route::match(['get', 'post'], 'client1/apply/app/LTO/48/hfsrb/annexAAdd{fname}/{mname}/{lname}/{sex}/{dob}/{prof}/{spec}/{prcno}/{status}/{appid}', 'LloydClientController@annexAAdd'); // Submit 
Route::match(['get', 'post'], 'client1/apply/app/LTO/48/hfsrb/annexAEdit', 'LloydClientController@annexAEdit'); // Edit 
Route::match(['get', 'post'], 'client1/apply/app/LTO/48/hfsrb/annexADelete', 'LloydClientController@annexADelete'); // Edit 
Route::get('employee/dashboard/others/submitRequest', 'AjaxController@submitRequest'); // Get Verdict

Route::prefix('employee/reports')->group(function() {
	Route::prefix('masterfile')->group(function() {
		Route::get('assessment/', 'ReportsController@assessment');
		Route::get('assessment/{type}/{apptype}', 'ReportsController@assessmentReportEach');
	});
	Route::prefix('license')->group(function() {
		Route::prefix('PTC')->group(function() {
			Route::get('inspection/', 'ReportsController@inspectionPTC');
		});
		Route::prefix('Recommended')->group(function() {
			Route::get('facilities', 'ReportsController@recommended');
		});
		Route::prefix('Approved')->group(function() {
			Route::get('facilities', 'ReportsController@approved');
		});
		Route::prefix('Certificates')->group(function() {
			Route::get('facilities', 'ReportsController@certificate');
		});
	});
	
});



// temporary upload
Route::get('/', 'NewClientController@__index');
Route::get('/logout', 'NewClientController@__newlogout');
// Route::get('/', 'UploadController@index');
Route::post('/store', 'UploadController@store')->name('file.store');
